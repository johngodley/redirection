<?php

class Error_Action extends Red_Action {
	function process_before( $code, $target ) {
		$this->code = $code;

		wp_reset_query();
		set_query_var( 'is_404', true );

		add_filter( 'template_include', [ $this, 'template_include' ] );
		add_filter( 'pre_handle_404', [ $this, 'pre_handle_404' ] );
		add_action( 'wp', [ $this, 'wp' ] );

		return true;
	}

	public function wp() {
		status_header( $this->code );
		nocache_headers();

		global $wp_version;

		if ( version_compare( $wp_version, '5.1', '<' ) ) {
			header( 'X-Redirect-Agent: redirection' );
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

	public function needs_target() {
		return false;
	}
}
