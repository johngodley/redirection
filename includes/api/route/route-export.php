<?php

namespace Redirection\Api\Route;

use Redirection\FileIO;
use Redirection\Api;
use Redirection\Plugin;

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
 * @apiError (Error 400) redirect_export_invalid_module Invalid module
 * @apiErrorExample {json} 404 Error Response:
 *     HTTP/1.1 400 Bad Request
 *     {
 *       "code": "redirect_export_invalid_module",
 *       "message": "Invalid module"
 *     }
 */
class Export extends Api\Route {
	public function __construct( $namespace ) {
		register_rest_route( $namespace, '/export/(?P<module>1|2|3|all)/(?P<format>csv|apache|nginx|json)', array(
			$this->get_route( \WP_REST_Server::READABLE, 'route_export', [ $this, 'permission_callback_manage' ] ),
		) );
	}

	public function permission_callback_manage( \WP_REST_Request $request ) {
		return Plugin\Capabilities::has_access( Plugin\Capabilities::CAP_IO_MANAGE );
	}

	public function route_export( \WP_REST_Request $request ) {
		$module = $request['module'];
		$format = 'json';

		if ( in_array( $request['format'], [ 'csv', 'apache', 'nginx', 'json' ], true ) ) {
			$format = $request['format'];
		}

		$export = FileIO\FileIO::export( $module, $format );
		if ( $export === false ) {
			return $this->add_error_details( new \WP_Error( 'redirect_export_invalid_module', 'Invalid module' ), __LINE__ );
		}

		return array(
			'data' => $export['data'],
			'total' => $export['total'],
		);
	}
}