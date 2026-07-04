<?php

namespace Redirection\ImportExport\Importer;

/**
 * @phpstan-import-type ImporterInfo from Plugin
 */
class PrettyLinks extends Plugin {
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

		$redirects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}prli_links" );
		$items = array();

		foreach ( $redirects as $redirect ) {
			$items[] = $this->get_item_for_link( $redirect );
		}

		return $items;
	}

	/**
	 * Create a Redirection item for a Pretty Links row.
	 *
	 * @param \stdClass $link Row from prli_links.
	 * @return array<string, mixed>
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
				'description' => __( 'Redirects created by Pretty Links.', 'redirection' ),
				'source' => __( 'Database tables', 'redirection' ),
				'total' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}prli_links" ),
			];
		}

		return false;
	}
}
