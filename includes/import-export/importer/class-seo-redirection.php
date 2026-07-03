<?php

namespace Redirection\ImportExport\Importer;

/**
 * @phpstan-import-type ImporterInfo from Plugin
 */
class SeoRedirection extends Plugin {
	/**
	 * @return bool
	 */
	public function supports_preview() {
		return true;
	}

	/**
	 * @return array<int, array<string, mixed>|false>
	 */
	protected function get_redirect_items() {
		global $wpdb;

		if ( defined( 'REDIRECTION_TESTS' ) && REDIRECTION_TESTS ) {
			return [];
		}

		$redirects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}WP_SEO_Redirection" );
		$items = array();

		foreach ( $redirects as $redirect ) {
			$items[] = $this->get_item_for_seo( $redirect );
		}

		return $items;
	}

	/**
	 * Create a Redirection item for an SEO Redirection row.
	 *
	 * @param \stdClass $seo Row from WP_SEO_Redirection.
	 * @return array<string, mixed>|false
	 */
	private function get_item_for_seo( $seo ) {
		if ( intval( $seo->enabled, 10 ) === 0 ) {
			return false;
		}

		return array(
			'url' => $seo->regex ? $seo->regex : $seo->redirect_from,
			'action_data' => array( 'url' => $seo->redirect_to ),
			'regex' => $seo->regex ? true : false,
			'match_type' => 'url',
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
