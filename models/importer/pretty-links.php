<?php

/**
 * @phpstan-import-type ImporterInfo from Red_Plugin_Importer
 */
class Red_PrettyLinks_Importer extends Red_Plugin_Importer {
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

		$redirects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}prli_links" );
		$items = array();

		foreach ( $redirects as $redirect ) {
			$items[] = $this->get_item_for_link( $redirect );
		}

		return $this->preview_redirect_items( $group_id, $options, $items );
	}

	/**
	 * Import redirects from Pretty Links.
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

		$redirects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}prli_links" );
		$items = array();

		foreach ( $redirects as $redirect ) {
			$items[] = $this->get_item_for_link( $redirect );
		}

		return $this->import_redirect_items( $group_id, $options, $items );
	}

	/**
	 * Create a Redirection item for a Pretty Links row.
	 *
	 * @param int      $group_id Target group ID.
	 * @param stdClass $link     Row from prli_links.
	 * @return Red_Item|WP_Error Created redirect or error.
	 */
	private function get_item_for_link( $link ) {
		return array(
			'url'         => '/' . $link->slug,
			'action_data' => array( 'url' => $link->url ),
			'regex'       => false,
			'match_type'  => 'url',
			'action_type' => 'url',
			'title'       => $link->name,
			'action_code' => $link->redirect_type,
		);
	}

	/**
	 * Get importer summary for Pretty Links.
	 *
	 * @return ImporterInfo|false
	 */
	public function get_data() {
		$data = get_option( 'prli_db_version' );

		if ( $data !== false ) {
			global $wpdb;

			return [
				'id' => 'pretty-links',
				'name' => 'PrettyLinks',
				'description' => __( 'Rules created by Pretty Links.', 'redirection' ),
				'source' => __( 'Database tables', 'redirection' ),
				'total' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}prli_links" ),
			];
		}

		return false;
	}
}
