<?php

/**
 * @phpstan-import-type ImporterInfo from Red_Plugin_Importer
 */
class Red_WordPressOldSlug_Importer extends Red_Plugin_Importer {
	/**
	 * @return bool
	 */
	protected function supports_preview() {
		return true;
	}

	/**
	 * @param int $group_id Target group ID.
	 * @param array<string, bool|string> $options Import options.
	 * @phpstan-return array{
	 *   created: int,
	 *   updated: int,
	 *   ignored: int,
	 *   groups_created: int,
	 *   preview: array<int, array{
	 *     source: string,
	 *     target: string,
	 *     code: int,
	 *     regex: bool,
	 *     group: string,
	 *     result: 'created'|'updated'|'ignored',
	 *     redirect_id?: int
	 *   }>
	 * }
	 * @return array{
	 *   created: int,
	 *   updated: int,
	 *   ignored: int,
	 *   groups_created: int,
	 *   preview: array<int, array{
	 *     source: string,
	 *     target: string,
	 *     code: int,
	 *     regex: bool,
	 *     group: string,
	 *     result: 'created'|'updated'|'ignored',
	 *     redirect_id?: int
	 *   }>
	 * }
	 */
	public function preview_plugin_results( $group_id, array $options = [] ) {
		global $wpdb;

		$redirects = $wpdb->get_results(
			"SELECT {$wpdb->prefix}postmeta.* FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta.post_id " .
			"WHERE {$wpdb->prefix}postmeta.meta_key = '_wp_old_slug' AND {$wpdb->prefix}postmeta.meta_value != '' AND {$wpdb->prefix}posts.post_status='publish' AND {$wpdb->prefix}posts.post_type IN ('page', 'post')"
		);
		$items = array();

		foreach ( $redirects as $redirect ) {
			$items[] = $this->get_item_for_redirect( $redirect );
		}

		return $this->preview_redirect_items( $group_id, $options, $items );
	}

	/**
	 * Import redirects for WordPress old slugs.
	 *
	 * @param int $group_id Target group ID.
	 * @param array<string, bool|string> $options Import options.
	 * @phpstan-return array{
	 *   created: int,
	 *   updated: int,
	 *   ignored: int,
	 *   groups_created: int,
	 *   preview: array<int, array{
	 *     source: string,
	 *     target: string,
	 *     code: int,
	 *     regex: bool,
	 *     group: string,
	 *     result: 'created'|'updated'|'ignored',
	 *     redirect_id?: int
	 *   }>
	 * }
	 * @return array{
	 *   created: int,
	 *   updated: int,
	 *   ignored: int,
	 *   groups_created: int,
	 *   preview: array<int, array{
	 *     source: string,
	 *     target: string,
	 *     code: int,
	 *     regex: bool,
	 *     group: string,
	 *     result: 'created'|'updated'|'ignored',
	 *     redirect_id?: int
	 *   }>
	 * }
	 */
	public function import_plugin( $group_id, array $options = [] ) {
		global $wpdb;

		$redirects = $wpdb->get_results(
			"SELECT {$wpdb->prefix}postmeta.* FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta.post_id " .
			"WHERE {$wpdb->prefix}postmeta.meta_key = '_wp_old_slug' AND {$wpdb->prefix}postmeta.meta_value != '' AND {$wpdb->prefix}posts.post_status='publish' AND {$wpdb->prefix}posts.post_type IN ('page', 'post')"
		);
		$group = new \Redirection\ImportExport\ImportGroup( $group_id, $options );
		$options['dry_run'] = false;
		$import = new \Redirection\ImportExport\ImportRedirect( $options );

		foreach ( $redirects as $redirect ) {
			$item = $this->get_item_for_redirect( $redirect );
			if ( $item === false ) {
				continue;
			}

			$saved = $import->save( $item, $group );
			if ( $saved && ! empty( $options['delete_source'] ) ) {
				delete_metadata_by_mid( 'post', intval( $redirect->meta_id, 10 ) );
			}
		}

		return [
			'created' => $import->get_created(),
			'updated' => $import->get_updated(),
			'ignored' => $import->get_ignored(),
			'groups_created' => $group->get_groups_created(),
			'preview' => $import->get_preview_items(),
		];
	}

	/**
	 * Build redirect data for a WordPress old slug row.
	 *
	 * @param stdClass $redirect Row from postmeta/posts join.
	 * @return array<string, mixed>|false
	 */
	private function get_item_for_redirect( $redirect ) {
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

		return array(
			'url'         => $old,
			'action_data' => array( 'url' => $new ),
			'regex'       => false,
			'match_type'  => 'url',
			'action_type' => 'url',
			'action_code' => 301,
		);
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
				'name' => __( 'WordPress permalink redirect', 'redirection' ),
				'description' => __( 'Redirects created by WordPress.', 'redirection' ),
				'source' => __( 'WordPress post meta', 'redirection' ),
				'total' => intval( $total, 10 ),
			);
		}

		return false;
	}
}
