<?php

/**
 * @phpstan-import-type ImporterInfo from Red_Plugin_Importer
 */
class Red_Simple301_Importer extends Red_Plugin_Importer {
	/**
	 * Import redirects from Simple 301 Redirects.
	 *
	 * @param int $group_id Target group ID.
	 * @return int Number of imported redirects.
	 */
	public function import_plugin( $group_id ) {
		$redirects = get_option( '301_redirects' );
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
	 * @param string $source   Source pattern from Simple 301.
	 * @param string $target   Target URL from Simple 301.
	 * @return Red_Item|WP_Error Created redirect or error.
	 */
	private function create_for_item( $group_id, $source, $target ) {
		$item = array(
			'url'         => str_replace( '*', '(.*?)', $source ),
			'action_data' => array( 'url' => str_replace( '*', '$1', trim( $target ) ) ),
			'regex'       => strpos( $source, '*' ) === false ? false : true,
			'group_id'    => $group_id,
			'match_type'  => 'url',
			'action_type' => 'url',
			'action_code' => 301,
		);

		return Red_Item::create( $item );
	}

	/**
	 * Get importer summary for Simple 301 Redirects.
	 *
	 * @return ImporterInfo|false
	 */
	public function get_data() {
		$data = get_option( '301_redirects' );

		if ( $data !== false ) {
			return array(
				'id' => 'wp-simple-redirect',
				'name' => 'Simple 301 Redirects',
				'total' => count( $data ),
			);
		}

		return false;
	}
}
