<?php

/**
 * @phpstan-import-type ImporterInfo from Red_Plugin_Importer
 */
class Red_QuickRedirect_Importer extends Red_Plugin_Importer {
	/**
	 * Import redirects from Quick Page/Post Redirects.
	 *
	 * @param int $group_id Target group ID.
	 * @return int Number of imported redirects.
	 */
	public function import_plugin( $group_id ) {
		$redirects = get_option( 'quickppr_redirects' );
		$count = 0;

		foreach ( $redirects as $source => $target ) {
			$item = $this->create_for_item( $group_id, $source, $target );

			if ( $item instanceof Red_Item ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Create a Redirection item for a given source/target pair.
	 *
	 * @param int    $group_id Target group ID.
	 * @param string $source   Source URL.
	 * @param string $target   Target URL.
	 * @return Red_Item|WP_Error Created redirect or error.
	 */
	private function create_for_item( $group_id, $source, $target ) {
		$item = array(
			'url'         => $source,
			'action_data' => array( 'url' => $target ),
			'regex'       => false,
			'group_id'    => $group_id,
			'match_type'  => 'url',
			'action_type' => 'url',
			'action_code' => 301,
		);

		return Red_Item::create( $item );
	}

	/**
	 * Get importer summary for Quick Page/Post Redirects.
	 *
	 * @return ImporterInfo|false
	 */
	public function get_data() {
		$data = get_option( 'quickppr_redirects' );

		if ( $data !== false ) {
			return array(
				'id' => 'quick-redirects',
				'name' => 'Quick Page/Post Redirects',
				'total' => count( $data ),
			);
		}

		return false;
	}
}
