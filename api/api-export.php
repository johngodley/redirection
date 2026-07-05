<?php

use Redirection\ImportExport\ExportService;

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
					'permission_callback' => [ $this, 'permission_callback_redirect_export' ],
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
					'permission_callback' => [ $this, 'permission_callback_redirect_export' ],
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
					'permission_callback' => [ $this, 'permission_callback_redirect_export' ],
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
					'permission_callback' => [ $this, 'permission_callback_log_export' ],
				],
			]
		);

		register_rest_route(
			$api_namespace,
			'/export/group/(?P<format>csv|json)',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_group_export' ],
					'permission_callback' => [ $this, 'permission_callback_group_export' ],
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
					'permission_callback' => [ $this, 'permission_callback_log_export' ],
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
					'permission_callback' => [ $this, 'permission_callback_404_export' ],
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
					'permission_callback' => [ $this, 'permission_callback_404_export' ],
				],
			]
		);

		register_rest_route(
			$api_namespace,
			'/export/bundle',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_bundle_export' ],
					'permission_callback' => [ $this, 'permission_callback_bundle_export' ],
				],
			]
		);

		register_rest_route(
			$api_namespace,
			'/export/bundle/preview',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_bundle_preview' ],
					'permission_callback' => [ $this, 'permission_callback_bundle_export' ],
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
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return bool
	 */
	public function permission_callback_redirect_export( WP_REST_Request $request ) {
		return $this->permission_callback_manage( $request );
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return bool
	 */
	public function permission_callback_group_export( WP_REST_Request $request ) {
		return $this->permission_callback_manage( $request ) && $this->can_manage_groups();
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return bool
	 */
	public function permission_callback_log_export( WP_REST_Request $request ) {
		return $this->permission_callback_manage( $request ) &&
			Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_LOG_MANAGE );
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return bool
	 */
	public function permission_callback_404_export( WP_REST_Request $request ) {
		return $this->permission_callback_manage( $request ) &&
			Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_404_MANAGE );
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return bool
	 */
	public function permission_callback_bundle_export( WP_REST_Request $request ) {
		if ( ! $this->permission_callback_manage( $request ) ) {
			return false;
		}

		foreach ( $this->get_bundle_types( $request ) as $type ) {
			if ( $type === 'group' && ! $this->can_manage_groups() ) {
				return false;
			}

			if ( $type === 'log' && ! Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_LOG_MANAGE ) ) {
				return false;
			}

			if ( $type === '404' && ! Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_404_MANAGE ) ) {
				return false;
			}

			if ( $type === 'setting' && ! $this->can_manage_settings() ) {
				return false;
			}
		}

		return true;
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
		$format = 'json';

		if ( in_array( $request->get_param( 'format' ), [ 'csv', 'apache', 'nginx', 'json' ], true ) ) {
			$format = sanitize_text_field( strval( $request->get_param( 'format' ) ) );
		}

		$params = $this->get_redirect_export_params( $request );

		if ( isset( $params['items'] ) || isset( $params['global'] ) || isset( $params['filterBy'] ) ) {
			$export = ( new ExportService() )->export_redirects( $format, $params );
		} else {
			$scope = $this->get_redirect_scope( $request );
			$export = ( new ExportService() )->export_scope( $scope['scope_type'], $scope['scope_value'], $format );
		}

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
		return $this->get_log_export_response(
			Red_Redirect_Log::class,
			sanitize_text_field( strval( $request['format'] ) ),
			$request
		);
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return ExportResponse|WP_Error
	 */
	public function route_group_export( WP_REST_Request $request ) {
		$format = sanitize_text_field( strval( $request['format'] ) );
		$result = ( new ExportService() )->export_groups( $format, $this->get_group_export_params( $request ) );

		if ( $result === false ) {
			return $this->add_error_details( new WP_Error( 'redirect_export_invalid_format', 'Invalid export format' ), __LINE__ );
		}

		return [
			'data' => $result['data'],
			'total' => $result['total'],
		];
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
		return $this->get_log_export_response(
			Red_404_Log::class,
			sanitize_text_field( strval( $request['format'] ) ),
			$request
		);
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return ExportResponse|WP_Error
	 */
	public function route_bundle_export( WP_REST_Request $request ) {
		$format = $this->get_bundle_format( $request );
		$result = ( new ExportService() )->export_bundle(
			$this->get_bundle_types( $request ),
			$this->get_bundle_scope_options( $request ),
			$format
		);

		if ( $result === false ) {
			return $this->add_error_details( new WP_Error( 'redirect_export_invalid_format', 'Invalid export format' ), __LINE__ );
		}

		return [
			'data' => $result['data'],
			'total' => $result['total'],
		];
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return ExportPreviewResponse|WP_Error
	 */
	public function route_bundle_preview( WP_REST_Request $request ) {
		$format = $this->get_bundle_format( $request );
		$result = ( new ExportService() )->preview_bundle(
			$this->get_bundle_types( $request ),
			$this->get_bundle_scope_options( $request ),
			$format
		);

		if ( $result === false ) {
			return $this->add_error_details( new WP_Error( 'redirect_export_invalid_format', 'Invalid export format' ), __LINE__ );
		}

		return $result;
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
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return ExportResponse|WP_Error
	 */
	private function get_log_export_response( $log_class, $format, WP_REST_Request $request ) {
		if ( ! in_array( $format, [ 'csv', 'json' ], true ) ) {
			return $this->add_error_details( new WP_Error( 'redirect_export_invalid_format', 'Invalid export format' ), __LINE__ );
		}

		$params = $this->get_log_export_params( $request );
		$display_selected = $this->get_log_display_selected( $request );
		$data = $log_class::get_export_data( $format, $params, $display_selected );

		if ( $data === false ) {
			return $this->add_error_details( new WP_Error( 'redirect_export_invalid_format', 'Invalid export format' ), __LINE__ );
		}

		return [
			'data' => $data,
			'total' => $log_class::get_export_total( $params ),
		];
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return array<string, mixed>
	 */
	private function get_log_export_params( WP_REST_Request $request ) {
		$params = [];
		$request_params = $request->get_params();

		if ( isset( $request_params['items'] ) && is_array( $request_params['items'] ) ) {
			$params['items'] = array_values( $request_params['items'] );
		}

		if ( isset( $request_params['global'] ) ) {
			$params['global'] = filter_var( $request_params['global'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( isset( $request_params['groupBy'] ) ) {
			$params['groupBy'] = sanitize_text_field( strval( $request_params['groupBy'] ) );
		}

		if ( isset( $request_params['filterBy'] ) && is_array( $request_params['filterBy'] ) ) {
			$params['filterBy'] = $request_params['filterBy'];
		}

		return $params;
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return array<int, string>
	 */
	private function get_log_display_selected( WP_REST_Request $request ) {
		$display_selected = $request->get_param( 'displaySelected' );

		if ( ! is_array( $display_selected ) ) {
			return [];
		}

		return array_values(
			array_filter(
				array_map(
					static function ( $value ) {
						return is_string( $value ) ? sanitize_text_field( $value ) : '';
					},
					$display_selected
				)
			)
		);
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return array<string, mixed>
	 */
	private function get_redirect_export_params( WP_REST_Request $request ) {
		$params = [];
		$request_params = $request->get_params();

		if ( isset( $request_params['items'] ) && is_array( $request_params['items'] ) ) {
			$params['items'] = array_values( $request_params['items'] );
		}

		if ( isset( $request_params['global'] ) ) {
			$params['global'] = filter_var( $request_params['global'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( isset( $request_params['filterBy'] ) && is_array( $request_params['filterBy'] ) ) {
			$params['filterBy'] = $request_params['filterBy'];
		}

		return $params;
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return array<string, mixed>
	 */
	private function get_group_export_params( WP_REST_Request $request ) {
		$params = [];
		$request_params = $request->get_params();

		if ( isset( $request_params['items'] ) && is_array( $request_params['items'] ) ) {
			$params['items'] = array_values( $request_params['items'] );
		}

		if ( isset( $request_params['global'] ) ) {
			$params['global'] = filter_var( $request_params['global'], FILTER_VALIDATE_BOOLEAN );
		}

		if ( isset( $request_params['filterBy'] ) && is_array( $request_params['filterBy'] ) ) {
			$params['filterBy'] = $request_params['filterBy'];
		}

		return $params;
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

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return array<int, string>
	 */
	private function get_bundle_types( WP_REST_Request $request ) {
		$types = $request->get_param( 'types' );

		if ( ! is_array( $types ) ) {
			return [];
		}

		return array_values(
			array_filter(
				array_map(
					static function ( $type ) {
						return is_string( $type ) ? sanitize_text_field( $type ) : '';
					},
					$types
				)
			)
		);
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return array{scope_type?: 'all'|'module'|'group', scope_value?: string|int}
	 */
	private function get_bundle_scope_options( WP_REST_Request $request ) {
		$scope = $this->get_redirect_scope( $request );

		return [
			'scope_type' => $scope['scope_type'],
			'scope_value' => $scope['scope_value'],
		];
	}

	/**
	 * @param WP_REST_Request<array<string, mixed>> $request
	 * @return 'json'|'csv'
	 */
	private function get_bundle_format( WP_REST_Request $request ) {
		$format = $request->get_param( 'format' );

		if ( $format === 'csv' ) {
			return 'csv';
		}

		return 'json';
	}

	/**
	 * @return bool
	 */
	private function can_manage_groups() {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_GROUP_MANAGE ) ||
			Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_REDIRECT_MANAGE );
	}

	/**
	 * @return bool
	 */
	private function can_manage_settings() {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_OPTION_MANAGE ) ||
			Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_SITE_MANAGE );
	}
}
