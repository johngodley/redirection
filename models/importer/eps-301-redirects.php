<?php

/**
 * @phpstan-import-type ImporterInfo from Red_Plugin_Importer
 */
class Red_EPS301Redirects_Importer extends Red_Plugin_Importer {
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

		if ( ! $this->table_exists() ) {
			return [];
		}

		$table = $wpdb->prefix . 'redirects';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( "SELECT * FROM {$table} WHERE `status` != 'inactive'" );
	}

	/**
	 * @return bool
	 */
	private function table_exists() {
		global $wpdb;

		$table = $wpdb->prefix . 'redirects';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = %s', $table ) );

		return intval( $result, 10 ) === 1;
	}

	/**
	 * @param object $redirect Redirect row.
	 * @return array<string, mixed>|false
	 */
	private function get_item_for_redirect( $redirect ) {
		$target = $redirect->type === 'post' ? get_permalink( intval( $redirect->url_to, 10 ) ) : $redirect->url_to;
		$code = intval( $redirect->status, 10 );

		if ( $target === false || empty( $redirect->url_from ) || $code === 0 ) {
			return false;
		}

		return array(
			'url' => '/' . ltrim( $redirect->url_from, '/' ),
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

		if ( ! $this->table_exists() ) {
			return false;
		}

		$table = $wpdb->prefix . 'redirects';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE `status` != 'inactive'" );

		if ( $total !== null && intval( $total, 10 ) > 0 ) {
			return array(
				'id' => 'eps-301-redirects',
				'name' => '301 Redirects',
				'description' => __( 'Redirects stored by 301 Redirects.', 'redirection' ),
				'source' => __( 'Database tables', 'redirection' ),
				'total' => intval( $total, 10 ),
			);
		}

		return false;
	}
}
