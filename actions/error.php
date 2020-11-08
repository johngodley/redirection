<?php

/**
 * Return an error to the client, and trigger the WordPress error page
 */
class Error_Action extends Red_Action {
	/**
	 * Set WordPress to show the error page
	 *
	 * @return void
	 */
	public function run() {
		wp_reset_query();

		// Set the query to be a 404
		set_query_var( 'is_404', true );

		// Return the 404 page
		add_filter( 'template_include', [ $this, 'template_include' ] );

		// Clear any posts if this is actually a valid URL
		add_filter( 'pre_handle_404', [ $this, 'pre_handle_404' ] );

		// Ensure the appropriate http code is returned
		add_action( 'wp', [ $this, 'wp' ] );
	}

	/**
	 * Output selected HTTP code, as well as redirection header
	 *
	 * @return void
	 */
	public function wp() {
		status_header( $this->code );
		nocache_headers();

		global $wp_version;

		if ( version_compare( $wp_version, '5.1', '<' ) ) {
			header( 'X-Redirect-Agent: redirection' );
		} else {
			header( 'X-Redirect-By: redirection' );
		}
	}

	public function pre_handle_404() {
		global $wp_query;

		// Page comments plugin interferes with this
		$wp_query->posts = [];
		return false;
	}

	public function template_include() {
		return get_404_template();
	}
}
