<?php

/**
 * @phpstan-import-type ImporterInfo from Red_Plugin_Importer
 */
class Red_SeoRedirection_Importer extends Red_Plugin_Importer {
	/**
	 * Import redirects from SEO Redirection.
	 *
	 * @param int $group_id Target group ID.
	 * @return int Number of imported redirects.
	 */
	public function import_plugin( $group_id ) {
		global $wpdb;

		if ( defined( 'REDIRECTION_TESTS' ) && REDIRECTION_TESTS ) {
			return 0;
		}

		$count = 0;
		$redirects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}WP_SEO_Redirection" );

		foreach ( $redirects as $redirect ) {
			$item = $this->create_for_item( $group_id, $redirect );

			if ( $item instanceof Red_Item ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Create a Redirection item for an SEO Redirection row.
	 *
	 * @param int      $group_id Target group ID.
	 * @param stdClass $seo      Row from WP_SEO_Redirection.
	 * @return Red_Item|WP_Error|false Created redirect, error, or false if disabled.
	 */
	private function create_for_item( $group_id, $seo ) {
		if ( intval( $seo->enabled, 10 ) === 0 ) {
			return false;
		}

		$data = array(
			'url'         => $seo->regex ? $seo->regex : $seo->redirect_from,
			'action_data' => array( 'url' => $seo->redirect_to ),
			'regex'       => $seo->regex ? true : false,
			'group_id'    => $group_id,
			'match_type'  => 'url',
			'action_type' => 'url',
			'action_code' => intval( $seo->redirect_type, 10 ),
		);

		return Red_Item::create( $data );
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
				'total' => $total,
			);
		}

		return false;
	}
}
