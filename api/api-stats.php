<?php

/**
 * @apiDefine StatsQueryParams Stats query parameters
 *
 * @apiParam (Query Parameter) {String} filterBy[url] Filter the results by the supplied URL
 * @apiParam (Query Parameter) {String} filterBy[url-exact] Filter the results by the exact URL (not a substring match, as per `url`)
 * @apiParam (Query Parameter) {String} dateRange[startDate] Filter the results by the supplied referrer
 * @apiParam (Query Parameter) {String} dateRange[endDate] Filter the results by the supplied user agent
 * @apiParam (Query Parameter) {String="asc","desc"} direction Direction to order the results by (ascending or descending)
 * @apiParam (Query Parameter) {Integer{1...200}} per_page Number of results per request
 * @apiParam (Query Parameter) {Integer} page Current page of results
 */

/**
 * @apiDefine Stats
 *
 * @apiSuccess {Object[]} items Array of log objects
 * @apiSuccess {Integer} items.date Date
 * @apiSuccess {String} items.daily_hits Number of hits registered in date
 * @apiSuccess {Integer} items.url The source URL
 * @apiSuccess {Integer} items.sent_to The target URL 
 * @apiSuccess {Integer} total Number of items
 *
 * @apiSuccessExample {json} Success 200:
 *     HTTP/1.1 200 OK
 *     {
 *       "items": [
 *           {
 *             "date": "14 January 2020",
 *             "daily_hits": 45,
 *             "url": "/the-url",
 *             "sent_to": "/another-url,
 *           }
 *       ],
 *       "total": 1
 *     }
 */

class Redirection_Api_Stats extends Redirection_Api_Filter_Route {
	public function __construct( $namespace ) {
		$dates = [ 'startDate', 'endDate' ];
		$filters = [ 'url-exact', 'url' ];

		register_rest_route( $namespace, '/stats', array(
			'args' => $this->get_filter_args( $dates, $filters ),
			$this->get_route( WP_REST_Server::READABLE, 'route_stats', [ $this, 'permission_callback_manage' ] ),
		) );

	}

	public function permission_callback_manage( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_LOG_MANAGE );
	}

	public function route_stats( WP_REST_Request $request ) {
		return $this->get_stats( $request->get_params() );
	}

	private function get_stats( array $params ) {
		return RE_Stats::get( 'redirection_logs', 'RE_Stats', $params );
	}
}
