<?php

/**
	* @phpstan-import-type GroupJson from Red_Group
*/

class Red_Json_File extends Red_FileIO {
	public function force_download() {
		parent::force_download();

		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename="' . $this->export_filename( 'json' ) . '"' );
	}

	/**
	 * @param array<Red_Item> $items
	 * @param array<GroupJson> $groups
	 * @return string
	 */
	public function get_data( array $items, array $groups ) {
		$version = red_get_plugin_data( dirname( __DIR__ ) . '/redirection.php' );

		$items = array(
			'plugin' => array(
				'version' => trim( $version['Version'] ),
				'date' => gmdate( 'r' ),
			),
			'groups' => $groups,
			'redirects' => array_map(
				function ( $item ) {
					return $item->to_json();
				},
				$items
			),
		);

		return wp_json_encode( $items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . PHP_EOL;
	}

	/**
	 * @param int $group Group ID to import into.
	 * @param string $filename Path to the file to import.
	 * @param string|false $data File contents (or false if not pre-loaded).
	 * @return int
	 */
	public function load( $group, $filename, $data ) {
		global $wpdb;

		if ( $data === false ) {
			return 0;
		}

		$count = 0;
		/** @var array<string, mixed>|false $json */
		$json = @json_decode( $data, true );
		if ( $json === false ) {
			return 0;
		}

		// Import groups
		$group_map = array();

		if ( isset( $json['groups'] ) ) {
			foreach ( $json['groups'] as $json_group ) {
				$old_group_id = $json_group['id'];
				unset( $json_group['id'] );

				$json_group = Red_Group::create( $json_group['name'], $json_group['module_id'], $json_group['enabled'] ? true : false );
				if ( $json_group !== false ) {
					$group_map[ $old_group_id ] = $json_group->get_id();
				}
			}
		}

		unset( $json['groups'] );

		// Import redirects
		if ( isset( $json['redirects'] ) ) {
			foreach ( $json['redirects'] as $pos => $redirect ) {
				unset( $redirect['id'] );

				if ( ! isset( $group_map[ $redirect['group_id'] ] ) ) {
					$new_group = Red_Group::create( 'Group', 1 );
					if ( $new_group !== false ) {
						$group_map[ $redirect['group_id'] ] = $new_group->get_id();
					}
				}

				if ( $redirect['match_type'] === 'url' && isset( $redirect['action_data'] ) && ! is_array( $redirect['action_data'] ) ) {
					$redirect['action_data'] = array( 'url' => $redirect['action_data'] );
				}

				$redirect['group_id'] = $group_map[ $redirect['group_id'] ];
				$created = Red_Item::create( $redirect );

				if ( $created instanceof Red_Item ) {
					$count++;
				}

				// Helps reduce memory usage
				unset( $json['redirects'][ $pos ] );
				$wpdb->queries = array();
				$wpdb->num_queries = 0;
			}
		}

		return $count;
	}
}
