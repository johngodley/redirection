<?php

/**
 * 'Plugin' functions for Redirection
 *
 * @phpstan-import-type DatabaseStatus from Red_Database_Status
 * @phpstan-import-type FixerJson from Red_Fixer
 */
class Redirection_Api_Plugin extends Redirection_Api_Route {
	/**
	 * Register REST routes for plugin actions
	 *
	 * @param string $api_namespace REST namespace.
	 * @return void
	 */
	public function __construct( $api_namespace ) {
		// GET/POST /plugin - Get plugin status or run fixer
		register_rest_route(
			$api_namespace,
			'/plugin',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_status' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
				],
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'route_fixit' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
					'args' => [
						'name' => [
							'description' => 'Name',
							'type' => 'string',
						],
						'value' => [
							'description' => 'Value',
							'type' => 'string',
						],
					],
				],
			]
		);

		// POST /plugin/delete - Delete plugin data
		register_rest_route(
			$api_namespace,
			'/plugin/delete',
			[
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'route_delete' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
				],
			]
		);

		// ALL /plugin/test - Test endpoint
		register_rest_route(
			$api_namespace,
			'/plugin/test',
			[
				[
					'methods' => WP_REST_Server::ALLMETHODS,
					'callback' => [ $this, 'route_test' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
				],
			]
		);

		// POST /plugin/data - Database upgrade/status
		register_rest_route(
			$api_namespace,
			'/plugin/data',
			[
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'route_database' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
					'args' => [
						'upgrade' => [
							'description' => 'Upgrade parameter',
							'type' => 'string',
							'enum' => [
								'stop',
								'skip',
								'retry',
							],
						],
					],
				],
			]
		);
	}

	/**
	 * Permission callback for manage-level actions
	 *
	 * @param WP_REST_Request $request REST request.
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 * @return bool
	 */
	public function permission_callback_manage( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_SUPPORT_MANAGE );
	}

	/**
	 * Get fixer status/debug details
	 *
	 * @param WP_REST_Request $request REST request.
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 * @return FixerJson
	 */
	public function route_status( WP_REST_Request $request ) {
		include_once dirname( REDIRECTION_FILE ) . '/models/fixer.php';

		$fixer = new Red_Fixer();
		return $fixer->get_json();
	}

	/**
	 * Run fixer or save a specific debug setting
	 *
	 * @param WP_REST_Request $request REST request.
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 * @return FixerJson|WP_Error
	 */
	public function route_fixit( WP_REST_Request $request ) {
		include_once dirname( REDIRECTION_FILE ) . '/models/fixer.php';

		$params = $request->get_params();
		$fixer = new Red_Fixer();

		if ( isset( $params['name'] ) && isset( $params['value'] ) ) {
			global $wpdb;

			$fixer->save_debug( sanitize_text_field( $params['name'] ), sanitize_text_field( $params['value'] ) );

			$groups = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups" ), 10 );
			if ( $groups === 0 ) {
				Red_Group::create( 'new group', 1 );
			}
		} else {
			$fixer->fix( $fixer->get_status() );
		}

		return $fixer->get_json();
	}

	/**
	 * Delete plugin (single-site only) and redirect back to plugins page
	 *
	 * @return array{location: string}|WP_Error
	 */
	public function route_delete() {
		if ( is_multisite() ) {
			return new WP_Error( 'redirect_delete_multi', 'Multisite installations must delete the plugin from the network admin' );
		}

		Redirection_Admin::plugin_uninstall();

		$current = get_option( 'active_plugins' );
		$plugin_position = array_search( basename( dirname( REDIRECTION_FILE ) ) . '/' . basename( REDIRECTION_FILE ), $current, true );
		if ( $plugin_position !== false ) {
			array_splice( $current, (int) $plugin_position, 1 );
			update_option( 'active_plugins', $current );
		}

		return array( 'location' => admin_url() . 'plugins.php' );
	}

	/**
	 * Simple plugin test endpoint
	 *
	 * @param WP_REST_Request $request REST request.
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 * @return array{success: true}
	 */
	public function route_test( WP_REST_Request $request ) {
		return array(
			'success' => true,
		);
	}

	/**
	 * Database status/upgrade orchestration
	 *
	 * @param WP_REST_Request $request REST request.
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 * @return DatabaseStatus
	 */
	public function route_database( WP_REST_Request $request ) {
		$params = $request->get_params();
		$status = new Red_Database_Status();
		/** @var string|false $upgrade */
		$upgrade = false;

		if ( isset( $params['upgrade'] ) && in_array( $params['upgrade'], [ 'stop', 'skip' ], true ) ) {
			$upgrade = sanitize_text_field( $params['upgrade'] );
		}

		// Check upgrade
		if ( ! $status->needs_updating() && ! $status->needs_installing() ) {
			/* translators: version number */
			$status->set_error( sprintf( __( 'Your database does not need updating to %s.', 'redirection' ), REDIRECTION_DB_VERSION ) );

			return $status->get_json();
		}

		if ( $upgrade === 'stop' ) {
			$status->stop_update();
		} elseif ( $upgrade === 'skip' ) {
			$status->set_next_stage();
		}

		$should_upgrade = $upgrade === false || $status->get_current_stage() !== false;
		if ( $should_upgrade ) {
			$database = new Red_Database();
			$database->apply_upgrade( $status );
		}

		return $status->get_json();
	}
}
