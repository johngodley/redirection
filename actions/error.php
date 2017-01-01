<?php

class Error_Action extends Red_Action {
	function can_change_code() {
		return true;
	}

	function can_perform_action() {
		return false;
	}

	function action_codes() {
		return array(
			404 => get_status_header_desc( 404 ),
			410 => get_status_header_desc( 410 ),
		);
	}

	function process_after( $code, $target ) {
		global $wp_query;
		$wp_query->is_404 = true;

		// Page comments plugin interferes with this
		remove_filter( 'template_redirect', 'paged_comments_alter_source', 12 );
	}
}
