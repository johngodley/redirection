<?php

/**
 * @phpstan-import-type ImporterInfo from Red_Plugin_Importer
 */
class Red_SlimSeo_Importer extends Red_Plugin_Importer {
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
		$redirects = get_option( 'ss_redirects' );
		$items = array();

		if ( is_array( $redirects ) ) {
			foreach ( $redirects as $redirect ) {
				$items[] = $this->get_item_for_redirect( $redirect );
			}
		}

		return $this->preview_redirect_items( $group_id, $options, $items );
	}

	/**
	 * @param int $group_id Target group ID.
	 * @param array<string, bool|string> $options Import options.
	 */
	public function import_plugin( $group_id, array $options = [] ) {
		$redirects = get_option( 'ss_redirects' );
		$items = array();

		if ( ! is_array( $redirects ) ) {
			return $this->get_empty_results();
		}

		foreach ( $redirects as $redirect ) {
			$items[] = $this->get_item_for_redirect( $redirect );
		}

		return $this->import_redirect_items( $group_id, $options, $items );
	}

	/**
	 * @param array<string, mixed> $redirect Redirect data.
	 * @return array<string, mixed>|false
	 */
	private function get_item_for_redirect( array $redirect ) {
		if ( empty( $redirect['from'] ) || empty( $redirect['to'] ) || empty( $redirect['enable'] ) ) {
			return false;
		}

		$condition = isset( $redirect['condition'] ) ? $redirect['condition'] : 'exact-match';
		$source = $this->normalize_source( (string) $redirect['from'] );
		$regex = false;

		if ( $condition === 'regex' ) {
			$regex = true;
		} elseif ( $condition === 'start-with' ) {
			$regex = true;
			$source = '^' . preg_quote( $source, '#' );
		} elseif ( $condition === 'end-with' ) {
			$regex = true;
			$source = preg_quote( $source, '#' ) . '/?$';
		} elseif ( $condition === 'contain' ) {
			$regex = true;
			$source = '.*' . preg_quote( ltrim( $source, '/' ), '#' ) . '.*';
		}

		return array(
			'url' => $source,
			'action_data' => array( 'url' => $this->normalize_target( (string) $redirect['to'] ) ),
			'regex' => $regex,
			'match_type' => 'url',
			'action_type' => 'url',
			'action_code' => isset( $redirect['type'] ) ? intval( $redirect['type'], 10 ) : 301,
		);
	}

	/**
	 * @param string $source Source URL.
	 * @return string
	 */
	private function normalize_source( $source ) {
		if ( substr( $source, 0, 1 ) !== '/' && substr( $source, 0, 1 ) !== '^' ) {
			return '/' . $source;
		}

		return $source;
	}

	/**
	 * @param string $target Target URL.
	 * @return string
	 */
	private function normalize_target( $target ) {
		if ( preg_match( '@^https?://@i', $target ) === 1 || substr( $target, 0, 1 ) === '/' ) {
			return $target;
		}

		return '/' . ltrim( $target, '/' );
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
