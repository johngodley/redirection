<?php

/**
 * @phpstan-import-type ImporterInfo from Red_Plugin_Importer
 */
class Red_SafeRedirectManager_Importer extends Red_Plugin_Importer {
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
		$posts = $this->get_redirect_posts();
		$items = array();

		foreach ( $posts as $post ) {
			$items[] = $this->get_item_for_post( $post );
		}

		return $this->preview_redirect_items( $group_id, $options, $items );
	}

	/**
	 * Import redirects from Safe Redirect Manager.
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
		$group = new \Redirection\ImportExport\ImportGroup( $group_id, $options );
		$options['dry_run'] = false;
		$import = new \Redirection\ImportExport\ImportRedirect( $options );

		foreach ( $this->get_redirect_posts() as $post ) {
			$item = $this->get_item_for_post( $post );

			if ( $item === false ) {
				continue;
			}

			$saved = $import->save( $item, $group );
			if ( $saved && ! empty( $options['delete_source'] ) && isset( $post['post_id'] ) ) {
				$this->delete_redirect_meta( intval( $post['post_id'], 10 ) );
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
	 * @return array<int, array<string, string|int>>
	 */
	private function get_redirect_posts() {
		global $wpdb;

		$redirects = $wpdb->get_results(
			"SELECT {$wpdb->prefix}postmeta.* FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta.post_id WHERE {$wpdb->prefix}postmeta.meta_key LIKE '_redirect_rule_%' AND {$wpdb->prefix}posts.post_status='publish'"
		);

		$by_post = array();
		foreach ( $redirects as $redirect ) {
			if ( ! isset( $by_post[ $redirect->post_id ] ) ) {
				$by_post[ $redirect->post_id ] = array(
					'post_id' => intval( $redirect->post_id, 10 ),
				);
			}

			$by_post[ $redirect->post_id ][ str_replace( '_redirect_rule_', '', $redirect->meta_key ) ] = $redirect->meta_value;
		}

		return array_values( $by_post );
	}

	/**
	 * Create a Redirection item from a collected SRM post meta map.
	 *
	 * @param int              $group_id Target group ID.
	 * @param array<string,string> $post    Map of SRM fields for a single link.
	 * @return Red_Item|WP_Error Created redirect or error.
	 */
	private function get_item_for_post( $post ) {
		$regex = false;
		$source = $post['from'];

		if ( strpos( $post['from'], '*' ) !== false ) {
			$regex = true;
			$source = str_replace( '*', '.*', $source );
		} elseif ( isset( $post['from_regex'] ) && $post['from_regex'] === '1' ) {
			$regex = true;
		}

		return array(
			'url'         => $source,
			'action_data' => array( 'url' => $post['to'] ),
			'regex'       => $regex,
			'match_type'  => 'url',
			'action_type' => 'url',
			'action_code' => intval( $post['status_code'], 10 ),
		);
	}

	/**
	 * @param int $post_id Post ID.
	 * @return void
	 */
	private function delete_redirect_meta( $post_id ) {
		delete_post_meta( $post_id, '_redirect_rule_from' );
		delete_post_meta( $post_id, '_redirect_rule_to' );
		delete_post_meta( $post_id, '_redirect_rule_status_code' );
		delete_post_meta( $post_id, '_redirect_rule_from_regex' );
	}

	/**
	 * Get importer summary for Safe Redirect Manager.
	 *
	 * @return ImporterInfo|false
	 */
	public function get_data() {
		global $wpdb;

		$total = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta.post_id WHERE {$wpdb->prefix}postmeta.meta_key = '_redirect_rule_from' AND {$wpdb->prefix}posts.post_status='publish'"
		);

		if ( $total !== null ) {
			return array(
				'id' => 'safe-redirect-manager',
				'name' => 'Safe Redirect Manager',
				'description' => __( 'Redirects created by Safe Redirect Manager.', 'redirection' ),
				'source' => __( 'WordPress posts and post meta', 'redirection' ),
				'total' => intval( $total, 10 ),
			);
		}

		return false;
	}
}
