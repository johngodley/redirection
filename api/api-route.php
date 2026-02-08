<?php

class Redirection_Api_Route {
	/**
	 * Add DB error details to a WP_Error
	 *
	 * @param WP_Error $error Error instance.
	 * @param string|int $line Error code/identifier.
	 * @param int $code HTTP status code.
	 * @return WP_Error
	 */
	protected function add_error_details( WP_Error $error, $line, $code = 400 ) {
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

	/**
	 * Default permission callback for API routes
	 *
	 * @param WP_REST_Request $request REST request.
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 * @return bool
	 */
	public function permission_callback( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_PLUGIN );
	}

	/**
	 * Build a REST route config
	 *
	 * @param mixed $method Allowed methods (WP_REST_Server constants).
	 * @param string $callback Method name on this class.
	 * @param callable|array{0:self,1:string}|false $permissions Permission callback or false for default.
	 * @return array<string, mixed>
	 */
	public function get_route( $method, $callback, $permissions = false ) {
		return [
			'methods' => $method,
			'callback' => [ $this, $callback ],
			'permission_callback' => $permissions ? $permissions : [ $this, 'permission_callback' ],
		];
	}
}
