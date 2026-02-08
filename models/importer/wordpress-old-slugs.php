<?php

/**
 * @phpstan-import-type ImporterInfo from Red_Plugin_Importer
 */
class Red_WordPressOldSlug_Importer extends Red_Plugin_Importer {
	/**
	 * Import redirects for WordPress old slugs.
	 *
	 * @param int $group_id Target group ID.
	 * @return int Number of imported redirects.
	 */
	public function import_plugin( $group_id ) {
		global $wpdb;

		$count = 0;
		$redirects = $wpdb->get_results(
			"SELECT {$wpdb->prefix}postmeta.* FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta.post_id " .
			"WHERE {$wpdb->prefix}postmeta.meta_key = '_wp_old_slug' AND {$wpdb->prefix}postmeta.meta_value != '' AND {$wpdb->prefix}posts.post_status='publish' AND {$wpdb->prefix}posts.post_type IN ('page', 'post')"
		);

		foreach ( $redirects as $redirect ) {
			$item = $this->create_for_item( $group_id, $redirect );

			if ( $item instanceof Red_Item ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Create a Redirection item for a WordPress old slug row.
	 *
	 * @param int      $group_id Target group ID.
	 * @param stdClass $redirect Row from postmeta/posts join.
	 * @return Red_Item|WP_Error|false Created redirect, error, or false on permalink error.
	 */
	private function create_for_item( $group_id, $redirect ) {
		$new = get_permalink( $redirect->post_id );
		if ( $new === false ) {
			return false;
		}

		$new_path = wp_parse_url( $new, PHP_URL_PATH );
		if ( $new_path === false || $new_path === null ) {
			return false;
		}

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

		return Red_Item::create( $data );
	}

	/**
	 * Get importer summary for WordPress old slugs.
	 *
	 * @return ImporterInfo|false
	 */
	public function get_data() {
		global $wpdb;

		$total = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta.post_id WHERE {$wpdb->prefix}postmeta.meta_key = '_wp_old_slug' AND {$wpdb->prefix}postmeta.meta_value != '' AND {$wpdb->prefix}posts.post_status='publish' AND {$wpdb->prefix}posts.post_type IN ('page', 'post')"
		);

		if ( $total !== null && intval( $total, 10 ) > 0 ) {
			return array(
				'id' => 'wordpress-old-slugs',
				'name' => __( 'Default WordPress "old slugs"', 'redirection' ),
				'total' => intval( $total, 10 ),
			);
		}

		return false;
	}
}
