<?php

use Redirection\ImportExport\ExportService;

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
 * @phpstan-type ExportPreviewResponse array{
 *   total: int,
 *   estimated_size: int
 * }
 */
class Redirection_Api_Export extends Redirection_Api_Route {
	/**
	 * Export API endpoint constructor
	 *
	 * @param non-falsy-string $api_namespace Namespace.
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

		register_rest_route(
			$api_namespace,
			'/export/redirect/preview',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_redirect_preview' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
				],
			]
		);

		register_rest_route(
			$api_namespace,
			'/export/redirect',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_redirect_export' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
				],
			]
		);

		register_rest_route(
			$api_namespace,
			'/export/log/preview',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_log_preview' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
				],
			]
		);

		register_rest_route(
			$api_namespace,
			'/export/log/(?P<format>csv|json)',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_log_export' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
				],
			]
		);

		register_rest_route(
			$api_namespace,
			'/export/404/preview',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_404_preview' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
				],
			]
		);

		register_rest_route(
			$api_namespace,
			'/export/404/(?P<format>csv|json)',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_404_export' ],
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

		$export = ( new ExportService() )->export( $module, $format );
		if ( $export === false ) {
			return $this->add_error_details( new WP_Error( 'redirect_export_invalid_module', 'Invalid module' ), __LINE__ );
		}

		return array(
			'data' => $export['data'],
			'total' => $export['total'],
		);
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return ExportPreviewResponse|WP_Error
	 */
	public function route_redirect_preview( WP_REST_Request $request ) {
		$scope = $this->get_redirect_scope( $request );
		$preview = ( new ExportService() )->preview_scope(
			$scope['scope_type'],
			$scope['scope_value'],
			$this->get_redirect_preview_format( $request )
		);

		if ( $preview === false ) {
			return $this->add_error_details( new WP_Error( 'redirect_export_invalid_scope', 'Invalid export scope' ), __LINE__ );
		}

		return $preview;
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return ExportResponse|WP_Error
	 */
	public function route_redirect_export( WP_REST_Request $request ) {
		$scope = $this->get_redirect_scope( $request );
		$format = 'json';

		if ( in_array( $request->get_param( 'format' ), [ 'csv', 'apache', 'nginx', 'json' ], true ) ) {
			$format = sanitize_text_field( strval( $request->get_param( 'format' ) ) );
		}

		$export = ( new ExportService() )->export_scope( $scope['scope_type'], $scope['scope_value'], $format );
		if ( $export === false ) {
			return $this->add_error_details( new WP_Error( 'redirect_export_invalid_scope', 'Invalid export scope' ), __LINE__ );
		}

		return [
			'data' => $export['data'],
			'total' => $export['total'],
		];
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return ExportPreviewResponse
	 */
	public function route_log_preview( WP_REST_Request $request ) {
		return Red_Redirect_Log::get_export_preview( $this->get_log_preview_format( $request ) );
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return ExportResponse|WP_Error
	 */
	public function route_log_export( WP_REST_Request $request ) {
		return $this->get_log_export_response( Red_Redirect_Log::class, sanitize_text_field( strval( $request['format'] ) ) );
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return ExportPreviewResponse
	 */
	public function route_404_preview( WP_REST_Request $request ) {
		return Red_404_Log::get_export_preview( $this->get_log_preview_format( $request ) );
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return ExportResponse|WP_Error
	 */
	public function route_404_export( WP_REST_Request $request ) {
		return $this->get_log_export_response( Red_404_Log::class, sanitize_text_field( strval( $request['format'] ) ) );
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return array{scope_type: 'all'|'module'|'group', scope_value: string|int}
	 */
	private function get_redirect_scope( WP_REST_Request $request ) {
		$scope_param = $request->get_param( 'scope_type' );
		$scope_type = sanitize_text_field( $scope_param === null ? 'all' : strval( $scope_param ) );
		$scope_value = $request->get_param( 'scope_value' );

		if ( $scope_type === 'group' ) {
			return [
				'scope_type' => 'group',
				'scope_value' => intval( $scope_value, 10 ),
			];
		}

		if ( $scope_type === 'module' ) {
			$value = $scope_value === 'all' ? 'all' : intval( $scope_value, 10 );

			return [
				'scope_type' => 'module',
				'scope_value' => $value,
			];
		}

		return [
			'scope_type' => 'all',
			'scope_value' => 'all',
		];
	}

	/**
	 * @param class-string<Red_Log> $log_class
	 * @param string $format
	 * @return ExportResponse|WP_Error
	 */
	private function get_log_export_response( $log_class, $format ) {
		if ( ! in_array( $format, [ 'csv', 'json' ], true ) ) {
			return $this->add_error_details( new WP_Error( 'redirect_export_invalid_format', 'Invalid export format' ), __LINE__ );
		}

		$data = $log_class::get_export_data( $format );

		if ( $data === false ) {
			return $this->add_error_details( new WP_Error( 'redirect_export_invalid_format', 'Invalid export format' ), __LINE__ );
		}

		return [
			'data' => $data,
			'total' => $log_class::get_export_total(),
		];
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return 'csv'|'json'
	 */
	private function get_log_preview_format( WP_REST_Request $request ) {
		$format = $request->get_param( 'format' );

		if ( $format === 'csv' ) {
			return 'csv';
		}

		return 'json';
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return 'csv'|'apache'|'nginx'|'json'
	 */
	private function get_redirect_preview_format( WP_REST_Request $request ) {
		$format = $request->get_param( 'format' );

		if ( $format === 'csv' || $format === 'apache' || $format === 'nginx' ) {
			return $format;
		}

		return 'json';
	}
}
