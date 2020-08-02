<?php
/**
 * @api {get} /redirection/v1/404 Get 404 logs
 * @apiName GetLogs
 * @apiDescription Get a paged list of 404 logs after applying a set of filters and result ordering.
 * @apiGroup 404
 *
 * @apiUse 404QueryParams
 *
 * @apiUse 404List
 * @apiUse 401Error
 * @apiUse 404Error
 */

/**
 * @api {post} /redirection/v1/404 Delete 404 logs
 * @apiName DeleteLogs
 * @apiDescription Delete 404 logs by filter. If no filter is supplied then all entries will be deleted. The endpoint will return the next page of results after.
 * performing the action, based on the supplied query parameters. This information can be used to refresh a list displayed to the client.
 * @apiGroup 404
 *
 * @apiParam (Query Parameter) {String} filterBy[ip] Filter the results by the supplied IP
 * @apiParam (Query Parameter) {String} filterBy[url] Filter the results by the supplied URL
 * @apiParam (Query Parameter) {String} filterBy[url-exact] Filter the results by the exact URL (not a substring match, as per `url`)
 * @apiParam (Query Parameter) {String} filterBy[referrer] Filter the results by the supplied referrer
 * @apiParam (Query Parameter) {String} filterBy[agent] Filter the results by the supplied user agent
 * @apiParam (Query Parameter) {String} filterBy[target] Filter the results by the supplied redirect target
 *
 * @apiUse 404List
 * @apiUse 401Error
 * @apiUse 404Error
 */

/**
 * @api {post} /redirection/v1/bulk/404/:type Bulk 404 action
 * @apiName BulkAction
 * @apiDescription Delete 404 logs by ID
 * @apiGroup 404
 *
 * @apiParam (URL) {String="delete"} :type Type of bulk action that is applied to every log ID.
 *
 * @apiParam (Query Parameter) {Integer[]} items Array of group IDs to perform the action on
 * @apiUse 404QueryParams
 *
 * @apiUse 404List
 * @apiUse 401Error
 * @apiUse 404Error
 * @apiUse 400MissingError
 */

/**
 * @apiDefine 404QueryParams 404 log query parameters
 *
 * @apiParam (Query Parameter) {String} filterBy[ip] Filter the results by the supplied IP
 * @apiParam (Query Parameter) {String} filterBy[url] Filter the results by the supplied URL
 * @apiParam (Query Parameter) {String} filterBy[url-exact] Filter the results by the exact URL (not a substring match, as per `url`)
 * @apiParam (Query Parameter) {String} filterBy[referrer] Filter the results by the supplied referrer
 * @apiParam (Query Parameter) {String} filterBy[agent] Filter the results by the supplied user agent
 * @apiParam (Query Parameter) {String} filterBy[target] Filter the results by the supplied redirect target
 * @apiParam (Query Parameter) {String} filterBy[domain] Filter the results by the supplied domain name
 * @apiParam (Query Parameter) {String="head","get","post"} filterBy[method] Filter the results by the supplied HTTP request method
 * @apiParam (Query Parameter) {Integer} filterBy[http] Filter the results by the supplied redirect HTTP code
 * @apiParam (Query Parameter) {string="ip","url"} orderby Order by IP or URL
 * @apiParam (Query Parameter) {String="asc","desc"} direction Direction to order the results by (ascending or descending)
 * @apiParam (Query Parameter) {Integer{1...200}} per_page Number of results per request
 * @apiParam (Query Parameter) {Integer} page Current page of results
 * @apiParam (Query Parameter) {String="ip","url"} groupBy Group by IP or URL
 */

/**
 * @apiDefine 404List
 *
 * @apiSuccess {Object[]} items Array of 404 log objects
 * @apiSuccess {Integer} items.id ID of 404 log entry
 * @apiSuccess {String} items.created Date the 404 log entry was recorded
 * @apiSuccess {Integer} items.created_time Unix time value for `created`
 * @apiSuccess {Integer} items.url The requested URL that caused the 404 log entry
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
class Redirection_Api_404 extends Redirection_Api_Filter_Route {
	public function __construct( $namespace ) {
		$orders = [ 'url', 'ip', 'total' ];
		$filters = [ 'ip', 'url-exact', 'referrer', 'agent', 'url', 'domain', 'method', 'http' ];

		register_rest_route( $namespace, '/404', array(
			'args' => $this->get_filter_args( $orders, $filters ),
			$this->get_route( WP_REST_Server::READABLE, 'route_404', [ $this, 'permission_callback_manage' ] ),
			$this->get_route( WP_REST_Server::EDITABLE, 'route_delete_all', [ $this, 'permission_callback_delete' ] ),
		) );

		register_rest_route( $namespace, '/bulk/404/(?P<bulk>delete)', array(
			$this->get_route( WP_REST_Server::EDITABLE, 'route_bulk', [ $this, 'permission_callback_delete' ] ),
			'args' => array_merge( $this->get_filter_args( $orders ), [
				'items' => [
					'description' => 'Comma separated list of item IDs to perform action on',
					'type' => 'string',
					'required' => true,
					'sanitize_callback' => [ $this, 'sanitize_bulk' ],
				],
			] ),
		) );
	}

	public function sanitize_bulk( $param ) {
		return explode( ',', $param );
	}

	public function permission_callback_manage( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_404_MANAGE );
	}

	public function permission_callback_delete( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_404_DELETE );
	}

	public function route_404( WP_REST_Request $request ) {
		return $this->get_404( $request->get_params() );
	}

	public function route_bulk( WP_REST_Request $request ) {
		$params = $request->get_params();
		$items = $request['items'];

		foreach ( $items as $item ) {
			if ( is_numeric( $item ) ) {
				Red_404_Log::delete( intval( $item, 10 ) );
			} else {
				Red_404_Log::delete_all( $this->get_delete_group( $params ), $item );
			}
		}

		return $this->route_404( $request );
	}

	private function get_delete_group( array $params ) {
		if ( isset( $params['groupBy'] ) && $params['groupBy'] === 'ip' ) {
			return 'ip';
		}

		return 'url-exact';
	}

	public function route_delete_all( WP_REST_Request $request ) {
		$params = $request->get_params();

		if ( isset( $params['items'] ) && is_array( $params['items'] ) ) {
			foreach ( $params['items'] as $url ) {
				Red_404_Log::delete_all( $this->get_delete_group( $params ), $url );
			}
		} else {
			$first_filter = isset( $params['filterBy'] ) ? array_keys( $params['filterBy'] )[0] : false;

			if ( $first_filter !== false || ! isset( $params['items'] ) ) {
				Red_404_Log::delete_all( $first_filter ? $first_filter : false, $first_filter ? $params['filterBy'][ $first_filter ] : false );
			}

			unset( $params['filterBy'] );
		}

		unset( $params['page'] );

		return $this->get_404( $params );
	}

	private function get_404( array $params ) {
		if ( isset( $params['groupBy'] ) && in_array( $params['groupBy'], [ 'ip', 'url', 'agent' ], true ) ) {
			return Red_404_Log::get_grouped( $params['groupBy'], $params );
		}

		return Red_404_Log::get_filtered( $params );
	}
}
