<?php

include_once dirname( __FILE__ ) . '/http-header.php';

class Cookie_Match extends Header_Match {
	function name() {
		return __( 'URL and cookie', 'redirection' );
	}

	function get_target( $url, $matched_url, $regex ) {
		$target = false;
		$matched = Redirection_Request::get_cookie( $this->name ) === $this->value;

		if ( $this->regex ) {
			$matched = preg_match( '@' . str_replace( '@', '\\@', $this->value ) . '@', Redirection_Request::get_cookie( $this->name ), $matches ) > 0;
		}

		// Check if referrer matches
		if ( $matched && $this->url_from !== '' ) {
			$target = $this->url_from;
		} elseif ( ! $matched && $this->url_notfrom !== '' ) {
			$target = $this->url_notfrom;
		}

		if ( $regex && $target ) {
			$target = $this->get_target_regex_url( $matched_url, $target, $url );
		}

		return $target;
	}
}
