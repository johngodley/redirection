<?php

include_once dirname( __FILE__ ) . '/http-header.php';

class Cookie_Match extends Header_Match {
	public function name() {
		return __( 'URL and cookie', 'redirection' );
	}

	public function is_match( $url ) {
		if ( $this->regex ) {
			$regex = new Red_Regex( $this->value, true );
			return $regex->is_match( Redirection_Request::get_cookie( $this->name ) );
		}

		return Redirection_Request::get_cookie( $this->name ) === $this->value;
	}
}
