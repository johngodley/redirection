<?php

/**
 * @phpstan-type RedirectLogResponse array{
 *   items: list<array<string, mixed>|object>,
 *   total: int
 * }
 *
 * Log API endpoint
 */
class Redirection_Api_Log extends Redirection_Api_Filter_Route {
	/**
	 * Log API endpoint constructor
	 *
	 * @param non-falsy-string $api_namespace Namespace.
	 */
	public function __construct( $api_namespace ) {
		$orders = [ 'url', 'ip', 'total', 'count', '' ];
		$filters = [ 'ip', 'url-exact', 'referrer', 'agent', 'url', 'target', 'domain', 'method', 'http', 'redirect_by' ];

		// GET /log - Get redirect logs
		register_rest_route(
			$api_namespace,
			'/log',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_log' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
					'args' => $this->get_filter_args( $orders, $filters ),
				],
			]
		);

		// POST /bulk/log/:bulk - Bulk delete logs
		register_rest_route(
			$api_namespace,
			'/bulk/log/(?P<bulk>delete)',
			[
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'route_bulk' ],
					'permission_callback' => [ $this, 'permission_callback_delete' ],
					'args' => array_merge(
						$this->get_filter_args( $orders, $filters ),
						[
							'items' => [
								'description' => 'Comma separated list of item IDs to perform action on',
								'type' => 'array',
								'items' => [
									'description' => 'Item ID',
									'type' => [ 'string', 'number' ],
								],
							],
						]
					),
				],
			]
		);
	}

	/**
	 * Checks a manage capability
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request.
	 * @return bool
	 */
	public function permission_callback_manage( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_LOG_MANAGE );
	}

	/**
	 * Checks a delete capability
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request.
	 * @return bool
	 */
	public function permission_callback_delete( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_LOG_DELETE );
	}

	/**
	 * Get log list
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request The request.
	 * @return RedirectLogResponse
	 */
	public function route_log( WP_REST_Request $request ) {
		return $this->get_logs( $request->get_params() );
	}

	/**
	 * Perform bulk action on logs
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request The request.
	 * @return RedirectLogResponse
	 */
	public function route_bulk( WP_REST_Request $request ) {
		$params = $request->get_params();

		if ( isset( $params['items'] ) && is_array( $params['items'] ) && count( $params['items'] ) > 0 ) {
			$items = $params['items'];

			foreach ( $items as $item ) {
				if ( is_numeric( $item ) ) {
					Red_Redirect_Log::delete( intval( $item, 10 ) );
				} elseif ( isset( $params['groupBy'] ) ) {
					$delete_by = 'url-exact';

					if ( in_array( $params['groupBy'], [ 'ip', 'agent' ], true ) ) {
						$delete_by = sanitize_text_field( $params['groupBy'] );
					}

					Red_Redirect_Log::delete_all( [ 'filterBy' => [ $delete_by => $item ] ] ); // @phpstan-ignore-line
				}
			}
		} elseif ( isset( $params['global'] ) && $params['global'] !== false ) {
			Red_Redirect_Log::delete_all( $params );
		}

		return $this->route_log( $request );
	}

	/**
	 * Get redirect logs
	 *
	 * @param array<string, mixed> $params The request parameters.
	 * @return RedirectLogResponse
	 */
	private function get_logs( array $params ) {
		if ( isset( $params['groupBy'] ) && in_array( $params['groupBy'], [ 'ip', 'url', 'agent' ], true ) ) {
			return Red_Redirect_Log::get_grouped( sanitize_text_field( $params['groupBy'] ), $params );
		}

		return Red_Redirect_Log::get_filtered( $params );
	}
}
