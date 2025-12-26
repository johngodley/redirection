<?php

/**
 * @phpstan-import-type ImporterInfo from Red_Plugin_Importer
 */
class Red_PrettyLinks_Importer extends Red_Plugin_Importer {
	/**
	 * Import redirects from Pretty Links.
	 *
	 * @param int $group_id Target group ID.
	 * @return int Number of imported redirects.
	 */
	public function import_plugin( $group_id ) {
		global $wpdb;

		$count = 0;
		$redirects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}prli_links" );

		foreach ( $redirects as $redirect ) {
			$created = $this->create_for_item( $group_id, $redirect );

			if ( $created instanceof Red_Item ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Create a Redirection item for a Pretty Links row.
	 *
	 * @param int      $group_id Target group ID.
	 * @param stdClass $link     Row from prli_links.
	 * @return Red_Item|WP_Error Created redirect or error.
	 */
	private function create_for_item( $group_id, $link ) {
		$item = array(
			'url'         => '/' . $link->slug,
			'action_data' => array( 'url' => $link->url ),
			'regex'       => false,
			'group_id'    => $group_id,
			'match_type'  => 'url',
			'action_type' => 'url',
			'title'       => $link->name,
			'action_code' => $link->redirect_type,
		);

		return Red_Item::create( $item );
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
				'total' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}prli_links" ),
			];
		}

		return false;
	}
}
