<?php

namespace Redirection\ImportExport\Importer;

/**
 * @phpstan-import-type ImporterInfo from Plugin
 */
class Seopress extends Plugin {
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
		$items = [];

		foreach ( $this->get_redirect_posts() as $post_id ) {
			$item = $this->get_item_for_post( $post_id );
			if ( $item !== false ) {
				$items[] = $item;
			}
		}

		foreach ( $this->get_redirect_terms() as $term_id ) {
			$item = $this->get_item_for_term( $term_id );
			if ( $item !== false ) {
				$items[] = $item;
			}
		}

		foreach ( $this->get_404_redirect_posts() as $post ) {
			$item = $this->get_item_for_404_post( $post );
			if ( $item !== false ) {
				$items[] = $item;
			}
		}

		return $items;
	}

	/**
	 * @return list<int>
	 */
	private function get_redirect_posts() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return array_values(
			array_map(
				'intval',
				$wpdb->get_col(
					"SELECT DISTINCT posts.ID FROM {$wpdb->posts} AS posts INNER JOIN {$wpdb->postmeta} AS pm ON posts.ID = pm.post_id WHERE pm.meta_key = '_seopress_redirections_enabled' AND pm.meta_value = 'yes' AND posts.post_type != 'seopress_404' AND posts.post_status = 'publish'"
				)
			)
		);
	}

	/**
	 * @return list<int>
	 */
	private function get_redirect_terms() {
		global $wpdb;

		$termmeta = $wpdb->termmeta ?? null;
		if ( ! is_string( $termmeta ) ) {
			return [];
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return array_values(
			array_map(
				'intval',
				$wpdb->get_col(
					"SELECT DISTINCT term_id FROM {$wpdb->termmeta} WHERE meta_key = '_seopress_redirections_enabled' AND meta_value = 'yes'"
				)
			)
		);
	}

	/**
	 * @return list<\WP_Post>
	 */
	private function get_404_redirect_posts() {
		return array_values(
			get_posts(
				[
					'post_type' => 'seopress_404',
					'post_status' => 'publish',
					'posts_per_page' => -1,
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Importer needs to find only enabled SEOPress redirects stored in post meta.
					'meta_query' => [
						[
							'key' => '_seopress_redirections_enabled',
							'value' => 'yes',
						],
					],
				]
			)
		);
	}

	/**
	 * @param int $post_id Post ID.
	 * @return array<string, mixed>|false
	 */
	private function get_item_for_post( $post_id ) {
		$source = get_permalink( $post_id );
		$target = get_post_meta( $post_id, '_seopress_redirections_value', true );
		$code = intval( get_post_meta( $post_id, '_seopress_redirections_type', true ), 10 );

		if ( $source === false ) {
			return false;
		}

		return $this->mapper->seopress_content( $source, (string) $target, $code );
	}

	/**
	 * @param int $term_id Term ID.
	 * @return array<string, mixed>|false
	 */
	private function get_item_for_term( $term_id ) {
		$source = get_term_link( intval( $term_id, 10 ) );
		$target = get_term_meta( $term_id, '_seopress_redirections_value', true );
		$code = intval( get_term_meta( $term_id, '_seopress_redirections_type', true ), 10 );

		if ( is_wp_error( $source ) ) {
			return false;
		}

		return $this->mapper->seopress_content( $source, (string) $target, $code );
	}

	/**
	 * @param \WP_Post $post Post object.
	 * @return array<string, mixed>|false
	 */
	private function get_item_for_404_post( $post ) {
		$target = get_post_meta( $post->ID, '_seopress_redirections_value', true );
		$code = intval( get_post_meta( $post->ID, '_seopress_redirections_type', true ), 10 );
		$regex = get_post_meta( $post->ID, '_seopress_redirections_enabled_regex', true ) === 'yes';
		return $this->mapper->seopress_404( $post->post_title, (string) $target, $code, $regex );
	}

	/**
	 * @return ImporterInfo|false
	 */
	public function get_data() {
		$total = count( $this->get_redirect_posts() ) + count( $this->get_redirect_terms() ) + count( $this->get_404_redirect_posts() );

		if ( $total > 0 ) {
			return array(
				'id' => 'seopress',
				'name' => 'SEOPress',
				'description' => __( 'Redirects created by SEOPress.', 'redirection' ),
				'source' => __( 'WordPress posts, terms, and SEOPress 404 entries', 'redirection' ),
				'total' => $total,
			);
		}

		return false;
	}
}
