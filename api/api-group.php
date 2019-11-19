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
 *
 * @apiSuccess {String} item The updated group
 * @apiSuccess {Integer} item.id ID of group
 * @apiSuccess {String} item.name Name of this group
 * @apiSuccess {Boolean} item.enabled `true` if group (and redirects) are enabled, `false` otherwise
 * @apiSuccess {Integer} item.redirects Number of redirects in this group
 * @apiSuccess {String} item.moduleName Name of the module this group belongs to
 * @apiSuccess {Integer} item.module_id ID of the module this group belongs to
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
 * @api {post} /redirection/v1/bulk/group/:type Bulk group action
 * @apiName BulkAction
 * @apiDescription Enable, disable, and delete a set of groups. The endpoint will return the next page of results after.
 * performing the action, based on the supplied query parameters. This information can be used to refresh a list displayed to the client.
 * @apiGroup Group
 *
 * @apiParam (URL) {String="delete","enable","disable"} :type Type of bulk action that is applied to every group ID.
 * Enabling or disabling a group will also enable or disable all redirects in that group
 *
 * @apiParam (Query Parameter) {Integer[]} items Array of group IDs to perform the action on
 * @apiUse GroupQueryParams
 *
 * @apiUse GroupList
 * @apiUse 401Error
 * @apiUse 404Error
 * @apiUse 400MissingError
 * @apiError (Error 400) redirect_group_invalid_items Invalid array of items
 * @apiErrorExample {json} 404 Error Response:
 *     HTTP/1.1 400 Bad Request
 *     {
 *       "code": "redirect_group_invalid_items",
 *       "message": "Invalid array of items"
 *     }
 */

/**
 * @apiDefine GroupQueryParams
 *
 * @apiParam (Query Parameter) {String} filterBy[name] Filter the results by the supplied name
 * @apiParam (Query Parameter) {String="enabled","disabled"} filterBy[status] Filter the results by the supplied status
 * @apiParam (Query Parameter) {Integer="1","2","3"} filterBy[module] Filter the results by the supplied module ID
 * @apiParam (Query Parameter) {String="name"} orderby Order in which results are returned
 * @apiParam (Query Parameter) {String="asc","desc"} direction Direction to order the results by (ascending or descending)
 * @apiParam (Query Parameter) {Integer{1...200}} per_page Number of results per request
 * @apiParam (Query Parameter) {Integer} page Current page of results
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
class Redirection_Api_Group extends Redirection_Api_Filter_Route {
	public function __construct( $namespace ) {
		$orders = [ 'name', 'id' ];
		$filters = [ 'status', 'module', 'name' ];

		register_rest_route( $namespace, '/group', array(
			'args' => $this->get_filter_args( $orders, $filters ),
			$this->get_route( WP_REST_Server::READABLE, 'route_list', [ $this, 'permission_callback_manage' ] ),
			array_merge(
				$this->get_route( WP_REST_Server::EDITABLE, 'route_create', [ $this, 'permission_callback_add' ] ),
				array( 'args' => $this->get_group_args() )
			),
		) );

		register_rest_route( $namespace, '/group/(?P<id>[\d]+)', array(
			'args' => $this->get_group_args(),
			$this->get_route( WP_REST_Server::EDITABLE, 'route_update', [ $this, 'permission_callback_add' ] ),
		) );

		$this->register_bulk( $namespace, '/bulk/group/(?P<bulk>delete|enable|disable)', $orders, 'route_bulk', [ $this, 'permission_callback_bulk' ] );
	}

	// Access to group data is required by the CAP_GROUP_MANAGE and CAP_REDIRECT_MANAGE caps
	public function permission_callback_manage( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_GROUP_MANAGE ) || Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_REDIRECT_MANAGE );
	}

	public function permission_callback_bulk( WP_REST_Request $request ) {
		if ( $request['bulk'] === 'delete' ) {
			return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_GROUP_DELETE );
		}

		return $this->permission_callback_add( $request );
	}

	public function permission_callback_add( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_GROUP_ADD );
	}

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
		);
	}

	public function route_list( WP_REST_Request $request ) {
		return Red_Group::get_filtered( $request->get_params() );
	}

	public function route_create( WP_REST_Request $request ) {
		$params = $request->get_params( $request );
		$group = Red_Group::create( isset( $params['name'] ) ? $params['name'] : '', isset( $params['moduleId'] ) ? $params['moduleId'] : 0 );

		if ( $group ) {
			return Red_Group::get_filtered( $params );
		}

		return $this->add_error_details( new WP_Error( 'redirect_group_invalid', 'Invalid group or parameters' ), __LINE__ );
	}

	public function route_update( WP_REST_Request $request ) {
		$params = $request->get_params( $request );
		$group = Red_Group::get( intval( $request['id'], 10 ) );

		if ( $group ) {
			$result = $group->update( $params );

			if ( $result ) {
				return array( 'item' => $group->to_json() );
			}
		}

		return $this->add_error_details( new WP_Error( 'redirect_group_invalid', 'Invalid group details' ), __LINE__ );
	}

	public function route_bulk( WP_REST_Request $request ) {
		$action = $request['bulk'];
		$items = explode( ',', $request['items'] );

		if ( is_array( $items ) ) {
			foreach ( $items as $item ) {
				$group = Red_Group::get( intval( $item, 10 ) );

				if ( $group ) {
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

		return $this->add_error_details( new WP_Error( 'redirect_group_invalid_items', 'Invalid array of items' ), __LINE__ );
	}
}
