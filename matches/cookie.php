<?php

include_once dirname( __FILE__ ) . '/http-header.php';

class Cookie_Match extends Header_Match {
	function name() {
		return __( 'URL and cookie', 'redirection' );
	}

	function get_target( $requested_url, $source_url, Red_Source_Flags $flags ) {
		$target = false;
		$matched = Redirection_Request::get_cookie( $this->name ) === $this->value;

		if ( $this->regex ) {
			$regex = new Red_Regex( $this->value, true );
			$matched = $regex->is_match( Redirection_Request::get_cookie( $this->name ) );
		}

		// Check if referrer matches
		if ( $matched && $this->url_from !== '' ) {
			$target = $this->url_from;
		} elseif ( ! $matched && $this->url_notfrom !== '' ) {
			$target = $this->url_notfrom;
		}

		if ( $flags->is_regex() && $target ) {
			$target = $this->get_target_regex_url( $source_url, $target, $requested_url, $flags );
		}

		return $target;
	}
}
