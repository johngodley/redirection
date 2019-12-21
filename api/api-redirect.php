<?php

/**
 * @api {get} /redirection/v1/redirect Get redirects
 * @apiName GetRedirects
 * @apiDescription Get a paged list of redirects based after applying a set of filters and result ordering.
 * @apiGroup Redirect
 *
 * @apiUse RedirectQueryParams
 *
 * @apiUse RedirectList
 * @apiUse 401Error
 * @apiUse 404Error
 */

/**
 * @api {post} /redirection/v1/redirect Create redirect
 * @apiName CreateRedirect
 * @apiDescription Create a new redirect, and return a paged list of redirects.
 * @apiGroup Redirect
 *
 * @apiUse RedirectItem
 * @apiUse RedirectQueryParams
 *
 * @apiUse RedirectList
 * @apiUse 401Error
 * @apiUse 404Error
 * @apiError (Error 400) redirect_create_failed Failed to create redirect
 * @apiErrorExample {json} 404 Error Response:
 *     HTTP/1.1 400 Bad Request
 *     {
 *       "code": "redirect_create_failed",
 *       "message": "Failed to create redirect"
 *     }
 */

/**
 * @api {post} /redirection/v1/redirect/:id Update redirect
 * @apiName UpdateRedirect
 * @apiDescription Update an existing redirect.
 * @apiGroup Redirect
 *
 * @apiParam (URL) {Integer} :id Redirect ID to update
 *
 * @apiUse RedirectItem
 *
 * @apiUse RedirectList
 * @apiUse 401Error
 * @apiUse 404Error
 * @apiError (Error 400) redirect_update_failed Failed to update redirect
 * @apiErrorExample {json} 404 Error Response:
 *     HTTP/1.1 400 Bad Request
 *     {
 *       "code": "redirect_update_failed",
 *       "message": "Failed to update redirect"
 *     }
 */

/**
 * @api {post} /redirection/v1/bulk/redirect/:type Bulk redirect action
 * @apiName BulkAction
 * @apiDescription Enable, disable, and delete a set of redirects. The endpoint will return the next page of results after.
 * performing the action, based on the supplied query parameters. This information can be used to refresh a list displayed to the client.
 * @apiGroup Redirect
 *
 * @apiParam (URL) {String="delete","enable","disable"} :type Type of bulk action that is applied to every group ID.
 *
 * @apiParam (Query Parameter) {Integer[]} items Array of redirect IDs to perform the action on
 * @apiUse RedirectQueryParams
 *
 * @apiUse RedirectList
 * @apiUse 401Error
 * @apiUse 404Error
 * @apiUse 400MissingError
 * @apiError (Error 400) redirect_invalid_items Invalid array of items
 * @apiErrorExample {json} 404 Error Response:
 *     HTTP/1.1 400 Bad Request
 *     {
 *       "code": "redirect_invalid_items",
 *       "message": "Invalid array of items"
 *     }
 */

/**
 * @apiDefine RedirectItem Redirect
 * All data associated with a redirect
 *
 * @apiParam {String="enabled","disabled"} status Status of the redirect
 * @apiParam {Integer} position Redirect position, used to determine order multiple redirects occur
 * @apiParam {Object} match_data Additional match parameters
 * @apiParam {Object} match_data.source Match against the source
 * @apiParam {Boolean} match_data.source.flag_regex `true` for regular expression, `false` otherwise
 * @apiParam {String="ignore","exact","pass"} match_data.source.flag_query Which query parameter matching to use
 * @apiParam {Boolean} match_data.source.flag_case] `true` for case insensitive matches, `false` otherwise
 * @apiParam {Boolean} match_data.source.flag_trailing] `true` to ignore trailing slashes, `false` otherwise
 * @apiParam {Boolean} regex True for regular expression, `false` otherwise
 * @apiParam {String} url The source URL
 * @apiParam {String="url","referrer","agent","login","header","custom","cookie","role","server","ip","page","language"} match_type What URL matching to use
 * @apiParam {String} [title] A descriptive title for the redirect, or empty for no title
 * @apiParam {Integer} group_id The group this redirect belongs to
 * @apiParam {String} action_type What to do when the URL is matched
 * @apiParam {Integer} action_code The HTTP code to return
 * @apiParam {String} action_data Any data associated with the `action_type` and `match_type`. For example, the target URL
 */

