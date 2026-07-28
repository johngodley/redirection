<?php

namespace Redirection\ImportExport\Importer;

/**
 * @phpstan-import-type ImporterInfo from Plugin
 */
class YoastSeo extends Plugin {
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
		$redirects = get_option( 'wpseo-premium-redirects-base' );
		$items = array();

		if ( is_array( $redirects ) ) {
			foreach ( $redirects as $redirect ) {
				if ( is_array( $redirect ) ) {
					$items[] = $this->mapper->yoast_seo( $redirect );
				}
			}
		}

		return $items;
	}

	/**
	 * @return ImporterInfo|false
	 */
	public function get_data() {
		$data = get_option( 'wpseo-premium-redirects-base' );

		if ( is_array( $data ) && count( $data ) > 0 ) {
			return array(
				'id' => 'yoast-seo',
				'name' => 'Yoast SEO Premium',
				'description' => __( 'Redirects created by Yoast SEO Premium.', 'redirection' ),
				'source' => __( 'Plugin settings', 'redirection' ),
				'total' => count( $data ),
			);
		}

		return false;
	}
}
