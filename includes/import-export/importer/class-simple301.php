<?php

namespace Redirection\ImportExport\Importer;

/**
 * @phpstan-import-type ImporterInfo from Plugin
 */
class Simple301 extends Plugin {
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
		$redirects = get_option( '301_redirects' );
		$items = array();

		if ( ! is_array( $redirects ) ) {
			return $items;
		}

		foreach ( $redirects as $source => $target ) {
			$items[] = $this->mapper->simple301( $source, $target );
		}

		return $items;
	}

	/**
	 * Get importer summary for Simple 301 Redirects.
	 *
	 * @return ImporterInfo|false
	 */
	public function get_data() {
		$data = get_option( '301_redirects' );

		if ( is_array( $data ) ) {
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
