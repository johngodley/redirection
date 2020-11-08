<?php

require_once dirname( __FILE__ ) . '/url.php';

/**
 * URL action - redirect to a URL
 */
class Random_Action extends Url_Action {
	/**
	 * Get a random URL
	 *
	 * @return string|null
	 */
	private function get_random_url() {
		// Pick a random WordPress page
		global $wpdb;

		$id = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='publish' AND post_password='' AND post_type='post' ORDER BY RAND() LIMIT 0,1" );
		if ( $id ) {
			$url = get_permalink( $id );

			if ( $url ) {
				return $url;
			}
		}

		return null;
	}

	/**
	 * Run this action. May not return from this function.
	 *
	 * @return void
	 */
	public function run() {
		$target = $this->get_random_url();

		if ( $target ) {
			$this->redirect_to( $target );
		}
	}
}
