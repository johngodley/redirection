<?php

class Error_Action extends Red_Action {
	function process_after( $code, $target ) {
		global $wp_query;
		$wp_query->is_404 = true;

		// Page comments plugin interferes with this
		remove_filter( 'template_redirect', 'paged_comments_alter_source', 12 );
	}
}