/**
 * @apiDefine RedirectList A list of redirects
 * A list of redirects
 *
 * @apiSuccess {Object[]} items Array of redirect objects
 * @apiSuccess {Integer} items.id ID of redirect
 * @apiSuccess {String} items.url Source URL to match
 * @apiSuccess {String} items.match_url Match URL
 * @apiSuccess {Object} items.match_data Match against the source
 * @apiSuccess {String} items.match_type What URL matching to use
 * @apiSuccess {String} items.action_type What to do when the URL is matched
 * @apiSuccess {Integer} items.action_code The HTTP code to return
 * @apiSuccess {String} items.action_data Any data associated with the action_type. For example, the target URL
 * @apiSuccess {String} items.title Optional A descriptive title for the redirect, or empty for no title
 * @apiSuccess {String} items.hits Number of hits this redirect has received
 * @apiSuccess {String} items.regex True for regular expression, false otherwise
 * @apiSuccess {String} items.group_id The group this redirect belongs to
 * @apiSuccess {String} items.position Redirect position, used to determine order multiple redirects occur
 * @apiSuccess {String} items.last_access The date this redirect was last hit
 * @apiSuccess {String} items.status Status of the redirect
 * @apiSuccess {Integer} total Number of items
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "items": [
 *           {
 *             id: 3,
 *             url: "/source",
 *             match_url: "/source",
 *             match_data: "",
 *             action_code: "",
 *             action_type: "",
 *             action_data: "",
 *             match_type: "url",
 *             title: "Redirect title",
 *             hits: 5,
 *             regex: true,
 *             group_id: 15,
 *             position: 1,
 *             last_access: "2019-01-01 01:01:01"
 *             status: "enabled"
 *           }
 *       ],
 *       "total": 1
 *     }
 */

/**
 * @apiDefine RedirectQueryParams
 *
 * @apiParam (Query Parameter) {String="enabled","disabled"} filterBy[status] Filter the results by the supplied status
 * @apiParam (Query Parameter) {String} filterBy[url] Filter the results by the supplied URL
 * @apiParam (Query Parameter) {String="regular","plain"} filterBy[url-match] Filter the results by `regular` expressions or non regular expressions
 * @apiParam (Query Parameter) {String} filterBy[match] Filter the results by the supplied match type
 * @apiParam (Query Parameter) {String} filterBy[action] Filter the results by the supplied action type
 * @apiParam (Query Parameter) {Integer} filterBy[http] Filter the results by the supplied redirect HTTP code
 * @apiParam (Query Parameter) {String="year","month","all"} filterBy[access] Filter the results by how long the redirect was last accessed
 * @apiParam (Query Parameter) {String} filterBy[target] Filter the results by the supplied redirect target
 * @apiParam (Query Parameter) {String} filterBy[title] Filter the results by the supplied redirect title
 * @apiParam (Query Parameter) {Integer} filterBy[group] Filter the results by the supplied redirect group ID
 * @apiParam (Query Parameter) {Integer="1","2","3"} filterBy[module] Filter the results by the supplied module ID
 * @apiParam (Query Parameter) {String="url","last_count","last_access","position","id"} orderby Order in which results are returned
 * @apiParam (Query Parameter) {String="asc","desc"} direction Direction to order the results by (ascending or descending)
 * @apiParam (Query Parameter) {Integer{1...200}} per_page Number of results per request
 * @apiParam (Query Parameter) {Integer} page Current page of results
 */
