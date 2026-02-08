<?php

/**
 * @api {get} /redirection/v1/group Get groups
 * @apiName GetGroups
 * @apiDescription Get a paged list of groups based after applying a set of filters and result ordering.
 * @apiGroup Group
 *
 * @apiUse GroupQueryParams
 *
 * @apiUse GroupList
 * @apiUse 401Error
 * @apiUse 404Error
 */

/**
 * @api {post} /redirection/v1/group Create group
 * @apiName CreateGroup
 * @apiDescription Create a new group, and return a paged list of groups.
 * @apiGroup Group
 *
 * @apiUse GroupItem
 * @apiUse GroupQueryParams
 *
 * @apiUse GroupList
 * @apiUse 401Error
 * @apiUse 404Error
 * @apiError (Error 400) redirect_group_invalid Invalid group or parameters
 * @apiErrorExample {json} 404 Error Response:
 *     HTTP/1.1 400 Bad Request
 *     {
 *       "code": "redirect_group_invalid",
 *       "message": "Invalid group or parameters"
 *     }
 */

/**
 * @api {post} /redirection/v1/group/:id Update group
 * @apiName UpdateGroup
 * @apiDescription Update an existing group.
 * @apiGroup Group
 *
 * @apiParam (URL) {Integer} :id Group ID to update
 * @apiUse GroupList
 *
 * @apiSuccess {String} item The updated group
 * @apiSuccess {Integer} item.id ID of group
 * @apiSuccess {String} item.name Name of this group
 * @apiSuccess {Boolean} item.enabled `true` if group (and redirects) are enabled, `false` otherwise
 * @apiSuccess {Integer} item.redirects Number of redirects in this group
 * @apiSuccess {String} item.moduleName Name of the module this group belongs to
 * @apiSuccess {Integer} item.module_id ID of the module this group belongs to
 *
 * @apiUse 401Error
 * @apiUse 404Error
 *
 * @apiError (Error 400) redirect_group_invalid Invalid group or parameters
 * @apiErrorExample {json} 404 Error Response:
 *     HTTP/1.1 400 Bad Request
 *     {
 *       "code": "redirect_group_invalid",
 *       "message": "Invalid group or parameters"
 *     }
 */

/**
 * @api {post} /redirection/v1/bulk/group/:type Bulk action
 * @apiName BulkAction
 * @apiDescription Enable, disable, and delete a set of groups. The endpoint will return the next page of results after.
 * performing the action, based on the supplied query parameters. This information can be used to refresh a list displayed to the client.
 * @apiGroup Group
 *
 * @apiParam (URL) {String="delete","enable","disable"} :type Type of bulk action that is applied to every group ID.
 * Enabling or disabling a group will also enable or disable all redirects in that group
 *
 * @apiParam (Query Parameter) {String[]} [items] Array of group IDs to perform the action on
 * @apiParam (Query Parameter) {Boolean=false} [global] Perform action globally using the filter parameters
 * @apiUse GroupQueryParams
 *
 * @apiUse GroupList
 * @apiUse 401Error
 * @apiUse 404Error
 * @apiUse 400MissingError
 */

/**
 * @apiDefine GroupQueryParams
 *
 * @apiParam (Query Parameter) {String} [filterBy[name]] Filter the results by the supplied name
 * @apiParam (Query Parameter) {String="enabled","disabled"} [filterBy[status]] Filter the results by the supplied status
 * @apiParam (Query Parameter) {Integer="1","2","3"} [filterBy[module]] Filter the results by the supplied module ID
 * @apiParam (Query Parameter) {String="name"} [orderby] Order in which results are returned
 * @apiParam (Query Parameter) {String="asc","desc"} [direction=desc] Direction to order the results by (ascending or descending)
 * @apiParam (Query Parameter) {Integer{1...200}} [per_page=25] Number of results per request
 * @apiParam (Query Parameter) {Integer} [page=0] Current page of results
 */

/**
 * @apiDefine GroupItem
 *
 * @apiParam (JSON Body) {String} name Name of the group
 * @apiParam (JSON Body) {Integer="1","2","3"} moduleID Module ID of the group, with 1 being WordPress, 2 is Apache, and 3 is Nginx
 */

/**
 * @apiDefine GroupList
 *
 * @apiSuccess {Object[]} items Array of group objects
 * @apiSuccess {Integer} items.id ID of group
 * @apiSuccess {String} items.name Name of this group
 * @apiSuccess {Boolean} items.enabled `true` if group (and redirects) are enabled, `false` otherwise
 * @apiSuccess {Integer} items.redirects Number of redirects in this group
 * @apiSuccess {String} items.moduleName Name of the module this group belongs to
 * @apiSuccess {Integer} items.module_id ID of the module this group belongs to
 * @apiSuccess {Integer} total Number of items
 *
 * @apiSuccessExample {json} Success 200:
 *     HTTP/1.1 200 OK
 *     {
 *       "items": [
 *           {
 *             "id": 3,
 *             "enabled": true,
 *             "moduleName": "WordPress",
 *             "module_id": 1,
 *             "name": "Redirections",
 *             "redirects": 0,
 *           }
 *       ],
 *       "total": 1
 *     }
 */

