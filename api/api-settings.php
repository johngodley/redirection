<?php
/**
 * @api {get} /redirection/v1/setting Get settings
 * @apiName GetSettings
 * @apiDescription Get all settings for Redirection. This includes user-configurable settings, as well as necessary WordPress settings.
 * @apiGroup Settings
 *
 * @apiUse SettingItem
 * @apiUse 401Error
 * @apiUse 404Error
 */

/**
 * @api {post} /redirection/v1/setting Update settings
 * @apiName UpdateSettings
 * @apiDescription Update Redirection settings. Note you can do partial updates, and only the values specified will be changed.
 * @apiGroup Settings
 *
 * @apiParam {Object} settings An object containing all the settings to update
 * @apiParamExample {json} settings:
 *     {
 *       "expire_redirect": 14,
 *       "https": false
 *     }
 *
 * @apiUse SettingItem
 * @apiUse 401Error
 * @apiUse 404Error
 */

/**
 * @apiDefine SettingItem Settings
 * Redirection settings
 *
 * @apiSuccess {Object[]} settings An object containing all settings
 * @apiSuccess {String} settings.expire_redirect
 * @apiSuccess {String} settings.token
 * @apiSuccess {String} settings.monitor_post
 * @apiSuccess {String[]} settings.monitor_types
 * @apiSuccess {String} settings.associated_redirect
 * @apiSuccess {String} settings.auto_target
 * @apiSuccess {String} settings.expire_redirect
 * @apiSuccess {String} settings.expire_404
 * @apiSuccess {String} settings.modules
 * @apiSuccess {String} settings.redirect_cache
 * @apiSuccess {String} settings.ip_logging
 * @apiSuccess {String} settings.last_group_id
 * @apiSuccess {String} settings.rest_api
 * @apiSuccess {String} settings.https
 * @apiSuccess {String} settings.headers
 * @apiSuccess {String} settings.database
 * @apiSuccess {String} settings.relocate Relocate this site to the specified domain (and path)
 * @apiSuccess {String="www","nowww",""} settings.preferred_domain Preferred canonical domain
 * @apiSuccess {String[]} settings.aliases Array of domains that will be redirected to the current WordPress site
 * @apiSuccess {Object[]} groups An array of groups
 * @apiSuccess {String} groups.label Name of the group
 * @apiSuccess {Integer} groups.value Group ID
 * @apiSuccess {String} installed The path that WordPress is installed in
 * @apiSuccess {Boolean} canDelete True if Redirection can be deleted, false otherwise (on multisite, for example)
 * @apiSuccess {String[]} post_types Array of WordPress post types
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "settings": {
 *         "expire_redirect": 7,
 *         "https": true
 *       },
 *       "groups": [
 *          { label: 'My group', value: 5 }
 *       ],
 *       "installed": "/var/html/wordpress",
 *       "canDelete": true,
 *       "post_types": [
 *         "post",
 *         "page"
 *       ]
 *     }
 */

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
	 * @param string $api_namespace Namespace.
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
