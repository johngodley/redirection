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
		if ( $post->post_status !== 'publish' || $post_before->post_status !== 'publish' ) {
			return false;
		}

	 	if ( is_post_type_hierarchical( $post->post_type ) ) {
			return false;
		}

		if ( ! isset( $form_data['redirection_slug'] ) ) {
			return false;
		}

		return true;
	}

	public function post_updated( $post_id, $post, $post_before ) {
		if ( $this->can_monitor_post( $post, $post_before, $_POST ) ) {
			$after  = parse_url( get_permalink( $post_id ) );
			$after  = $after['path'];
			$before = esc_url( $_POST['redirection_slug'] );
			$site   = parse_url( get_site_url() );

			if ( $before !== $after && $before !== '/' && ( ! isset( $site['path'] ) || ( isset( $site['path'] ) && $before !== $site['path'].'/' ) ) ) {
				Red_Item::create( array(
					'source'     => $before,
					'target'     => $after,
					'match'      => 'url',
					'red_action' => 'url',
					'group_id'   => $this->monitor_group_id,
				) );
			}
		}
	}
}
