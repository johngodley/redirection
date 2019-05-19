<?php
/**
 * @api {get} /redirection/v1/log Get log logs
 * @apiDescription Get log logs
 * @apiGroup Log
 *
 * @apiParam {string} groupBy Group by 'ip' or 'url'
 * @apiParam {string} orderby
 * @apiParam {string} direction
 * @apiParam {string} filter
 * @apiParam {string} per_page
 * @apiParam {string} page
 */

/**
 * @api {post} /redirection/v1/log Delete log logs
 * @apiDescription Delete log logs either by ID or filter or group
 * @apiGroup Log
 *
 * @apiParam {string} items Array of log IDs
 * @apiParam {string} filter
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
		$filters = array( 'url', 'ip', 'url-exact' );
		$orders = array( 'url', 'ip' );

		register_rest_route( $namespace, '/log', array(
			'args' => $this->get_filter_args( $filters, $orders ),
			$this->get_route( WP_REST_Server::READABLE, 'route_log' ),
			$this->get_route( WP_REST_Server::EDITABLE, 'route_delete_all' ),
		) );

		$this->register_bulk( $namespace, '/bulk/log/(?P<bulk>delete)', $filters, $filters, 'route_bulk' );
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
		$filter = false;
		$filter_by = false;

		if ( isset( $params['filter'] ) ) {
			$filter = $params['filter'];
		}

		if ( isset( $params['filterBy'] ) && in_array( $params['filterBy'], array( 'url', 'ip', 'url-exact' ), true ) ) {
			$filter_by = $params['filterBy'];
		}

		RE_Log::delete_all( $filter_by, $filter );
		return $this->route_log( $request );
	}

	private function get_logs( array $params ) {
		return RE_Filter_Log::get( 'redirection_logs', 'RE_Log', $params );
	}
}
