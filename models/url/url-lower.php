<?php

require_once __DIR__ . '/url-path.php';

/**
 * Global URL lowercase helper.
 *
 * When the "force lowercase" option is enabled this builds a canonical target
 * URL for requests that contain uppercase characters in the path.
 */
class Red_Url_Lowercase {
	/**
	 * Get a lowercased target URL for a request, or false if no change is needed.
	 *
	 * @param string $request_url The current request URL (path + query, e.g. `/Page?x=1`).
	 * @param string $base_url    The canonical base URL (e.g. `home_url()` or a domain-canonical target).
	 * @return string|false
	 */
	public static function get_target( $request_url, $base_url ) {
		$request_parts = wp_parse_url( $request_url );

		if ( ! is_array( $request_parts ) || ! isset( $request_parts['path'] ) ) {
			return false;
		}

		$path = $request_parts['path'];
		$lower_path = Red_Url_Path::to_lower( $path );

		// Only redirect when the path actually changes.
		if ( $lower_path === $path ) {
			return false;
		}

		if ( self::is_protected_path( $path ) ) {
			return false;
		}

		$base_parts = wp_parse_url( $base_url );

		if ( ! is_array( $base_parts ) ) {
			return false;
		}

		return self::build_url( $base_parts, $lower_path, $request_parts['query'] ?? '' );
	}

	/**
	 * Should this path be left alone?
	 *
	 * @param string $path URL path.
	 * @return boolean
	 */
	private static function is_protected_path( $path ) {
		$protected = apply_filters(
			'redirection_lowercase_protected',
			[ '/wp-admin', '/wp-login.php', '/wp-json/', '/wp-cron.php' ],
			$path
		);

		foreach ( $protected as $prefix ) {
			if ( substr( $path, 0, strlen( $prefix ) ) === $prefix ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Build a URL from canonical base parts and a new lowercased path.
	 *
	 * @param array<string, mixed> $parts URL parts from wp_parse_url().
	 * @param string               $path  New path.
	 * @param string               $query New query string.
	 * @return string
	 */
	private static function build_url( array $parts, $path, $query ) {
		$url = '';

		if ( isset( $parts['scheme'] ) ) {
			$url .= $parts['scheme'] . '://';
		}

		if ( isset( $parts['user'] ) ) {
			$url .= $parts['user'];

			if ( isset( $parts['pass'] ) ) {
				$url .= ':' . $parts['pass'];
			}

			$url .= '@';
		}

		if ( isset( $parts['host'] ) ) {
			$url .= $parts['host'];
		}

		if ( isset( $parts['port'] ) ) {
			$url .= ':' . $parts['port'];
		}

		$url .= $path;

		if ( $query !== '' ) {
			$url .= '?' . $query;
		}

		return $url;
	}
}
