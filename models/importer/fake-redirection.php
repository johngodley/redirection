<?php

/**
 * @phpstan-import-type ImporterInfo from Red_Plugin_Importer
 */
class Red_FakeRedirection_Importer extends Red_Plugin_Importer {
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
		$items = array();

		foreach ( $this->get_redirects() as $redirect ) {
			$items[] = $this->get_item_for_redirect( $redirect );
		}

		return $this->preview_redirect_items( $group_id, $options, $items );
	}

	/**
	 * @param int $group_id Target group ID.
	 * @return int Number of imported redirects.
	 */
	public function import_plugin( $group_id, array $options = [] ) {
		$items = array();

		foreach ( $this->get_redirects() as $redirect ) {
			$items[] = $this->get_item_for_redirect( $redirect );
		}

		return $this->import_redirect_items( $group_id, $options, $items );
	}

	/**
	 * @return list<object>
	 */
	private function get_redirects() {
		global $wpdb;

		if ( ! $this->table_exists( 'irrp_redirections' ) ) {
			return [];
		}

		$table = $wpdb->prefix . 'irrp_redirections';
		$meta_table = $wpdb->prefix . 'irrp_redirectionmeta';
		$join = '';

		if ( $this->table_exists( 'irrp_redirectionmeta' ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$join = $wpdb->prepare( " LEFT JOIN {$meta_table} AS meta ON meta.redirect_id = redirects.id AND meta.meta_key = %s", 'redirect_code' );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( "SELECT redirects.*, COALESCE( meta.meta_value, '301' ) AS redirect_code FROM {$table} AS redirects{$join} WHERE redirects.`status` = 1 AND redirects.`type` = 'redirection'" );
	}

	/**
	 * @param string $table_name Table name without prefix.
	 * @return bool
	 */
	private function table_exists( $table_name ) {
		global $wpdb;

		$table = $wpdb->prefix . $table_name;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = %s', $table ) );

		return intval( $result, 10 ) === 1;
	}

	/**
	 * @param object $redirect Redirect row.
	 * @return array<string, mixed>|false
	 */
	private function get_item_for_redirect( $redirect ) {
		$source = (string) $redirect->match;
		$target = (string) $redirect->to;
		$code = isset( $redirect->redirect_code ) ? intval( $redirect->redirect_code, 10 ) : 301;

		if ( $source === '' || $target === '' || $code === 0 ) {
			return false;
		}

		return array(
			'url' => $source,
			'action_data' => array( 'url' => $target ),
			'regex' => false,
			'match_type' => 'url',
			'action_type' => 'url',
			'action_code' => $code,
		);
	}

	/**
	 * @return ImporterInfo|false
	 */
	public function get_data() {
		global $wpdb;

		if ( ! $this->table_exists( 'irrp_redirections' ) ) {
			return false;
		}

		$table = $wpdb->prefix . 'irrp_redirections';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE `status` = 1 AND `type` = 'redirection'" );

		if ( $total !== null && intval( $total, 10 ) > 0 ) {
			return array(
				'id' => 'fake-redirection',
				'name' => 'Fake Redirection',
				'description' => __( 'Redirects stored by Redirect Redirection.', 'redirection' ),
				'source' => __( 'Database tables', 'redirection' ),
				'total' => intval( $total, 10 ),
			);
		}

		return false;
	}
}
