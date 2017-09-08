<?php

class Url_Action extends Red_Action {
	function process_before( $code, $target ) {
		$redirect = wp_redirect( $target, $code );
		if ( $redirect ) {
			die();
		}
	}
}
