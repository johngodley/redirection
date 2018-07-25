<?php

class Redirection_Api_Settings extends Redirection_Api_Route {
	public function __construct( $namespace ) {
		register_rest_route( $namespace, '/setting', array(
			$this->get_route( WP_REST_Server::READABLE, 'route_settings' ),
			$this->get_route( WP_REST_Server::EDITABLE, 'route_save_settings' ),
		) );
	}

	public function route_settings( WP_REST_Request $request ) {
		if ( ! function_exists( 'get_home_path' ) ) {
			include_once ABSPATH . '/wp-admin/includes/file.php';
		}

		return array(
			'settings' => red_get_options(),
			'groups' => $this->groups_to_json( Red_Group::get_for_select() ),
			'installed' => get_home_path(),
			'canDelete' => ! is_multisite(),
			'post_types' => red_get_post_types(),
		);
	}

	public function route_save_settings( WP_REST_Request $request ) {
		red_set_options( $request->get_params() );

		return $this->route_settings( $request );
	}

	private function groups_to_json( $groups, $depth = 0 ) {
		$items = array();

		foreach ( $groups as $text => $value ) {
			if ( is_array( $value ) && $depth === 0 ) {
				$items[] = (object)array( 'text' => $text, 'value' => $this->groups_to_json( $value, 1 ) );
			} else {
				$items[] = (object)array( 'text' => $value, 'value' => $text );
			}
		}

		return $items;
	}
}
