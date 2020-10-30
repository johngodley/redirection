<?php

/**
 * Provides permalink migration facilities
 */
class Red_Permalinks {
	/**
	 * List of migrated permalink structures
	 *
	 * @var string[]
	 */
	private $permalinks = [];

	/**
	 * Current permalink structure
	 *
	 * @var string|null
	 */
	private $current_permalink = null;

	/**
	 * Constructor
	 *
	 * @param string[] $permalinks List of migrated permalinks.
	 */
	public function __construct( $permalinks ) {
		$this->permalinks = $permalinks;
	}

	/**
	 * Match and migrate any permalinks
	 *
	 * @param WP_Query $query Query.
	 * @return void
	 */
	public function migrate( WP_Query $query ) {
		global $wp;

		if ( count( $this->permalinks ) === 0 ) {
			return;
		}

		$this->intercept_permalinks();

		foreach ( $this->permalinks as $old ) {
			// Set the current permalink
			$this->current_permalink = $old;

			// Run the WP query again
			$wp->init();
			$wp->parse_request();

			// Anything matched?
			if ( $wp->matched_rule ) {
				// Perform the post query
				$wp->query_posts();

				// A single post?
				if ( is_single() && count( $query->posts ) > 0 ) {
					// Restore permalinks
					$this->release_permalinks();

					// Get real URL from the post ID
					$url = get_permalink( $query->posts[0]->ID );
					if ( $url ) {
						wp_safe_redirect( $url, 301, 'redirection' );
						die();
					}
				}

				break;
			}
		}

		$this->release_permalinks();
	}

	/**
	 * Hook the permalink options and return the migrated one
	 *
	 * @return void
	 */
	private function intercept_permalinks() {
		add_filter( 'pre_option_rewrite_rules', [ $this, 'get_old_rewrite_rules' ] );
		add_filter( 'pre_option_permalink_structure', [ $this, 'get_old_permalink' ] );
	}

	/**
	 * Restore the hooked option
	 *
	 * @return void
	 */
	private function release_permalinks() {
		remove_filter( 'pre_option_rewrite_rules', [ $this, 'get_old_rewrite_rules' ] );
		remove_filter( 'pre_option_permalink_structure', [ $this, 'get_old_permalink' ] );
	}

	/**
	 * Returns rewrite rules for the current migrated permalink
	 *
	 * @param array $rules Current rules.
	 * @return array
	 */
	public function get_old_rewrite_rules( $rules ) {
		global $wp_rewrite;

		if ( $this->current_permalink ) {
			$wp_rewrite->init();
			$wp_rewrite->matches = 'matches';
			return $wp_rewrite->rewrite_rules();
		}

		return $rules;
	}

	/**
	 * Get the current migrated permalink structure
	 *
	 * @param string $result Current value.
	 * @return string
	 */
	public function get_old_permalink( $result ) {
		if ( $this->current_permalink ) {
			return $this->current_permalink;
		}

		return $result;
	}
}
