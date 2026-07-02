<?php

namespace Redirection\ImportExport\Importer;

/**
 * @phpstan-import-type ImporterInfo from Plugin
 */
class SlimSeo extends Plugin {
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
		$redirects = get_option( 'ss_redirects' );
		$items = array();

		if ( is_array( $redirects ) ) {
			foreach ( $redirects as $redirect ) {
				$items[] = $this->mapper->slim_seo( $redirect );
			}
		}

		return $items;
	}

	/**
	 * @return ImporterInfo|false
	 */
	public function get_data() {
		$data = get_option( 'ss_redirects' );

		if ( is_array( $data ) && count( $data ) > 0 ) {
			return array(
				'id' => 'slim-seo',
				'name' => 'Slim SEO',
				'description' => __( 'Redirects created by Slim SEO.', 'redirection' ),
				'source' => __( 'Plugin settings', 'redirection' ),
				'total' => count( $data ),
			);
		}

		return false;
	}
}
