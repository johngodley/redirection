<?php

require_once __DIR__ . '/http-header.php';

/**
 * Check that a cookie value exists
 */
class Cookie_Match extends Header_Match {
	public function name() {
		return __( 'URL and cookie', 'redirection' );
	}

	public function is_match( $url ) {
		if ( $this->regex ) {
			$regex = new Red_Regex( $this->value, true );
			$cookie = Redirection_Request::get_cookie( $this->name );
			if ( $cookie === false ) {
				return false;
			}

			return $regex->is_match( $cookie );
		}

		return Redirection_Request::get_cookie( $this->name ) === $this->value;
	}
}
