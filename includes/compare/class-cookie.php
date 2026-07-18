<?php

namespace Redirection\Compare;

use Redirection\Core\Regex;
use Redirection\Request\Request;

/**
 * Check that a cookie value exists
 */
class Cookie extends Header {
	public function name() {
		return __( 'URL and cookie', 'redirection' );
	}

	public function is_match( $url ) {
		if ( $this->regex ) {
			$regex = new Regex( $this->value, true );
			$cookie = Request::get_cookie( $this->name );
			if ( $cookie === false ) {
				return false;
			}

			return $regex->is_match( $cookie );
		}

		return Request::get_cookie( $this->name ) === $this->value;
	}
}
