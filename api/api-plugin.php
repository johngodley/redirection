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
}
