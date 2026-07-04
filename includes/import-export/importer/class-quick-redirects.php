<?php

namespace Redirection\ImportExport\Importer;

/**
 * @phpstan-import-type ImporterInfo from Plugin
 */
class QuickRedirects extends Plugin {
	/**
	 * @var RedirectItemMapper
	 */
	private $mapper;

	public function __construct( ?RedirectItemMapper $mapper = null ) {
		$this->mapper = $mapper ? $mapper : new RedirectItemMapper();
	}

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
		$redirects = get_option( 'quickppr_redirects' );
		$items = array();

		if ( ! is_array( $redirects ) ) {
			return $items;
		}

		foreach ( $redirects as $source => $target ) {
			$items[] = $this->mapper->quick_redirects( $source, $target );
		}

		return $items;
	}

	/**
	 * Get importer summary for Quick Page/Post Redirects.
	 *
	 * @return ImporterInfo|false
	 */
	public function get_data() {
		$data = get_option( 'quickppr_redirects' );

		if ( is_array( $data ) ) {
			return array(
				'id' => 'quick-redirects',
				'name' => 'Quick Page/Post Redirects',
				'description' => __( 'Redirects created by Quick Page/Post Redirects.', 'redirection' ),
				'source' => __( 'Plugin settings', 'redirection' ),
				'total' => count( $data ),
			);
		}

		return false;
	}
}
