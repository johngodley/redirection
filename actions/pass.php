<?php

class Pass_Action extends Red_Action {
	public function process_external( $url ) {
		echo @wp_remote_fopen( $url );
	}

	/**
	 * This is deprecated and will be removed in a future version
	 */
	public function process_file( $url ) {
		$parts = explode( '?', substr( $url, 7 ) );

		if ( count( $parts ) > 1 ) {
			// Put parameters into the environment
			$args = explode( '&', $parts[1] );

			if ( count( $args ) > 0 ) {
				foreach ( $args as $arg ) {
					$tmp = explode( '=', $arg );

					if ( count( $tmp ) === 1 ) {
						$_GET[ $arg ] = '';
					} else {
						$_GET[ $tmp[0] ] = $tmp[1];
					}
				}
			}
		}

		@include $parts[0];
	}

	public function process_internal( $target ) {
		// Another URL on the server
		$_SERVER['REQUEST_URI'] = $target;

		if ( strpos( $target, '?' ) ) {
			$_SERVER['QUERY_STRING'] = substr( $target, strpos( $target, '?' ) + 1 );
			parse_str( $_SERVER['QUERY_STRING'], $_GET );
		}

		return true;
	}

	public function is_external( $target ) {
		return substr( $target, 0, 7 ) === 'http://' || substr( $target, 0, 8 ) === 'https://';
	}

	public function process_before( $code, $target ) {
		// External target
		if ( $this->is_external( $target ) ) {
			$this->process_external( $target );
			exit();
		}

		return $this->process_internal( $target );
	}

	public function needs_target() {
		return true;
	}
}
