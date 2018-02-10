<?php

class Redirection_Api_Redirect extends Redirection_Api_Filter_Route {
	public function __construct( $namespace ) {
		$filters = array( 'url', 'group' );
		$orders = array( 'url', 'last_count', 'last_access', 'position', 'id' );

		register_rest_route( $namespace, '/redirect', array(
			'args' => $this->get_filter_args( $filters, $orders ),
			$this->get_route( WP_REST_Server::READABLE, 'route_list' ),
			$this->get_route( WP_REST_Server::EDITABLE, 'route_create' ),
		) );

		register_rest_route( $namespace, '/redirect/(?P<id>[\d]+)', array(
			$this->get_route( WP_REST_Server::EDITABLE, 'route_update' ),
		) );

		$this->register_bulk( $namespace, '/bulk/redirect/(?P<bulk>delete|enable|disable|reset)', $filters, $orders, 'route_bulk' );
	}

	public function route_list( WP_REST_Request $request ) {
		return Red_Item::get_filtered( $request->get_params() );
	}

	public function route_create( WP_REST_Request $request ) {
		$redirect = Red_Item::create( $request->get_params() );

		if ( is_wp_error( $redirect ) ) {
			return $this->add_error_details( $redirect, __LINE__ );
		}

		return $this->route_list( $request );;
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

		return $this->add_error_details( new WP_Error( 'redirect', 'Invalid redirect details' ), __LINE__ );
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
					} else if ( $action === 'disable' ) {
						$redirect->disable();
					} else if ( $action === 'enable' ) {
						$redirect->enable();
					} else if ( $action === 'reset' ) {
						$redirect->reset();
					}
				}
			}

			return $this->route_list( $request );
		}

		return $this->add_error_details( new WP_Error( 'redirect', 'Invalid array of items' ), __LINE__ );
	}
}
