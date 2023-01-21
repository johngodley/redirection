<?php

class Redirection_Request {
	/**
	 * URL friendly sanitize_text_fields which lets encoded characters through and doesn't trim
	 *
	 * @param string $value Value.
	 * @return string
	 */
	public static function sanitize_url( $value ) {
		// Remove invalid UTF
		$url = wp_check_invalid_utf8( $value, true );

		// No new lines
		$url = preg_replace( "/[\r\n\t].*?$/s", '', $url );

		// Clean control codes
		$url = preg_replace( '/[^\PC\s]/u', '', $url );

		return $url;
	}

	/**
	 * Get HTTP headers
	 *
	 * @return array
	 */
	public static function get_request_headers() {
		$ignore = apply_filters( 'redirection_request_headers_ignore', [
			'cookie',
			'host',
		] );
		$headers = [];

		foreach ( $_SERVER as $name => $value ) {
			$value = sanitize_text_field( $value );
			$name = sanitize_text_field( $name );

			if ( substr( $name, 0, 5 ) === 'HTTP_' ) {
				$name = strtolower( substr( $name, 5 ) );
				$name = str_replace( '_', ' ', $name );
				$name = ucwords( $name );
				$name = str_replace( ' ', '-', $name );

				if ( ! in_array( strtolower( $name ), $ignore, true ) ) {
					$headers[ $name ] = $value;
				}
			}
		}

		return apply_filters( 'redirection_request_headers', $headers );
	}

	/**
	 * Get request method
	 *
	 * @return string
	 */
	public static function get_request_method() {
		$method = '';

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && is_string( $_SERVER['REQUEST_METHOD'] ) ) {
			$method = sanitize_text_field( $_SERVER['REQUEST_METHOD'] );
		}

		return apply_filters( 'redirection_request_method', $method );
	}

	/**
	 * Get the server name (from $_SERVER['SERVER_NAME]), or use the request name ($_SERVER['HTTP_HOST']) if not present
	 *
	 * @return string
	 */
	public static function get_server_name() {
		$host = self::get_request_server_name();

		if ( isset( $_SERVER['SERVER_NAME'] ) && is_string( $_SERVER['SERVER_NAME'] ) ) {
			$host = sanitize_text_field( $_SERVER['SERVER_NAME'] );
		}

		return apply_filters( 'redirection_request_server', $host );
	}

	/**
	 * Get the request server name (from $_SERVER['HTTP_HOST])
	 *
	 * @return string
	 */
	public static function get_request_server_name() {
		$host = '';

		if ( isset( $_SERVER['HTTP_HOST'] ) && is_string( $_SERVER['HTTP_HOST'] ) ) {
			$host = sanitize_text_field( $_SERVER['HTTP_HOST'] );
		}

		return apply_filters( 'redirection_request_server_host', $host );
	}

	/**
	 * Get server name + protocol
	 *
	 * @return string
	 */
	public static function get_server() {
		return self::get_protocol() . '://' . self::get_server_name();
	}

	/**
	 * Get protocol
	 *
	 * @return string
	 */
	public static function get_protocol() {
		return is_ssl() ? 'https' : 'http';
	}

	/**
	 * Get request protocol
	 *
	 * @return string
	 */
	public static function get_request_url() {
		$url = '';

		if ( isset( $_SERVER['REQUEST_URI'] ) && is_string( $_SERVER['REQUEST_URI'] ) ) {
			$url = self::sanitize_url( $_SERVER['REQUEST_URI'] );
		}

		return apply_filters( 'redirection_request_url', stripslashes( $url ) );
	}

	/**
	 * Get user agent
	 *
	 * @return string
	 */
	public static function get_user_agent() {
		$agent = '';

		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && is_string( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$agent = sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] );
		}

		return apply_filters( 'redirection_request_agent', $agent );
	}

	/**
	 * Get referrer
	 *
	 * @return string
	 */
	public static function get_referrer() {
		$referrer = '';

		if ( isset( $_SERVER['HTTP_REFERER'] ) && is_string( $_SERVER['HTTP_REFERER'] ) ) {
			$referrer = self::sanitize_url( $_SERVER['HTTP_REFERER'] );
		}

		return apply_filters( 'redirection_request_referrer', $referrer );
	}

	/**
	 * Get standard IP header names
	 *
	 * @return string[]
	 */
	public static function get_ip_headers() {
		return [
			'HTTP_CF_CONNECTING_IP',
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'HTTP_VIA',
			'REMOTE_ADDR',
		];
	}

	/**
	 * Get browser IP
	 *
	 * @return string
	 */
	public static function get_ip() {
		$ip = '';

		foreach ( self::get_ip_headers() as $var ) {
			if ( ! empty( $_SERVER[ $var ] ) && is_string( $_SERVER[ $var ] ) ) {
				$ip = sanitize_text_field( $_SERVER[ $var ] );
				$ip = explode( ',', $ip );
				$ip = array_shift( $ip );
				break;
			}
		}

		// Convert to binary
		// phpcs:ignore
		$ip = @inet_pton( trim( $ip ) );
		if ( $ip !== false ) {
			// phpcs:ignore
			$ip = @inet_ntop( $ip );  // Convert back to string
		}

		return apply_filters( 'redirection_request_ip', $ip ? $ip : '' );
	}

	/**
	 * Get a cookie
	 *
	 * @param string $cookie Name.
	 * @return string|false
	 */
	public static function get_cookie( $cookie ) {
		if ( isset( $_COOKIE[ $cookie ] ) && is_string( $_COOKIE[ $cookie ] ) ) {
			return apply_filters( 'redirection_request_cookie', sanitize_text_field( $_COOKIE[ $cookie ] ), $cookie );
		}

		return false;
	}

	/**
	 * Get a HTTP header
	 *
	 * @param string $name Header name.
	 * @return string|false
	 */
	public static function get_header( $name ) {
		$name = 'HTTP_' . strtoupper( $name );
		$name = str_replace( '-', '_', $name );

		if ( isset( $_SERVER[ $name ] ) && is_string( $_SERVER[ $name ] ) ) {
			return apply_filters( 'redirection_request_header', sanitize_text_field( $_SERVER[ $name ] ), $name );
		}

		return false;
	}

	/**
	 * Get browser accept language
	 *
	 * @return string[]
	 */
	public static function get_accept_language() {
		if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) && is_string( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			$languages = preg_replace( '/;.*$/', '', sanitize_text_field( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) );
			$languages = str_replace( ' ', '', $languages );

			return apply_filters( 'redirection_request_accept_language', explode( ',', $languages ) );
		}

		return [];
	}
}
