<?php
/**
 * @api {get} /redirection/v1/log Get log logs
 * @apiDescription Get log logs
 * @apiGroup Log
 *
 * @apiParam {string} groupBy Group by 'ip' or 'url'
 * @apiParam {string} orderby
 * @apiParam {string} direction
 * @apiParam {string} filterBy
 * @apiParam {string} per_page
 * @apiParam {string} page
 */

/**
 * @api {post} /redirection/v1/log Delete log logs
 * @apiDescription Delete log logs either by ID or filter or group
 * @apiGroup Log
 *
 * @apiParam {string} items Array of log IDs
 * @apiParam {string} filterBy
 * @apiParam {string} groupBy Group by 'ip' or 'url'
 */

/**
 * @api {post} /redirection/v1/bulk/log/delete Bulk actions on logs
 * @apiDescription Delete log logs either by ID
 * @apiGroup Log
 */
class Redirection_Api_Log extends Redirection_Api_Filter_Route {
	public function __construct( $namespace ) {
		$orders = [ 'url', 'ip' ];
		$filters = [ 'ip', 'url-exact', 'referrer', 'agent', 'url', 'target' ];

		register_rest_route( $namespace, '/log', array(
			'args' => $this->get_filter_args( $orders, $filters ),
			$this->get_route( WP_REST_Server::READABLE, 'route_log' ),
			$this->get_route( WP_REST_Server::EDITABLE, 'route_delete_all' ),
		) );

		$this->register_bulk( $namespace, '/bulk/log/(?P<bulk>delete)', $orders, 'route_bulk' );
	}

	public function route_log( WP_REST_Request $request ) {
		return $this->get_logs( $request->get_params() );
	}

	public function route_bulk( WP_REST_Request $request ) {
		$items = explode( ',', $request['items'] );

		if ( is_array( $items ) ) {
			$items = array_map( 'intval', $items );
			array_map( array( 'RE_Log', 'delete' ), $items );
			return $this->route_log( $request );
		}

		return $this->add_error_details( new WP_Error( 'redirect', 'Invalid array of items' ), __LINE__ );
	}

	public function route_delete_all( WP_REST_Request $request ) {
		$params = $request->get_params();

		RE_Log::delete_all( isset( $params['filterBy'] ) ? $params['filterBy'] : [] );
		return $this->route_log( $request );
	}

	private function get_logs( array $params ) {
		return RE_Filter_Log::get( 'redirection_logs', 'RE_Log', $params );
	}
}
