<?php

class Red_Monitor {
	private $monitor_group_id;

	function __construct( $options ) {
		if ( isset( $options['monitor_post'] ) && $options['monitor_post'] > 0 ) {
			$this->monitor_group_id = intval( $options['monitor_post'] );

			// Only monitor if permalinks enabled
			if ( get_option( 'permalink_structure' ) ) {
				add_action( 'post_updated', array( &$this, 'post_updated' ), 11, 3 );
				add_action( 'edit_form_advanced', array( &$this, 'insert_old_post' ) );
				add_action( 'edit_page_form',     array( &$this, 'insert_old_post' ) );
			}
		}
	}

	public function insert_old_post() {
		global $post;

		$url = parse_url( get_permalink() );
		$url = $url['path'];

?>
	<input type="hidden" name="redirection_slug" value="<?php echo esc_attr( $url ) ?>"/>
<?php
	}

	public function can_monitor_post( $post, $post_before, $form_data ) {
		// Don't do anything if we're not published
		if ( $post->post_status !== 'publish' || $post_before->post_status !== 'publish' ) {
			return false;
		}

		// Hierarchical post? Do nothing
	 	if ( is_post_type_hierarchical( $post->post_type ) ) {
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

	/**
	 * Changed if permalinks are different and the before wasn't the site url (we don't want to redirect the site URL)
	 */
	public function has_permalink_changed( $before, $after ) {
		if ( $before !== $after && $this->get_site_path() !== $before ) {
			return true;
		}

		return false;
	}

	private function get_site_path() {
		$path = parse_url( get_site_url(), PHP_URL_PATH );

		if ( $path ) {
			return rtrim( $path, '/' ).'/';
		}

		return '/';
	}

	public function check_for_modified_slug( $post_id, $before ) {
		$after  = parse_url( get_permalink( $post_id ) );
		$after  = $after['path'];
		$before = esc_url( $before );

		if ( $this->has_permalink_changed( $before, $after ) ) {
			Red_Item::create( array(
				'source'     => $before,
				'target'     => $after,
				'match'      => 'url',
				'red_action' => 'url',
				'group_id'   => $this->monitor_group_id,
			) );

			return true;
		}

		return false;
	}
}
