<?php

class Url_Action extends Red_Action {
	function can_change_code() {
		return true;
	}

	function action_codes() {
		return array(
			301 => get_status_header_desc( 301 ),
			302 => get_status_header_desc( 302 ),
			307 => get_status_header_desc( 307 ),
		);
	}

	function process_before( $code, $target ) {
		wp_redirect( $target, $code );
		die();
	}
}