class Redirection_Api_Redirect extends Redirection_Api_Filter_Route {
	public function __construct( $namespace ) {
		$orders = [ 'url', 'last_count', 'last_access', 'position', 'id' ];
		$filters = [ 'status', 'url-match', 'match', 'action', 'http', 'access', 'url', 'target', 'title', 'group' ];

		register_rest_route( $namespace, '/redirect', array(
			'args' => $this->get_filter_args( $orders, $filters ),
			$this->get_route( WP_REST_Server::READABLE, 'route_list', [ $this, 'permission_callback_manage' ] ),
			$this->get_route( WP_REST_Server::EDITABLE, 'route_create', [ $this, 'permission_callback_add' ] ),
		) );

		register_rest_route( $namespace, '/redirect/(?P<id>[\d]+)', array(
			$this->get_route( WP_REST_Server::EDITABLE, 'route_update', [ $this, 'permission_callback_add' ] ),
		) );

		register_rest_route( $namespace, '/redirect/post', array(
			$this->get_route( WP_REST_Server::READABLE, 'route_match_post', [ $this, 'permission_callback_manage' ] ),
			'args' => [
				'text' => [
					'description' => 'Text to match',
					'type' => 'string',
				],
			],
		) );

		$this->register_bulk( $namespace, '/bulk/redirect/(?P<bulk>delete|enable|disable|reset)', $orders, 'route_bulk', [ $this, 'permission_callback_bulk' ] );
	}

	public function permission_callback_manage( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_REDIRECT_MANAGE );
	}

	public function permission_callback_bulk( WP_REST_Request $request ) {
		if ( $request['bulk'] === 'delete' ) {
			return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_REDIRECT_DELETE );
		}

		return $this->permission_callback_add( $request );
	}

	public function permission_callback_add( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_REDIRECT_ADD );
	}

	public function route_list( WP_REST_Request $request ) {
		return Red_Item::get_filtered( $request->get_params() );
	}

	public function route_create( WP_REST_Request $request ) {
		$params = $request->get_params();
		$urls = array();

		if ( isset( $params['url'] ) ) {
			$urls = array( $params['url'] );

			if ( is_array( $params['url'] ) ) {
				$urls = $params['url'];
			}

			foreach ( $urls as $url ) {
				$params['url'] = $url;
				$redirect = Red_Item::create( $params );

				if ( is_wp_error( $redirect ) ) {
					return $this->add_error_details( $redirect, __LINE__ );
				}
			}
		}

		return $this->route_list( $request );
	}

	public function route_update( WP_REST_Request $request ) {
		$params = $request->get_params();
		$redirect = Red_Item::get_by_id( intval( $params['id'], 10 ) );

		if ( $redirect ) {
			$result = $redirect->update( $params );

			if ( is_wp_error( $result ) ) {
				return $this->add_error_details( $result, __LINE__ );
			}

			return array( 'item' => $redirect->to_json() );
		}

		return $this->add_error_details( new WP_Error( 'redirect_update_failed', 'Invalid redirect details' ), __LINE__ );
	}

	public function route_bulk( WP_REST_Request $request ) {
		$action = $request['bulk'];
		$items = explode( ',', $request['items'] );

		if ( is_array( $items ) ) {
			foreach ( $items as $item ) {
				$redirect = Red_Item::get_by_id( intval( $item, 10 ) );

				if ( $redirect ) {
					if ( $action === 'delete' ) {
						$redirect->delete();
					} elseif ( $action === 'disable' ) {
						$redirect->disable();
					} elseif ( $action === 'enable' ) {
						$redirect->enable();
					} elseif ( $action === 'reset' ) {
						$redirect->reset();
					}
				}
			}

			return $this->route_list( $request );
		}

		return $this->add_error_details( new WP_Error( 'redirect_invalid_items', 'Invalid array of items' ), __LINE__ );
	}

	public function route_match_post( WP_REST_Request $request ) {
		$params = $request->get_params();
		$search = isset( $params['text'] ) ? $params['text'] : false;
		$results = [];

		if ( $search ) {
			global $wpdb;

			$posts = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID,post_title,post_name FROM $wpdb->posts WHERE post_status='publish' AND (post_title LIKE %s OR post_name LIKE %s) " .
					"AND post_type NOT IN ('nav_menu_item','wp_block','oembed_cache')",
					'%' . $wpdb->esc_like( $search ) . '%', '%' . $wpdb->esc_like( $search ) . '%'
				)
			);

			foreach ( (array) $posts as $post ) {
				$results[] = [
					'title' => $post->post_title,
					'slug' => $post->post_name,
					'url' => get_permalink( $post->ID ),
				];
			}
		}

		return $results;
	}
}
