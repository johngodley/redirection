<?php

class Redirection_Api_404 extends Redirection_Api_Filter_Route {
	public function __construct( $namespace ) {
		$filters = array( 'ip', 'url', 'url-exact' );

		register_rest_route( $namespace, '/404', array(
			'args' => $this->get_filter_args( $filters, $filters ),
			$this->get_route( WP_REST_Server::READABLE, 'route_404' ),
			$this->get_route( WP_REST_Server::EDITABLE, 'route_delete_all' ),
		) );

		$this->register_bulk( $namespace, '/bulk/404/(?P<bulk>delete)', $filters, $filters, 'route_bulk' );
	}

	public function route_404( WP_REST_Request $request ) {
		return RE_Filter_Log::get( 'redirection_404', 'RE_404', $request->get_params() );
	}

	public function route_bulk( WP_REST_Request $request ) {
		$items = explode( ',', $request['items'] );

		if ( is_array( $items ) ) {
			$items = array_map( 'intval', $items );
			array_map( array( 'RE_404', 'delete' ), $items );
			return $this->route_404( $request );
		}

		return $this->add_error_details( new WP_Error( 'redirect', 'Invalid array of items' ), __LINE__ );
	}

	public function route_delete_all( WP_REST_Request $request ) {
		$params = $request->get_params();
		$filter = false;
		$filterBy = false;

		if ( isset( $params['filter'] ) ) {
			$filter = $params['filter'];
		}

		if ( isset( $params['filterBy'] ) ) {
			$filterBy = $params['filterBy'];
		}

		RE_404::delete_all( $filterBy, $filter );

		unset( $params['filterBy'] );
		unset( $params['filter'] );
		unset( $params['page'] );

		return RE_Filter_Log::get( 'redirection_404', 'RE_404', $params );
	}
}
