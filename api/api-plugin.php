<?php

/**
 * 'Plugin' functions for Redirection
 */
class Redirection_Api_Plugin extends Redirection_Api_Route {
	public function __construct( $namespace ) {
		register_rest_route( $namespace, '/plugin', array(
			$this->get_route( WP_REST_Server::READABLE, 'route_status' ),
			$this->get_route( WP_REST_Server::EDITABLE, 'route_fixit' ),
		) );

		register_rest_route( $namespace, '/plugin/delete', array(
			$this->get_route( WP_REST_Server::EDITABLE, 'route_delete' ),
		) );

		register_rest_route( $namespace, '/plugin/test', array(
			$this->get_route( WP_REST_Server::EDITABLE, 'route_test' ),
		) );

		register_rest_route( $namespace, '/plugin/database', array(
			$this->get_route( WP_REST_Server::EDITABLE, 'route_database' ),
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

	public function route_status( WP_REST_Request $request ) {
		include_once dirname( REDIRECTION_FILE ) . '/models/fixer.php';

		$fixer = new Red_Fixer();
		return $fixer->get_status();
	}

	public function route_fixit( WP_REST_Request $request ) {
		include_once dirname( REDIRECTION_FILE ) . '/models/fixer.php';

		$fixer = new Red_Fixer();
		return $fixer->fix( $fixer->get_status() );
	}

	public function route_delete() {
		if ( is_multisite() ) {
			return $this->getError( 'Multisite installations must delete the plugin from the network admin', __LINE__ );
		}

		$plugin = Redirection_Admin::init();
		$plugin->plugin_uninstall();

		$current = get_option( 'active_plugins' );
		array_splice( $current, array_search( basename( dirname( REDIRECTION_FILE ) ) . '/' . basename( REDIRECTION_FILE ), $current ), 1 );
		update_option( 'active_plugins', $current );

		return array( 'location' => admin_url() . 'plugins.php' );
	}

	public function route_test() {
		return array(
			'success' => true,
		);
	}

	public function route_database( WP_REST_Request $request ) {
		$params = $request->get_params();
		$database = new Red_Database();
		$status = new Red_Database_Status();
		$upgrade = false;

		if ( isset( $params['upgrade'] ) && in_array( $params['upgrade'], [ 'stop', 'skip' ], true ) ) {
			$upgrade = $params['upgrade'];
		}

		// Check upgrade
		if ( ! $database->needs_updating( REDIRECTION_DB_VERSION ) && ! $database->needs_installing() ) {
			$latest = Red_Database::get_latest_database();

			return array_merge(
				$status->get_upgrade_status(),
				[
					'status' => 'error',
					/* translators: version number */
					'reason' => sprintf( __( 'Your database does not need updating to %s.', 'redirection' ), REDIRECTION_DB_VERSION ),
					'debug' => $latest->get_table_schema(),
				]
			);
		}

		if ( $upgrade === 'stop' ) {
			$status->stop_upgrade();
			return $status->get_upgrade_status();
		}

		$current = $status->get_current_stage();
		if ( $upgrade === 'skip' ) {
			$current = $status->skip_current_stage();

			if ( $current === false ) {
				return $status->get_upgrade_status();
			}
		}

		$result = $database->apply_upgrade( $current );
		return $status->get_upgrade_status( $result );
	}
}
