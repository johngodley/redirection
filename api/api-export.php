<?php

/**
 * @api {get} /redirection/v1/export/:module/:format Export redirects for a module in a format
 * @apiDescription Export redirects for a module in a format
 * @apiGroup Export
 *
 * @apiParam {String} module The module to export - 1, 2, 3, or 'all'
 * @apiParam {String} format The format of the export. Either 'csv', 'apache', 'nginx', or 'json'
 *
 * @apiSuccess {Array} ip Array of export data
 * @apiSuccess {Integer} total Number of items exported
 *
 * @apiUse 400Error
 */
class Redirection_Api_Export extends Redirection_Api_Route {
	public function __construct( $namespace ) {
		register_rest_route( $namespace, '/export/(?P<module>1|2|3|all)/(?P<format>csv|apache|nginx|json)', array(
			$this->get_route( WP_REST_Server::READABLE, 'route_export' ),
		) );
	}

	public function route_export( WP_REST_Request $request ) {
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
