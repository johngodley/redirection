<?php

class Redirection_Api_Group extends Redirection_Api_Filter_Route {
	public function __construct( $namespace ) {
		$filters = array( 'name', 'module' );
		$orders = array( 'name', 'id' );

		register_rest_route( $namespace, '/group', array(
			'args' => $this->get_filter_args( $filters, $orders ),
			$this->get_route( WP_REST_Server::READABLE, 'route_list' ),
			array_merge(
				$this->get_route( WP_REST_Server::EDITABLE, 'route_create' ),
				array( 'args' => $this->get_group_args() )
			),
		) );

		register_rest_route( $namespace, '/group/(?P<id>[\d]+)', array(
			'args' => $this->get_group_args(),
			$this->get_route( WP_REST_Server::EDITABLE, 'route_update' ),
		) );

		$this->register_bulk( $namespace, '/bulk/group/(?P<bulk>delete|enable|disable)', $filters, $orders, 'route_bulk' );
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

		return $this->add_error_details( new WP_Error( 'redirect', 'Invalid group or parameters' ), __LINE__ );
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

		return $this->add_error_details( new WP_Error( 'redirect', 'Invalid group details' ), __LINE__ );
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
					} else if ( $action === 'disable' ) {
						$group->disable();
					} else if ( $action === 'enable' ) {
						$group->enable();
					}
				}
			}

			return $this->route_list( $request );
		}

		return $this->add_error_details( new WP_Error( 'redirect', 'Invalid array of items' ), __LINE__ );
	}
}
