<?php

class Red_Url_Path {
	private $path;

	public function __construct( $path ) {
		$this->path = $this->get_path_component( $path );
	}

	public function is_match( $url, Red_Source_Flags $flags ) {
		$target = new Red_Url_Path( $url );

		$target_path = $target->get();
		$source_path = $this->get();

		if ( $flags->is_ignore_trailing() ) {
			// Ignore trailing slashes
			$source_path = $this->get_without_trailing_slash();
			$target_path = $target->get_without_trailing_slash();
		}

		if ( $flags->is_ignore_case() ) {
			// Case insensitive match
			$source_path = self::to_lower( $source_path );
			$target_path = self::to_lower( $target_path );
		}

		return $target_path === $source_path;
	}

	public static function to_lower( $url ) {
		if ( function_exists( 'mb_strtolower' ) ) {
			return mb_strtolower( $url );
		}

		return strtolower( $url );
	}

	public function get() {
		return $this->path;
	}

	public function get_without_trailing_slash() {
		// Return / or // as-is
		if ( $this->path === '/' ) {
			return $this->path;
		}

		// Anything else remove the last /
		return preg_replace( '@/$@', '', $this->get() );
	}

	// parse_url doesn't handle 'incorrect' URLs, such as those with double slashes
	// These are often used in redirects, so we fall back to our own parsing
	private function get_path_component( $url ) {
		$path = $url;

		if ( preg_match( '@^https?://@', $url, $matches ) > 0 ) {
			$parts = explode( '://', $url );

			if ( count( $parts ) > 1 ) {
				$rest = explode( '/', $parts[1] );
				$path = '/' . implode( '/', array_slice( $rest, 1 ) );
			}
		}

		return urldecode( $this->get_query_before( $path ) );
	}

	private function get_query_before( $url ) {
		$qpos = strpos( $url, '?' );
		$qrpos = strpos( $url, '\\?' );

		if ( $qrpos !== false && $qrpos < $qpos ) {
			return substr( $url, 0, $qrpos + strlen( $qrpos ) - 1 );
		}

		if ( $qpos === false ) {
			return $url;
		}

		return substr( $url, 0, $qpos );
	}
}
