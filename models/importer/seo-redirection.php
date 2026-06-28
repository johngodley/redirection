<?php

/**
 * @phpstan-import-type ImporterInfo from Red_Plugin_Importer
 */
class Red_SeoRedirection_Importer extends Red_Plugin_Importer {
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

		if ( defined( 'REDIRECTION_TESTS' ) && REDIRECTION_TESTS ) {
			return $this->get_empty_results();
		}

		$redirects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}WP_SEO_Redirection" );
		$items = array();

		foreach ( $redirects as $redirect ) {
			$items[] = $this->get_item_for_seo( $redirect );
		}

		return $this->preview_redirect_items( $group_id, $options, $items );
	}

	/**
	 * Import redirects from SEO Redirection.
	 *
	 * @param int $group_id Target group ID.
	 * @return int Number of imported redirects.
	 */
	public function import_plugin( $group_id, array $options = [] ) {
		global $wpdb;

		if ( defined( 'REDIRECTION_TESTS' ) && REDIRECTION_TESTS ) {
			return $this->get_empty_results();
		}

		$redirects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}WP_SEO_Redirection" );
		$items = array();

		foreach ( $redirects as $redirect ) {
			$items[] = $this->get_item_for_seo( $redirect );
		}

		return $this->import_redirect_items( $group_id, $options, $items );
	}

	/**
	 * Create a Redirection item for an SEO Redirection row.
	 *
	 * @param int      $group_id Target group ID.
	 * @param stdClass $seo      Row from WP_SEO_Redirection.
	 * @return Red_Item|WP_Error|false Created redirect, error, or false if disabled.
	 */
	private function get_item_for_seo( $seo ) {
		if ( intval( $seo->enabled, 10 ) === 0 ) {
			return false;
		}

		return array(
			'url'         => $seo->regex ? $seo->regex : $seo->redirect_from,
			'action_data' => array( 'url' => $seo->redirect_to ),
			'regex'       => $seo->regex ? true : false,
			'match_type'  => 'url',
			'action_type' => 'url',
			'action_code' => intval( $seo->redirect_type, 10 ),
		);
	}

	/**
	 * Get importer summary for SEO Redirection.
	 *
	 * @return ImporterInfo|false
	 */
	public function get_data() {
		global $wpdb;

		$plugins = get_option( 'active_plugins', array() );
		$found = false;

		foreach ( $plugins as $plugin ) {
			if ( strpos( $plugin, 'seo-redirection.php' ) !== false ) {
				$found = true;
				break;
			}
		}

		if ( $found ) {
			$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}WP_SEO_Redirection" );

			return array(
				'id' => 'seo-redirection',
				'name' => 'SEO Redirection',
				'description' => __( 'Redirects created by SEO Redirection.', 'redirection' ),
				'source' => __( 'Database tables', 'redirection' ),
				'total' => $total,
			);
		}

		return false;
	}
}
