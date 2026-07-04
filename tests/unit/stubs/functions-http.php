<?php

if ( ! function_exists( 'get_status_header_desc' ) ) {
	function get_status_header_desc( $code ) {
		$valid = [
			301 => 'Moved Permanently',
			302 => 'Found',
			308 => 'Permanent Redirect',
			404 => 'Not Found',
			410 => 'Gone',
		];

		return $valid[ intval( $code, 10 ) ] ?? '';
	}
}
