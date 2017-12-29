<?php

class Redirection_Api_Export extends Redirection_Api_Route {
	public function __construct( $namespace ) {
		register_rest_route( $namespace, '/export/(?P<module>1|2|3|all)/(?P<format>csv|apache|nginx|json)', array(
			$this->get_route( WP_REST_Server::READABLE, 'route_export' ),
		) );
	}

	public function route_export( WP_REST_Request $request ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			include_once ABSPATH.'/wp-admin/includes/plugin.php';
		}

		$module = $request['module'];
		$format = 'json';

		if ( in_array( $request['format'], array( 'csv', 'apache', 'nginx', 'json' ) ) ) {
			$format = $request['format'];
		}

		$export = Red_FileIO::export( $module, $format );
		if ( $export === false ) {
			return $this->add_error_details( new WP_Error( 'redirect', 'Invalid module' ), __LINE__ );
		}

		return array(
			'data' => $export['data'],
			'total' => $export['total'],
		);
	}
}
