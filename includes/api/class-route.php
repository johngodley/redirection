<?php

namespace Redirection\Api;

use Redirection\Plugin;

class Route {
	protected function add_error_details( \WP_Error $error, $line, $code = 400 ) {
		global $wpdb;

		$data = array(
			'status' => $code,
			'error_code' => $line,
		);

		if ( isset( $wpdb->last_error ) && $wpdb->last_error ) {
			$data['wpdb'] = $wpdb->last_error;
		}

		$error->add_data( $data );
		return $error;
	}

	public function permission_callback( \WP_REST_Request $request ) {
		return Plugin\Capabilities::has_access( Plugin\Capabilities::CAP_PLUGIN );
	}

	public function get_route( $method, $callback, $permissions = false ) {
		return [
			'methods' => $method,
			'callback' => [ $this, $callback ],
			'permission_callback' => $permissions ? $permissions : [ $this, 'permission_callback' ],
		];
	}
}
