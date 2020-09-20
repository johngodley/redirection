<?php

class Redirection_Request {
	public static function get_request_headers() {
		$ignore = apply_filters( 'redirection_request_headers_ignore', [
			'cookie',
			'host',
		] );
		$headers = [];

		foreach ( $_SERVER as $name => $value ) {
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

	public static function get_request_method() {
		$method = '';

		if ( isset( $_SERVER['REQUEST_METHOD'] ) ) {
			$method = $_SERVER['REQUEST_METHOD'];
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

		if ( isset( $_SERVER['SERVER_NAME'] ) ) {
			$host = $_SERVER['SERVER_NAME'];
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

		if ( isset( $_SERVER['HTTP_HOST'] ) ) {
			$host = $_SERVER['HTTP_HOST'];
		}

		return apply_filters( 'redirection_request_server_host', $host );
	}

	public static function get_server() {
		return self::get_protocol() . '://' . self::get_server_name();
	}

	public static function get_protocol() {
		return is_ssl() ? 'https' : 'http';
	}

	public static function get_request_url() {
		$url = '';

		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$url = $_SERVER['REQUEST_URI'];
		}

		return apply_filters( 'redirection_request_url', stripslashes( $url ) );
	}

	public static function get_user_agent() {
		$agent = '';

		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$agent = $_SERVER['HTTP_USER_AGENT'];
		}

		return apply_filters( 'redirection_request_agent', $agent );
	}

	public static function get_referrer() {
		$referrer = '';

		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$referrer = $_SERVER['HTTP_REFERER'];
		}

		return apply_filters( 'redirection_request_referrer', $referrer );
	}

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

	public static function get_ip() {
		$ip = '';

		foreach ( self::get_ip_headers() as $var ) {
			if ( ! empty( $_SERVER[ $var ] ) ) {
				$ip = $_SERVER[ $var ];
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

	public static function get_cookie( $cookie ) {
		if ( isset( $_COOKIE[ $cookie ] ) ) {
			return apply_filters( 'redirection_request_cookie', $_COOKIE[ $cookie ], $cookie );
		}

		return false;
	}

	public static function get_header( $name ) {
		$name = 'HTTP_' . strtoupper( $name );
		$name = str_replace( '-', '_', $name );

		if ( isset( $_SERVER[ $name ] ) ) {
			return apply_filters( 'redirection_request_header', $_SERVER[ $name ], $name );
		}

		return false;
	}

	public static function get_accept_language() {
		if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			$languages = preg_replace( '/;.*$/', '', $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
			$languages = str_replace( ' ', '', $languages );
			return apply_filters( 'redirection_request_accept_language', explode( ',', $languages ) );
		}

		return [];
	}
}