/**
 * Group API endpoint
 *
 * @phpstan-type GroupListResponse array{
 *   items: list<array<string, mixed>>,
 *   total: int
 * }
 */
class Redirection_Api_Group extends Redirection_Api_Filter_Route {
	/**
	 * 404 API endpoint constructor
	 *
	 * @param string $api_namespace Namespace.
	 */
	public function __construct( $api_namespace ) {
		$orders = [ 'name', 'id', '' ];
		$filters = [ 'status', 'module', 'name' ];

		// GET /group - List groups
		// POST /group - Create group
		register_rest_route(
			$api_namespace,
			'/group',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_list' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
					'args' => $this->get_filter_args( $orders, $filters ),
				],
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'route_create' ],
					'permission_callback' => [ $this, 'permission_callback_add' ],
					'args' => array_merge(
						$this->get_filter_args( $orders, $filters ),
						$this->get_group_args()
					),
				],
			]
		);

		// POST /group/:id - Update group
		register_rest_route(
			$api_namespace,
			'/group/(?P<id>[\d]+)',
			[
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'route_update' ],
					'permission_callback' => [ $this, 'permission_callback_add' ],
					'args' => $this->get_group_args(),
				],
			]
		);

		// POST /bulk/group/:bulk - Bulk actions on groups
		register_rest_route(
			$api_namespace,
			'/bulk/group/(?P<bulk>delete|enable|disable)',
			[
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'route_bulk' ],
					'permission_callback' => [ $this, 'permission_callback_bulk' ],
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
	 * Access to group data is required by the CAP_GROUP_MANAGE and CAP_REDIRECT_MANAGE caps
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request.
	 * @return bool
	 */
	public function permission_callback_manage( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_GROUP_MANAGE ) || Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_REDIRECT_MANAGE );
	}

	/**
	 * Checks a bulk capability
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request.
	 * @return bool
	 */
	public function permission_callback_bulk( WP_REST_Request $request ) {
		if ( $request['bulk'] === 'delete' ) {
			return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_GROUP_DELETE );
		}

		return $this->permission_callback_add( $request );
	}

	/**
	 * Checks a create capability
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request.
	 * @return bool
	 */
	public function permission_callback_add( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_GROUP_ADD );
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	private function get_group_args() {
		return array(
			'moduleId' => array(
				'description' => 'Module ID',
				'type' => 'integer',
				'minimum' => 0,
				'maximum' => 3,
				'required' => true,
			),
			'name' => array(
				'description' => 'Group name',
				'type' => 'string',
				'required' => true,
			),
			'status' => [
				'description' => 'Status of the group',
			],
		);
	}

	/**
	 * Get group list
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request The request.
	 * @return GroupListResponse
	 */
	public function route_list( WP_REST_Request $request ) {
		return Red_Group::get_filtered( $request->get_params() ); // @phpstan-ignore-line
	}

	/**
	 * Create a group
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request The request.
	 * @return GroupListResponse|WP_Error
	 */
	public function route_create( WP_REST_Request $request ) {
		$params = $request->get_params();
		$name = '';
		$module = 0;

		if ( isset( $params['name'] ) ) {
			$name = sanitize_text_field( $params['name'] );
		}

		if ( isset( $params['moduleId'] ) ) {
			$module = intval( $params['moduleId'], 10 );
		}

		$group = Red_Group::create( $name, $module );

		if ( $group !== false ) {
			return Red_Group::get_filtered( $params ); // @phpstan-ignore-line
		}

		return $this->add_error_details( new WP_Error( 'redirect_group_invalid', 'Invalid group or parameters' ), __LINE__ );
	}

	/**
	 * Update a group
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request The request.
	 * @return array{item: array<string, mixed>}|WP_Error
	 */
	public function route_update( WP_REST_Request $request ) {
		$params = $request->get_params();
		$group = Red_Group::get( intval( $request['id'], 10 ) );

		if ( $group !== false ) {
			$result = $group->update( $params );

			if ( $result !== false ) {
				return array( 'item' => $group->to_json() );
			}
		}

		return $this->add_error_details( new WP_Error( 'redirect_group_invalid', 'Invalid group details' ), __LINE__ );
	}

	/**
	 * Perform action on groups
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request The request.
	 * @return GroupListResponse|WP_Error
	 */
	public function route_bulk( WP_REST_Request $request ) {
		$params = $request->get_params();
		$action = $request['bulk'];

		$items = [];
		if ( isset( $params['items'] ) && is_array( $params['items'] ) ) {
			// Array of integers, sanitized below
			$items = $params['items'];
		} elseif ( isset( $params['global'] ) && $params['global'] !== false ) {
			// Groups have additional actions that fire and so we need to action them individually
			$groups = Red_Group::get_all( $params );
			$items = array_column( $groups, 'id' );
		}

		foreach ( $items as $item ) {
			$group = Red_Group::get( intval( $item, 10 ) );

			if ( is_object( $group ) ) {
				if ( $action === 'delete' ) {
					$group->delete();
				} elseif ( $action === 'disable' ) {
					$group->disable();
				} elseif ( $action === 'enable' ) {
					$group->enable();
				}
			}
		}

		return $this->route_list( $request );
	}
}
