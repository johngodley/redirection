<?php

class Red_Json_File extends Red_FileIO {
	public function force_download() {
		parent::force_download();

		$filename = 'redirection-'.date_i18n( get_option( 'date_format' ) ).'.json';

		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename="'.$filename.'"' );
	}

	public function get_data( array $items, array $groups ) {
		$version = get_plugin_data( dirname( dirname( __FILE__ ) ).'/redirection.php' );

		$items = array(
			'plugin' => array(
				'version' => trim( $version['Version'] ),
				'date' => date( 'r' ),
			),
			'groups' => $groups,
			'redirects' => array_map( function( $item ) {
				return $item->to_json();
			}, $items ),
		);

		return json_encode( $items, JSON_PRETTY_PRINT ).PHP_EOL;
	}

	public function load( $group, $filename, $data ) {
		$count = 0;
		$json = @json_decode( $data );
		if ( $json === false ) {
			return 0;
		}

		// Import groups
		$groups = array();
		$group_map = array();

		if ( isset( $json->groups ) ) {
			foreach ( $json->groups as $group ) {
				$old_group_id = $group->id;
				unset( $group->id );

				$group = Red_Group::create( $group->name, $group->module_id );
				if ( $group ) {
					$group_map[ $old_group_id ] = $group->get_id();
				}
			}
		}

		// Import redirects
		if ( isset( $json->redirects ) ) {
			foreach ( $json->redirects as $redirect ) {
				unset( $redirect->id );

				if ( ! isset( $group_map[ $redirect->group_id ] ) ) {
					$group_map[ $redirect->group_id ] = Red_Group::create( 'Group', 1 );
				}

				$redirect->group_id = $group_map[ $redirect->group_id ];
				$redirect = Red_Item::create( (array)$redirect );
				$count++;
			}
		}

		return $count;
	}
}
