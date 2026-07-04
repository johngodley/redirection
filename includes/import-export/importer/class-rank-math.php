<?php

namespace Redirection\ImportExport\Importer;

class RankMath extends Plugin {
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

		$redirects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}rank_math_redirections" );
		$items = array();

		foreach ( $redirects as $redirect ) {
			foreach ( $this->get_items_for_redirect( $redirect ) as $item ) {
				$items[] = $item;
			}
		}

		return $items;
	}

	/**
	 * Build one or more redirect data rows for a RankMath entry.
	 *
	 * @param \stdClass $redirect Row from rank_math_redirections.
	 * @return array<int, array<string, mixed>>
	 */
	private function get_items_for_redirect( $redirect ) {
		if ( ! is_string( $redirect->sources ) || preg_match( '/^a:\d+:\{.*\}$/s', $redirect->sources ) !== 1 ) {
			return [];
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize -- Importing existing Rank Math data.
		$sources = unserialize( $redirect->sources, [ 'allowed_classes' => false ] );
		$items = [];

		if ( ! is_array( $sources ) ) {
			return $items;
		}

		foreach ( $sources as $source ) {
			if ( ! is_array( $source ) || ! isset( $source['pattern'], $source['comparison'] ) ) {
				continue;
			}

			$url = $source['pattern'];
			if ( substr( $url, 0, 1 ) !== '/' ) {
				$url = '/' . $url;
			}

			$data = array(
				'url'         => $url,
				'action_data' => array( 'url' => str_replace( '\\\\', '\\', $redirect->url_to ) ),
				'regex'       => $source['comparison'] === 'regex' ? true : false,
				'match_type'  => 'url',
				'action_type' => 'url',
				'action_code' => $redirect->header_code,
			);

			$items[] = $data;
		}

		return $items;
	}

	/**
	 * Get importer summary for RankMath.
	 *
	 * @return array{id: string, name: string, description: string, source: string, total: int}|false
	 */
	public function get_data() {
		global $wpdb;

		if ( defined( 'REDIRECTION_TESTS' ) && REDIRECTION_TESTS ) {
			return false;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$total = 0;
		if ( is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
			$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}rank_math_redirections" );
		}

		if ( $total ) {
			return array(
				'id' => 'rank-math',
				'name' => 'RankMath',
				'description' => __( 'Redirects created by Rank Math.', 'redirection' ),
				'source' => __( 'Database tables', 'redirection' ),
				'total' => intval( $total, 10 ),
			);
		}

		return false;
	}
}
