<?php

require_once dirname( __FILE__ ) . '/url.php';

/**
 * A 'pass through' action. Matches a rewrite rather than a redirect, and uses PHP to fetch data from a remote URL.
 */
class Pass_Action extends Url_Action {
	/**
	 * Process an external passthrough - a URL that lives external to this server.
	 *
	 * @param string $url Target URL.
	 * @return void
	 */
	public function process_external( $url ) {
		// This is entirely at the user's risk. The $url is set by the user
		// phpcs:ignore
		echo wp_remote_fopen( $url );
	}

	/**
	 * Process an internal passthrough - a URL that lives on the same server. Here we change the request URI and continue without making a remote request.
	 *
	 * @param string $target Target URL.
	 * @return void
	 */
	public function process_internal( $target ) {
		// Another URL on the server
		$pos = strpos( $target, '?' );
		$_SERVER['REQUEST_URI'] = $target;
		$_SERVER['PATH_INFO'] = $target;

		if ( $pos ) {
			$_SERVER['QUERY_STRING'] = substr( $target, $pos + 1 );
			$_SERVER['PATH_INFO'] = $target;

			// Take the query params in the target and make them the params for this request
			parse_str( $_SERVER['QUERY_STRING'], $_GET );
		}
	}

	/**
	 * Is a URL external?
	 *
	 * @param string $target URL to test.
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
		$target = $this->get_target();
		if ( $target === null ) {
			return;
		}

		if ( $this->is_external( $target ) ) {
			// Pass on to an external request, echo the results, and then stop
			$this->process_external( $target );
			exit();
		}

		// Change the request and carry on
		$this->process_internal( $target );
	}

	public function name() {
		return __( 'Pass-through', 'redirection' );
	}
}
