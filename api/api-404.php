<?php

/**
 * @phpstan-type Log404Response array{
 *   items: list<array<string, mixed>|object>,
 *   total: int
 * }
 *
 * 404 API endpoint
 */
class Redirection_Api_404 extends Redirection_Api_Filter_Route {
	/**
	 * 404 API endpoint constructor
	 *
	 * @param non-falsy-string $api_namespace Namespace.
	 */
	public function __construct( $api_namespace ) {
		$orders = [ 'url', 'ip', 'total', 'count', '' ];
		$filters = [ 'ip', 'url-exact', 'referrer', 'agent', 'url', 'domain', 'method', 'http' ];

		// GET /404 - List 404 logs
		register_rest_route(
			$api_namespace,
			'/404',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_404' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
					'args' => $this->get_filter_args( $orders, $filters ),
				],
			]
		);

		// POST /bulk/404/:bulk - Bulk delete 404 logs
		register_rest_route(
			$api_namespace,
			'/bulk/404/(?P<bulk>delete)',
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
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_404_MANAGE );
	}

	/**
	 * Checks a delete capability
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request.
	 * @return bool
	 */
	public function permission_callback_delete( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_404_DELETE );
	}

	/**
	 * Get 404 log
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request The request.
	 * @return Log404Response
	 */
	public function route_404( WP_REST_Request $request ) {
		return $this->get_404( $request->get_params() );
	}

	/**
	 * Perform action on 404s
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request The request.
	 * @return Log404Response
	 */
	public function route_bulk( WP_REST_Request $request ) {
		$params = $request->get_params();

		if ( isset( $params['items'] ) && is_array( $params['items'] ) && count( $params['items'] ) > 0 ) {
			$items = $params['items'];

			foreach ( $items as $item ) {
				if ( is_numeric( $item ) ) {
					Red_404_Log::delete( intval( $item, 10 ) );
				} elseif ( isset( $params['groupBy'] ) ) {
					$group_by = sanitize_text_field( $params['groupBy'] );
					$delete_by = 'url-exact';

					if ( in_array( $group_by, [ 'ip', 'agent' ], true ) ) {
						$delete_by = $group_by;
					}

					Red_404_Log::delete_all( [ 'filterBy' => [ $delete_by => $item ] ] );
				}
			}

			if ( isset( $params['groupBy'] ) && $params['groupBy'] === 'url-exact' ) {
				unset( $params['groupBy'] );
			}
		} elseif ( isset( $params['global'] ) && $params['global'] !== false ) {
			Red_404_Log::delete_all( $params );
		}

		return $this->get_404( $params );
	}

	/**
	 * Get 404 log
	 *
	 * @param array<string, mixed> $params The request parameters.
	 * @return Log404Response
	 */
	private function get_404( array $params ) {
		if ( isset( $params['groupBy'] ) && in_array( $params['groupBy'], [ 'ip', 'url', 'agent', 'url-exact' ], true ) ) {
			$group_by = sanitize_text_field( $params['groupBy'] );
			if ( $group_by === 'url-exact' ) {
				$group_by = 'url';
			}

			return Red_404_Log::get_grouped( $group_by, $params );
		}

		return Red_404_Log::get_filtered( $params );
	}
}
