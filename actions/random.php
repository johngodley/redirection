<?php

include_once dirname( __FILE__ ) . '/url.php';

class Random_Action extends Url_Action {
	public function process_before( $code, $target ) {
		// Pick a random WordPress page
		global $wpdb;

		$id = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='publish' AND post_password='' AND post_type='post' ORDER BY RAND() LIMIT 0,1" );
		return str_replace( get_bloginfo( 'url' ), '', get_permalink( $id ) );
	}

	public function process_after( $code, $target ) {
		$this->redirect_to( $code, $target );
	}

	public function needs_target() {
		return true;
	}
}
