<?php

class Red_Monitor {
	private $monitor_group_id;

	function __construct( $options ) {
		$this->monitor_types = apply_filters( 'redirection_monitor_types', isset( $options['monitor_types'] ) ? $options['monitor_types'] : array() );

		if ( count( $this->monitor_types ) > 0 && $options['monitor_post'] > 0 ) {
			$this->monitor_group_id = intval( $options['monitor_post'], 10 );
			$this->associated = isset( $options['associated_redirect'] ) ? $options['associated_redirect'] : '';

			// Only monitor if permalinks enabled
			if ( get_option( 'permalink_structure' ) ) {
				add_action( 'post_updated', array( $this, 'post_updated' ), 11, 3 );
				add_action( 'edit_form_advanced', array( $this, 'insert_old_post' ) );
				add_action( 'edit_page_form',     array( $this, 'insert_old_post' ) );
				add_filter( 'redirection_remove_existing', array( $this, 'remove_existing_redirect' ) );
				add_filter( 'redirection_permalink_changed', array( $this, 'has_permalink_changed' ), 10, 3 );

				if ( in_array( 'trash', $this->monitor_types ) ) {
					add_action( 'wp_trash_post', array( $this, 'post_trashed' ) );
				}
			}
		}
	}

	public function remove_existing_redirect( $url ) {
		Red_Item::disable_where_matches( $url );
	}

	public function insert_old_post() {
		$url = parse_url( get_permalink(), PHP_URL_PATH );

?>
	<input type="hidden" name="redirection_slug" value="<?php echo esc_attr( $url ) ?>"/>
<?php
	}

	public function can_monitor_post( $post, $post_before, $form_data ) {
		// Check this is the for the expected post
		if ( ! isset( $form_data['ID'] ) || ! isset( $post->ID ) || $form_data['ID'] !== $post->ID ) {
            return false;
        }

		// Don't do anything if we're not published
		if ( $post->post_status !== 'publish' || $post_before->post_status !== 'publish' ) {
			return false;
		}

		// Hierarchical post? Do nothing
		$type = get_post_type( $post->ID );
		if ( is_post_type_hierarchical( $post->post_type ) && $type !== 'page' ) {
			return false;
		}

		if ( ! in_array( $type, $this->monitor_types ) ) {
			return false;
		}

		// Old Redirection slug not defined? Do nothing
		if ( ! isset( $form_data['redirection_slug'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Called when a post has been updated - check if the slug has changed
	 */
	public function post_updated( $post_id, $post, $post_before ) {
		if ( $this->can_monitor_post( $post, $post_before, $_POST ) ) {
			$this->check_for_modified_slug( $post_id, $_POST['redirection_slug'] );
		}
	}

	public function post_trashed( $post_id ) {
		$data = array(
			'url'         => parse_url( get_permalink( $post_id ), PHP_URL_PATH ),
			'action_data' => array( 'url' => '/' ),
			'match_type'  => 'url',
			'action_type' => 'url',
			'action_code' => 301,
			'group_id'    => $this->monitor_group_id,
			'status'      => 'disabled',
		);

		// Create a new redirect for this post
		Red_Item::create( $data );
	}

	/**
	 * Changed if permalinks are different and the before wasn't the site url (we don't want to redirect the site URL)
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

	private function get_site_path() {
		$path = parse_url( get_site_url(), PHP_URL_PATH );

		if ( $path ) {
			return rtrim( $path, '/' ).'/';
		}

		return '/';
	}

	public function check_for_modified_slug( $post_id, $before ) {
		$after  = parse_url( get_permalink( $post_id ), PHP_URL_PATH );
		$before = esc_url( $before );

		if ( apply_filters( 'redirection_permalink_changed', false, $before, $after ) ) {
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
			Red_Item::create( $data );

			if ( !empty( $this->associated ) ) {
				// Create an associated redirect for this post
				$data['url'] = trailingslashit( $data['url'] ) . ltrim( $this->associated, '/' );
				$data['action_data'] = array( 'url' => trailingslashit( $data['action_data']['url'] ) . ltrim( $this->associated, '/' ) );
				Red_Item::create( $data );
			}

			return true;
		}

		return false;
	}
}
