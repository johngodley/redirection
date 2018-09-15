<?php

class Error_Action extends Red_Action {
	function process_before( $code, $target ) {
		$this->code = $code;

		wp_reset_query();
		set_query_var( 'is_404', true );

		add_filter( 'template_include', array( $this, 'template_include' ) );
		add_filter( 'pre_handle_404', array( $this, 'pre_handle_404' ) );
		add_action( 'wp', array( $this, 'wp' ) );

		return false;
	}

	public function wp() {
		status_header( $this->code );
		nocache_headers();
	}

	public function pre_handle_404() {
		global $wp_query;

		// Page comments plugin interferes with this
		$wp_query->posts = array();
		return false;
	}

	public function template_include() {
		return get_404_template();
	}
}
