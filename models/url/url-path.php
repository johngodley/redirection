<?php

/**
 * The path part of a URL
 */
class Red_Url_Path {
	/**
	 * URL path
	 *
	 * @var String
	 */
	private $path;

	/**
	 * Constructor
	 *
	 * @param String $path URL.
	 */
	public function __construct( $path ) {
		$this->path = $this->get_path_component( $path );
	}

	/**
	 * Is the supplied `url` a match for this object?
	 *
	 * @param String           $url URL to match against.
	 * @param Red_Source_Flags $flags Source flags to use in match.
	 * @return boolean
	 */
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

	/**
	 * Convert a URL to lowercase
	 *
	 * @param String $url URL.
	 * @return String
	 */
	public static function to_lower( $url ) {
		if ( function_exists( 'mb_strtolower' ) ) {
			return mb_strtolower( $url, 'UTF-8' );
		}

		return strtolower( $url );
	}

	/**
	 * Get the path value
	 *
	 * @return String
	 */
	public function get() {
		return $this->path;
	}

	/**
	 * Get the path value without trailing slash, or `/` if home
	 *
	 * @return String
	 */
	public function get_without_trailing_slash() {
		// Return / or // as-is
		if ( $this->path === '/' ) {
			return $this->path;
		}

		// Anything else remove the last /
		return preg_replace( '@/$@', '', $this->get() );
	}

	/**
	 * `parse_url` doesn't handle 'incorrect' URLs, such as those with double slashes
	 * These are often used in redirects, so we fall back to our own parsing
	 *
	 * @param String $url URL.
	 * @return String
	 */
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

	/**
	 * Get the path component up to the query string
	 *
	 * @param String $url URL.
	 * @return String
	 */
	private function get_query_before( $url ) {
		$qpos = strpos( $url, '?' );
		$qrpos = strpos( $url, '\\?' );

		// Have we found an escaped query and it occurs before a normal query?
		if ( $qrpos !== false && $qrpos < $qpos ) {
			// Yes, the path is everything up to the escaped query
			return substr( $url, 0, $qrpos );
		}

		// No query - return everything as path
		if ( $qpos === false ) {
			return $url;
		}

		// Query found - return everything up to it
		return substr( $url, 0, $qpos );
	}
}
