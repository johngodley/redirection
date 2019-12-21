<?php

/**
 * 'Plugin' functions for Redirection
 */
class Redirection_Api_Plugin extends Redirection_Api_Route {
	public function __construct( $namespace ) {
		register_rest_route( $namespace, '/plugin', array(
			$this->get_route( WP_REST_Server::READABLE, 'route_status', [ $this, 'permission_callback_manage' ] ),
		) );

		register_rest_route( $namespace, '/plugin', array(
			$this->get_route( WP_REST_Server::EDITABLE, 'route_fixit', [ $this, 'permission_callback_manage' ] ),
			'args' => [
				'name' => array(
					'description' => 'Name',
					'type' => 'string',
				),
				'value' => array(
					'description' => 'Value',
					'type' => 'string',
				),
			],
		) );

		register_rest_route( $namespace, '/plugin/delete', array(
			$this->get_route( WP_REST_Server::EDITABLE, 'route_delete', [ $this, 'permission_callback_manage' ] ),
		) );

		register_rest_route( $namespace, '/plugin/test', array(
			$this->get_route( WP_REST_Server::ALLMETHODS, 'route_test', [ $this, 'permission_callback_manage' ] ),
		) );

		register_rest_route( $namespace, '/plugin/database', array(
			$this->get_route( WP_REST_Server::EDITABLE, 'route_database', [ $this, 'permission_callback_manage' ] ),
			'args' => array(
				'description' => 'Upgrade parameter',
				'type' => 'enum',
				'enum' => array(
					'stop',
					'skip',
				),
			),
		) );
	}

	public function permission_callback_manage( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_SUPPORT_MANAGE );
	}

	public function route_status( WP_REST_Request $request ) {
		include_once dirname( REDIRECTION_FILE ) . '/models/fixer.php';

		$fixer = new Red_Fixer();
		return $fixer->get_json();
	}

	public function route_fixit( WP_REST_Request $request ) {
		include_once dirname( REDIRECTION_FILE ) . '/models/fixer.php';

		$params = $request->get_params();
		$fixer = new Red_Fixer();

		if ( isset( $params['name'] ) && isset( $params['value'] ) ) {
			global $wpdb;

			$fixer->save_debug( $params['name'], $params['value'] );

			$groups = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups" ), 10 );
			if ( $groups === 0 ) {
				Red_Group::create( 'new group', 1 );
			}
		} else {
			$fixer->fix( $fixer->get_status() );
		}

		return $fixer->get_json();
	}

	public function route_delete() {
		if ( is_multisite() ) {
			return new WP_Error( 'redirect_delete_multi', 'Multisite installations must delete the plugin from the network admin' );
		}

		$plugin = Redirection_Admin::init();
		$plugin->plugin_uninstall();

		$current = get_option( 'active_plugins' );
		array_splice( $current, array_search( basename( dirname( REDIRECTION_FILE ) ) . '/' . basename( REDIRECTION_FILE ), $current ), 1 );
		update_option( 'active_plugins', $current );

		return array( 'location' => admin_url() . 'plugins.php' );
	}

	public function route_test( WP_REST_Request $request ) {
		return array(
			'success' => true,
		);
	}

	public function route_database( WP_REST_Request $request ) {
		$params = $request->get_params();
		$status = new Red_Database_Status();
		$upgrade = false;

		if ( isset( $params['upgrade'] ) && in_array( $params['upgrade'], [ 'stop', 'skip' ], true ) ) {
			$upgrade = $params['upgrade'];
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

		if ( $upgrade === false || $status->get_current_stage() ) {
			$database = new Red_Database();
			$database->apply_upgrade( $status );
		}

		return $status->get_json();
	}
}
