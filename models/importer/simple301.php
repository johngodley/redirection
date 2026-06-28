<?php

/**
 * @phpstan-import-type ImporterInfo from Red_Plugin_Importer
 */
class Red_Simple301_Importer extends Red_Plugin_Importer {
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
		$redirects = get_option( '301_redirects' );
		$items = array();

		foreach ( $redirects as $source => $target ) {
			$items[] = $this->get_item_for_redirect( $source, $target );
		}

		return $this->preview_redirect_items( $group_id, $options, $items );
	}

	/**
	 * Import redirects from Simple 301 Redirects.
	 *
	 * @param int $group_id Target group ID.
	 * @param array<string, bool|string> $options Import options.
	 */
	public function import_plugin( $group_id, array $options = [] ) {
		$redirects = get_option( '301_redirects' );
		$items = array();

		foreach ( $redirects as $source => $target ) {
			$items[] = $this->get_item_for_redirect( $source, $target );
		}

		return $this->import_redirect_items( $group_id, $options, $items );
	}

	/**
	 * Build redirect data for a given source/target pair.
	 *
	 * @param string $source   Source pattern from Simple 301.
	 * @param string $target   Target URL from Simple 301.
	 * @return array<string, mixed>
	 */
	private function get_item_for_redirect( $source, $target ) {
		return array(
			'url'         => str_replace( '*', '(.*?)', $source ),
			'action_data' => array( 'url' => str_replace( '*', '$1', trim( $target ) ) ),
			'regex'       => strpos( $source, '*' ) === false ? false : true,
			'match_type'  => 'url',
			'action_type' => 'url',
			'action_code' => 301,
		);
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
				'description' => __( 'Redirects created by Simple 301 Redirects.', 'redirection' ),
				'source' => __( 'Plugin settings', 'redirection' ),
				'total' => count( $data ),
			);
		}

		return false;
	}
}
