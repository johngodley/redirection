<?php

/**
 * @api {get} /redirection/v1/export/:module/:format Export redirects
 * @apiName Export
 * @apiDescription Export redirects for a module to Apache, CSV, Nginx, or JSON format
 * @apiGroup Import/Export
 *
 * @apiParam (URL) {String="1","2","3","all"} :module The module to export, with 1 being WordPress, 2 is Apache, and 3 is Nginx
 * @apiParam (URL) {String="csv","apache","nginx","json"} :format The format of the export
 *
 * @apiSuccess {String} data Exported data
 * @apiSuccess {Integer} total Number of items exported
 *
 * @apiUse 401Error
 * @apiUse 404Error
 * @apiError redirect_export_invalid_module Invalid module
 * @apiErrorExample {json} 404 Error Response:
 *     HTTP/1.1 400 Bad Request
 *     {
 *       "code": "redirect_export_invalid_module",
 *       "message": "Invalid module"
 *     }
 */

/**
 * @phpstan-type ExportResponse array{
 *   data: string,
 *   total: int
 * }
 */
class Redirection_Api_Export extends Redirection_Api_Route {
	/**
	 * Export API endpoint constructor
	 *
	 * @param string $api_namespace Namespace.
	 */
	public function __construct( $api_namespace ) {
		// GET /export/:module/:format - Export redirects to specified format
		register_rest_route(
			$api_namespace,
			'/export/(?P<module>1|2|3|all)/(?P<format>csv|apache|nginx|json)',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_export' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
				],
			]
		);
	}

	/**
	 * Check if the user has permission to manage import/export
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return bool
	 */
	public function permission_callback_manage( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_IO_MANAGE );
	}

	/**
	 * Export redirects to a specified format
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return ExportResponse|WP_Error
	 */
	public function route_export( WP_REST_Request $request ) {
		$module = sanitize_text_field( $request['module'] );
		$format = 'json';

		if ( in_array( $request['format'], [ 'csv', 'apache', 'nginx', 'json' ], true ) ) {
			$format = sanitize_text_field( $request['format'] );
		}

		$export = Red_FileIO::export( $module, $format );
		if ( $export === false ) {
			return $this->add_error_details( new WP_Error( 'redirect_export_invalid_module', 'Invalid module' ), __LINE__ );
		}

		return array(
			'data' => $export['data'],
			'total' => $export['total'],
		);
	}
}
