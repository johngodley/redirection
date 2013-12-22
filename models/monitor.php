<?php

class Red_Monitor {
	var $monitor_post;

	function Red_Monitor( $options ) {
		if ( $options['monitor_post'] > 0 ) {
			$this->monitor_post = $options['monitor_post'];

			// Only monitor if permalinks enabled
			if ( get_option( 'permalink_structure' ) ) {
				add_action( 'post_updated', array( &$this, 'post_updated' ), 11, 3 );
				add_action( 'edit_form_advanced', array( &$this, 'insert_old_post' ) );
				add_action( 'edit_page_form',     array( &$this, 'insert_old_post' ) );
			}
		}
	}

	function insert_old_post() {
		global $post;

		$url = parse_url( get_permalink() );
		$url = $url['path'];
?>
	<input type="hidden" name="redirection_slug" value="<?php echo esc_attr( $url ) ?>"/>
<?php
	}

	function post_updated( $post_id, $post, $post_before ) {
		if ( $post->post_status != 'publish' || is_post_type_hierarchical( $post->post_type ) )
			return;

		if ( isset( $_POST['redirection_slug'] ) ) {
			$after  = parse_url( get_permalink( $post_id ) );
			$after  = $after['path'];
			$before = esc_url( $_POST['redirection_slug'] );
			$site   = parse_url( get_site_url() );

			if ( in_array( $post->post_status, array( 'publish', 'static' ) ) && $before != $after && $before != '/' && ( !isset( $site['path'] ) || ( isset( $site['path'] ) && $before != $site['path'].'/' ) ) ) {
				Red_Item::create( array(
					'source'     => $before,
					'target'     => $after,
					'match'      => 'url',
					'red_action' => 'url',
					'group'      => $this->monitor_post
				) );
			}
		}
	}
}
