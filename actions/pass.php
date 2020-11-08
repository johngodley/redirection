<?php

require_once dirname( __FILE__ ) . '/url.php';

/**
 * A 'pass through' action. Matches a rewrite rather than a redirect, and uses PHP to fetch data from a remote URL.
 */
class Pass_Action extends Url_Action {
	/**
	 * Process an external passthrough - a URL that lives external to this server.
	 *
	 * @param String $target Target URL.
	 * @return void
	 */
	public function process_external( $url ) {
		// This is entirely at the user's risk. The $url is set by the user
		echo wp_remote_fopen( $url );
	}

	/**
	 * Process an internal passthrough - a URL that lives on the same server. Here we change the request URI and continue without making a remote request.
	 *
	 * @param String $target Target URL.
	 * @return void
	 */
	public function process_internal( $target ) {
		// Another URL on the server
		$_SERVER['REQUEST_URI'] = $target;

		if ( strpos( $target, '?' ) ) {
			$_SERVER['QUERY_STRING'] = substr( $target, strpos( $target, '?' ) + 1 );
			parse_str( $_SERVER['QUERY_STRING'], $_GET );
		}

		return true;
	}

	/**
	 * Is a URL external?
	 *
	 * @param String $target URL to test.
	 * @return boolean
	 */
	public function is_external( $target ) {
		return substr( $target, 0, 7 ) === 'http://' || substr( $target, 0, 8 ) === 'https://';
	}

	/**
	 * Pass the data from the target
	 *
	 * @return void
	 */
	public function run() {
		// External target
		if ( $this->is_external( $this->get_target() ) ) {
			$this->process_external( $this->get_target() );
			exit();
		}

		return $this->process_internal( $this->get_target() );
	}
}
