<?php

/**
 * @phpstan-import-type ImporterInfo from Red_Plugin_Importer
 */
class Red_SafeRedirectManager_Importer extends Red_Plugin_Importer {
	/**
	 * Import redirects from Safe Redirect Manager.
	 *
	 * @param int $group_id Target group ID.
	 * @return int Number of imported redirects.
	 */
	public function import_plugin( $group_id ) {
		global $wpdb;

		$count = 0;
		$redirects = $wpdb->get_results(
			"SELECT {$wpdb->prefix}postmeta.* FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta.post_id WHERE {$wpdb->prefix}postmeta.meta_key LIKE '_redirect_rule_%' AND {$wpdb->prefix}posts.post_status='publish'"
		);

		// Group them by post ID
		$by_post = array();
		foreach ( $redirects as $redirect ) {
			if ( ! isset( $by_post[ $redirect->post_id ] ) ) {
				$by_post[ $redirect->post_id ] = array();
			}

			$by_post[ $redirect->post_id ][ str_replace( '_redirect_rule_', '', $redirect->meta_key ) ] = $redirect->meta_value;
		}

		// Now go through the redirects
		foreach ( $by_post as $post ) {
			$item = $this->create_for_item( $group_id, $post );

			if ( $item instanceof Red_Item ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Create a Redirection item from a collected SRM post meta map.
	 *
	 * @param int              $group_id Target group ID.
	 * @param array<string,string> $post    Map of SRM fields for a single link.
	 * @return Red_Item|WP_Error Created redirect or error.
	 */
	private function create_for_item( $group_id, $post ) {
		$regex = false;
		$source = $post['from'];

		if ( strpos( $post['from'], '*' ) !== false ) {
			$regex = true;
			$source = str_replace( '*', '.*', $source );
		} elseif ( isset( $post['from_regex'] ) && $post['from_regex'] === '1' ) {
			$regex = true;
		}

		$data = array(
			'url'         => $source,
			'action_data' => array( 'url' => $post['to'] ),
			'regex'       => $regex,
			'group_id'    => $group_id,
			'match_type'  => 'url',
			'action_type' => 'url',
			'action_code' => intval( $post['status_code'], 10 ),
		);

		return Red_Item::create( $data );
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
				'total' => intval( $total, 10 ),
			);
		}

		return false;
	}
}
