<?php

namespace Redirection\Match;

use Redirection\Site;

require_once dirname( __FILE__ ) . '/http-header.php';

/**
 * Check that a cookie value exists
 */
class Cookie extends Header {
	public function name() {
		return __( 'URL and cookie', 'redirection' );
	}

	public function is_match( $url ) {
		if ( $this->regex ) {
			$regex = new Site\Regex( $this->value, true );
			return $regex->is_match( Site\Request::get_cookie( $this->name ) );
		}

		return Site\Request::get_cookie( $this->name ) === $this->value;
	}
}
