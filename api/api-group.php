<?php

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
	 * @param non-falsy-string $api_namespace Namespace.
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
