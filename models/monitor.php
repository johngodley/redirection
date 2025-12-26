<?php

class Red_Monitor {
	/**
	 * @var int
	 */
	private $monitor_group_id = 0;

	/**
	 * @var array<int, string>
	 */
	private $updated_posts = array();

	/**
	 * @var list<string>
	 */
	private $monitor_types = array();

	/**
	 * @var string
	 */
	private $associated = '';

	/**
	 * @param array<string, mixed> $options
	 */
	public function __construct( array $options ) {
		$this->monitor_types = apply_filters( 'redirection_monitor_types', isset( $options['monitor_types'] ) ? $options['monitor_types'] : array() );

		if ( count( $this->monitor_types ) > 0 && $options['monitor_post'] > 0 ) {
			$this->monitor_group_id = intval( $options['monitor_post'], 10 );
			$this->associated = isset( $options['associated_redirect'] ) ? $options['associated_redirect'] : '';

			// Only monitor if permalinks enabled
			if ( get_option( 'permalink_structure' ) !== false ) {
				add_action( 'pre_post_update', array( $this, 'pre_post_update' ), 10, 2 );
				add_action( 'post_updated', array( $this, 'post_updated' ), 11, 3 );
				add_action( 'redirection_remove_existing', array( $this, 'remove_existing_redirect' ) );
				add_filter( 'redirection_permalink_changed', array( $this, 'has_permalink_changed' ), 10, 3 );

				if ( in_array( 'trash', $this->monitor_types, true ) ) {
					add_action( 'wp_trash_post', array( $this, 'post_trashed' ) );
				}
			}
		}
	}

	/**
	 * @param string $url
	 * @return void
	 */
	public function remove_existing_redirect( string $url ): void {
		Red_Item::disable_where_matches( $url );
	}

	/**
	 * @param WP_Post $post
	 * @param WP_Post $post_before
	 * @return bool
	 */
	public function can_monitor_post( WP_Post $post, WP_Post $post_before ): bool {
		// Check this is for the expected post
		// @phpstan-ignore isset.property
		if ( ! isset( $post->ID ) || ! isset( $this->updated_posts[ $post->ID ] ) ) {
			return false;
		}

		// Don't do anything if we're not published
		if ( $post->post_status !== 'publish' || $post_before->post_status !== 'publish' ) {
			return false;
		}

		$type = get_post_type( $post->ID );
		if ( ! in_array( $type, $this->monitor_types, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Called when a post has been updated - check if the slug has changed
	 *
	 * @param int $post_id
	 * @param WP_Post $post
	 * @param WP_Post $post_before
	 * @return void
	 */
	public function post_updated( int $post_id, WP_Post $post, WP_Post $post_before ): void {
		if ( isset( $this->updated_posts[ $post_id ] ) && $this->can_monitor_post( $post, $post_before ) ) {
			$this->check_for_modified_slug( $post_id, $this->updated_posts[ $post_id ] );
		}
	}

	/**
	 * Remember the previous post permalink
	 *
	 * @param int $post_id
	 * @param array<string, mixed> $data
	 * @return void
	 */
	public function pre_post_update( int $post_id, array $data ): void {
		$permalink = get_permalink( $post_id );
		if ( $permalink !== false ) {
			$this->updated_posts[ $post_id ] = $permalink;
		}
	}

	/**
	 * @param int $post_id
	 * @return void
	 */
	public function post_trashed( int $post_id ): void {
		$permalink = get_permalink( $post_id );
		if ( $permalink === false ) {
			return;
		}

		$data = array(
			'url'         => wp_parse_url( $permalink, PHP_URL_PATH ),
			'action_data' => array( 'url' => '/' ),
			'match_type'  => 'url',
			'action_type' => 'url',
			'action_code' => 301,
			'group_id'    => $this->monitor_group_id,
			'status'      => 'disabled',
		);

		// Create a new redirect for this post, but only if not draft
		if ( $data['url'] !== null && $data['url'] !== false && $data['url'] !== '/' ) {
			Red_Item::create( $data );
		}
	}

	/**
	 * Changed if permalinks are different and the before wasn't the site url (we don't want to redirect the site URL)
	 *
	 * @param bool $result
	 * @param string|false $before
	 * @param string|false $after
	 * @return bool
	 */
	public function has_permalink_changed( $result, $before, $after ) {
		// Check it's not redirecting from the root
		if ( $this->get_site_path() === $before || $before === '/' ) {
			return false;
		}

		// Are the URLs the same?
		if ( $before === $after ) {
			return false;
		}

		return true;
	}

	/**
	 * @return string
	 */
	private function get_site_path(): string {
		$path = wp_parse_url( get_site_url(), PHP_URL_PATH );

		if ( is_string( $path ) ) {
			return rtrim( $path, '/' ) . '/';
		}

		return '/';
	}

	/**
	 * @param int $post_id
	 * @param string $before
	 * @return bool
	 */
	public function check_for_modified_slug( int $post_id, string $before ): bool {
		$permalink = get_permalink( $post_id );
		if ( $permalink === false ) {
			return false;
		}

		$after = wp_parse_url( $permalink, PHP_URL_PATH );
		$before = wp_parse_url( esc_url( $before ), PHP_URL_PATH );

		if ( is_string( $before ) && is_string( $after ) && apply_filters( 'redirection_permalink_changed', false, $before, $after ) ) {
			do_action( 'redirection_remove_existing', $after, $post_id );

			$data = array(
				'url'         => $before,
				'action_data' => array( 'url' => $after ),
				'match_type'  => 'url',
				'action_type' => 'url',
				'action_code' => 301,
				'group_id'    => $this->monitor_group_id,
			);

			// Create a new redirect for this post
			$new_item = Red_Item::create( $data );

			if ( ! is_wp_error( $new_item ) ) {
				do_action( 'redirection_monitor_created', $new_item, $before, $post_id );

				if ( ! empty( $this->associated ) ) {
					// Create an associated redirect for this post
					$data['url'] = trailingslashit( $data['url'] ) . ltrim( $this->associated, '/' );
					$data['action_data'] = array( 'url' => trailingslashit( $data['action_data']['url'] ) . ltrim( $this->associated, '/' ) );
					Red_Item::create( $data );
				}
			}

			return true;
		}

		return false;
	}
}
