<?php

class Pass_Action extends Red_Action {
	function process_before( $code, $target ) {
		// Determine what we are passing to:  local URL, remote URL, file
		if ( substr( $target, 0, 7 ) === 'http://' || substr( $target, 0, 8 ) === 'https://' ) {
			echo @wp_remote_fopen( $target );
			die();
		}
		else if ( substr( $target, 0, 7 ) === 'file://' ) {
			$parts = explode( '?', substr( $target, 7 ) );
			if ( count( $parts ) > 1 ) {
				// Put parameters into the environment
				$args = explode( '&', $parts[1] );

				if ( count( $args ) > 0 ) {
					foreach ( $args as $arg ) {
						$tmp = explode( '=', $arg );
						if ( count( $tmp ) === 1 )
							$_GET[ $arg ] = '';
						else
							$_GET[ $tmp[0] ] = $tmp[1];
					}
				}
			}

			include( $parts[0] );
			exit();
		}
		else {
			$_SERVER['REQUEST_URI'] = $target;
			if ( strpos( $target, '?' ) ) {
				$_SERVER['QUERY_STRING'] = substr( $target, strpos( $target, '?' ) + 1 );
				parse_str( $_SERVER['QUERY_STRING'], $_GET );
			}
		}

		return true;
	}
}
