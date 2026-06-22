<?php

namespace Redirection\FileIO\Format;

use Redirection\FileIO\FileIO;

/**
 * @phpstan-import-type GroupJson from \Red_Group
 */
class Json extends FileIO {
	public function force_download() {
		parent::force_download();

		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename="' . $this->export_filename( 'json' ) . '"' );
	}

	/**
	 * @param array<\Red_Item> $items
	 * @param array<GroupJson> $groups
	 * @return string
	 */
	public function get_data( array $items, array $groups ) {
		$version = red_get_plugin_data( dirname( __DIR__, 3 ) . '/redirection.php' );

		$data = [
			'plugin' => [
				'version' => trim( $version['Version'] ),
				'date' => gmdate( 'r' ),
			],
			'groups' => $groups,
			'redirects' => array_map(
				static function ( $item ) {
					return $item->to_json();
				},
				$items
			),
		];

		return wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . PHP_EOL;
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

		$group_map = [];

		if ( isset( $json['groups'] ) ) {
			foreach ( $json['groups'] as $json_group ) {
				$old_group_id = $json_group['id'];
				$group_map[ $old_group_id ] = $this->get_group_id( $old_group_id, $json_group );
			}
		}

		unset( $json['groups'] );

		if ( isset( $json['redirects'] ) ) {
			foreach ( $json['redirects'] as $pos => $redirect ) {
				unset( $redirect['id'] );

				if ( ! isset( $group_map[ $redirect['group_id'] ] ) ) {
					$group_map[ $redirect['group_id'] ] = $this->get_group_id( $redirect['group_id'] );
				}

				if ( $redirect['match_type'] === 'url' && isset( $redirect['action_data'] ) && ! is_array( $redirect['action_data'] ) ) {
					$redirect['action_data'] = [ 'url' => $redirect['action_data'] ];
				}

				$redirect['group_id'] = $group_map[ $redirect['group_id'] ];
				$created = \Red_Item::create( $redirect );

				if ( $created instanceof \Red_Item ) {
					$count++;
				}

				unset( $json['redirects'][ $pos ] );
				$wpdb->queries = [];
				$wpdb->num_queries = 0;
			}
		}

		return $count;
	}

	/**
	 * @param int|string $group_id
	 * @param array<string, mixed>|null $group
	 * @return int
	 */
	private function get_group_id( $group_id, $group = null ) {
		$group_id = intval( $group_id, 10 );
		$existing = \Red_Group::get( $group_id );

		if ( $existing !== false ) {
			return $existing->get_id();
		}

		if ( $group !== null ) {
			$created = \Red_Group::create( $group['name'], $group['module_id'], $group['enabled'] ? true : false );
			if ( $created !== false ) {
				return $created->get_id();
			}
		}

		$created = \Red_Group::create( 'Group', 1 );
		if ( $created !== false ) {
			return $created->get_id();
		}

		return 0;
	}
}

if ( ! class_exists( 'Red_Json_File', false ) ) {
	\class_alias( '\Redirection\FileIO\Format\Json', 'Red_Json_File' );
}
