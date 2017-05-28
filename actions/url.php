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
            308 => get_status_header_desc( 308 ),
		);
	}

	function process_before( $code, $target ) {
		$redirect = wp_redirect( $target, $code );
		if ( $redirect ) {
			die();
		}
	}
}
