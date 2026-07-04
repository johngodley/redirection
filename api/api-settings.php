<?php

/**
 * @phpstan-import-type RedirectionOptions from Red_Options
 *
 * @phpstan-type SettingsResponse array{
 *   settings: RedirectionOptions,
 *   groups: array<int, object>,
 *   installed: string,
 *   canDelete: bool,
 *   post_types: array<int|string>
 * }
 * @phpstan-type SettingsResponseWithWarning array{
 *   settings: RedirectionOptions,
 *   groups: array<int, object>,
 *   installed: string,
 *   canDelete: bool,
 *   post_types: array<int|string>,
 *   warning?: string
 * }
 */
class Redirection_Api_Settings extends Redirection_Api_Route {
	/**
	 * Settings API endpoint constructor
	 *
	 * @param non-falsy-string $api_namespace Namespace.
	 */
	public function __construct( $api_namespace ) {
		// GET /setting - Get settings
		// POST /setting - Update settings
		register_rest_route(
			$api_namespace,
			'/setting',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_settings' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
				],
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'route_save_settings' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
				],
			]
		);
	}

	/**
	 * Get all settings for Redirection
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return SettingsResponse
	 */
	public function route_settings( WP_REST_Request $request ) {
		if ( ! function_exists( 'get_home_path' ) ) {
			include_once ABSPATH . '/wp-admin/includes/file.php';
		}

		return [
			'settings' => Red_Options::get(),
			'groups' => $this->groups_to_json( Red_Group::get_for_select() ),
			'installed' => get_home_path(),
			'canDelete' => ! is_multisite(),
			'post_types' => red_get_post_types(),
		];
	}

	/**
	 * Check if the user has permission to manage settings
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return bool
	 */
	public function permission_callback_manage( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_OPTION_MANAGE ) || Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_SITE_MANAGE );
	}

	/**
	 * Save settings for Redirection
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return SettingsResponseWithWarning
	 */
	public function route_save_settings( WP_REST_Request $request ) {
		$params = $request->get_params();
		$result = true;

		if ( isset( $params['location'] ) && strlen( $params['location'] ) > 0 ) {
			$module = Red_Module::get( 2 );
			if ( $module !== false && $module instanceof Apache_Module ) {
				$result = $module->can_save( sanitize_text_field( $params['location'] ) );
			}
		}

		red_set_options( $params );

		$settings = $this->route_settings( $request );
		if ( is_wp_error( $result ) ) {
			$settings['warning'] = $result->get_error_message();
		}

		return $settings;
	}

	/**
	 * Convert groups array to JSON format
	 *
	 * @param array<string|int, mixed> $groups Groups array from Red_Group::get_for_select()
	 * @param int $depth Current recursion depth
	 * @return array<int, object>
	 */
	private function groups_to_json( $groups, $depth = 0 ) {
		$items = array();

		foreach ( $groups as $text => $value ) {
			if ( is_array( $value ) && $depth === 0 ) {
				$items[] = (object) array(
					'label' => $text,
					'value' => $this->groups_to_json( $value, 1 ),
				);
			} else {
				$items[] = (object) array(
					'label' => $value,
					'value' => $text,
				);
			}
		}

		return $items;
	}
}
