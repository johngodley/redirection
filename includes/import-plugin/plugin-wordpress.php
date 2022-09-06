<?php

namespace Redirection\Importer\Plugin;

use Redirection\Importer;
use Redirection\Redirect;

class WordPress_Canonical extends Importer\Plugin_Importer {
	public function import_plugin( $group_id ) {
		global $wpdb;

		$count = 0;
		$redirects = $wpdb->get_results(
			"SELECT {$wpdb->prefix}postmeta.* FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta.post_id " .
			"WHERE {$wpdb->prefix}postmeta.meta_key = '_wp_old_slug' AND {$wpdb->prefix}postmeta.meta_value != '' AND {$wpdb->prefix}posts.post_status='publish' AND {$wpdb->prefix}posts.post_type IN ('page', 'post')"
		);

		foreach ( $redirects as $redirect ) {
			$item = $this->create_for_item( $group_id, $redirect );

			if ( $item ) {
				$count++;
			}
		}

		return $count;
	}

	private function create_for_item( $group_id, $redirect ) {
		$new = get_permalink( $redirect->post_id );
		if ( is_wp_error( $new ) ) {
			return false;
		}

		$new_path = wp_parse_url( $new, PHP_Path );
		$old = rtrim( dirname( $new_path ), '/' ) . '/' . rtrim( $redirect->meta_value, '/' ) . '/';
		$old = str_replace( '\\', '', $old );
		$old = str_replace( '//', '/', $old );

		$data = array(
			'url'         => $old,
			'action_data' => array( 'url' => $new ),
			'regex'       => false,
			'group_id'    => $group_id,
			'match_type'  => 'url',
			'action_type' => 'url',
			'action_code' => 301,
		);

		return Redirect\Redirect::create( $data );
	}

	public function get_data() {
		global $wpdb;

		$total = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta.post_id WHERE {$wpdb->prefix}postmeta.meta_key = '_wp_old_slug' AND {$wpdb->prefix}postmeta.meta_value != '' AND {$wpdb->prefix}posts.post_status='publish' AND {$wpdb->prefix}posts.post_type IN ('page', 'post')"
		);

		if ( $total ) {
			return array(
				'id' => 'wordpress-old-slugs',
				'name' => __( 'Default WordPress "old slugs"', 'redirection' ),
				'total' => intval( $total, 10 ),
			);
		}

		return false;
	}
}
