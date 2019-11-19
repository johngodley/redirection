<?php

/**
 * @api {get} /redirection/v1/group Get logs
 * @apiName GetLogs
 * @apiDescription Get a paged list of redirect logs after applying a set of filters and result ordering.
 * @apiGroup Log
 *
 * @apiUse LogQueryParams
 *
 * @apiUse LogList
 * @apiUse 401Error
 * @apiUse 404Error
 */

/**
 * @api {post} /redirection/v1/log Delete logs
 * @apiName DeleteLogs
 * @apiDescription Delete logs by filter. If no filter is supplied then all entries will be deleted. The endpoint will return the next page of results after.
 * performing the action, based on the supplied query parameters. This information can be used to refresh a list displayed to the client.
 * @apiGroup Log
 *
 * @apiParam (Query Parameter) {String} filterBy[ip] Filter the results by the supplied IP
 * @apiParam (Query Parameter) {String} filterBy[url] Filter the results by the supplied URL
 * @apiParam (Query Parameter) {String} filterBy[url-exact] Filter the results by the exact URL (not a substring match, as per `url`)
 * @apiParam (Query Parameter) {String} filterBy[referrer] Filter the results by the supplied referrer
 * @apiParam (Query Parameter) {String} filterBy[agent] Filter the results by the supplied user agent
 * @apiParam (Query Parameter) {String} filterBy[target] Filter the results by the supplied redirect target
 *
 * @apiUse LogList
 * @apiUse 401Error
 * @apiUse 404Error
 */

/**
 * @api {post} /redirection/v1/bulk/log/:type Bulk log action
 * @apiName BulkAction
 * @apiDescription Delete logs by ID
 * @apiGroup Log
 *
 * @apiParam (URL) {String="delete"} :type Type of bulk action that is applied to every log ID.
 *
 * @apiParam (Query Parameter) {Integer[]} items Array of group IDs to perform the action on
 * @apiUse LogQueryParams
 *
 * @apiUse LogList
 * @apiUse 401Error
 * @apiUse 404Error
 * @apiUse 400MissingError
 * @apiError (Error 400) redirect_log_invalid_items Invalid array of items
 * @apiErrorExample {json} 404 Error Response:
 *     HTTP/1.1 400 Bad Request
 *     {
 *       "code": "redirect_log_invalid_items",
 *       "message": "Invalid array of items"
 *     }
 */

/**
 * @apiDefine LogQueryParams Log query parameters
 *
 * @apiParam (Query Parameter) {String} filterBy[ip] Filter the results by the supplied IP
 * @apiParam (Query Parameter) {String} filterBy[url] Filter the results by the supplied URL
 * @apiParam (Query Parameter) {String} filterBy[url-exact] Filter the results by the exact URL (not a substring match, as per `url`)
 * @apiParam (Query Parameter) {String} filterBy[referrer] Filter the results by the supplied referrer
 * @apiParam (Query Parameter) {String} filterBy[agent] Filter the results by the supplied user agent
 * @apiParam (Query Parameter) {String} filterBy[target] Filter the results by the supplied redirect target
 * @apiParam (Query Parameter) {string="ip","url"} orderby Order by IP or URL
 * @apiParam (Query Parameter) {String="asc","desc"} direction Direction to order the results by (ascending or descending)
 * @apiParam (Query Parameter) {Integer{1...200}} per_page Number of results per request
 * @apiParam (Query Parameter) {Integer} page Current page of results
 */

/**
 * @apiDefine LogList
 *
 * @apiSuccess {Object[]} items Array of log objects
 * @apiSuccess {Integer} items.id ID of log entry
 * @apiSuccess {String} items.created Date the log entry was recorded
 * @apiSuccess {Integer} items.created_time Unix time value for `created`
 * @apiSuccess {Integer} items.url The requested URL that caused the log entry
 * @apiSuccess {String} items.agent User agent of the client initiating the request
 * @apiSuccess {Integer} items.referrer Referrer of the client initiating the request
 * @apiSuccess {Integer} total Number of items
 *
 * @apiSuccessExample {json} Success 200:
 *     HTTP/1.1 200 OK
 *     {
 *       "items": [
 *           {
 *             "id": 3,
 *             "created": "2019-01-01 12:12:00,
 *             "created_time": "12345678",
 *             "url": "/the-url",
 *             "agent": "FancyBrowser",
 *             "referrer": "http://site.com/previous/,
 *           }
 *       ],
 *       "total": 1
 *     }
 */

class Redirection_Api_Log extends Redirection_Api_Filter_Route {
	public function __construct( $namespace ) {
		$orders = [ 'url', 'ip' ];
		$filters = [ 'ip', 'url-exact', 'referrer', 'agent', 'url', 'target' ];

		register_rest_route( $namespace, '/log', array(
			'args' => $this->get_filter_args( $orders, $filters ),
			$this->get_route( WP_REST_Server::READABLE, 'route_log', [ $this, 'permission_callback_manage' ] ),
			$this->get_route( WP_REST_Server::EDITABLE, 'route_delete_all', [ $this, 'permission_callback_delete' ] ),
		) );

		$this->register_bulk( $namespace, '/bulk/log/(?P<bulk>delete)', $orders, 'route_bulk', [ $this, 'permission_callback_delete' ] );
	}

	public function permission_callback_manage( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_LOG_MANAGE );
	}

	public function permission_callback_delete( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_LOG_DELETE );
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

		return $this->add_error_details( new WP_Error( 'redirect_log_invalid_items', 'Invalid array of items' ), __LINE__ );
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
