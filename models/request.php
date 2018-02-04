<?php

class Redirection_Request {
	public static function get_request_url() {
		$url = '';

		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$url = $_SERVER['REQUEST_URI'];
		}

		return apply_filters( 'redirection_request_url', $url );
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

	public static function get_ip() {
		$ip = '';

		if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
			$ip = array_shift( $ip );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		// Convert to binary
		$ip = @inet_pton( trim( $ip ) );
		if ( $ip !== false ) {
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
		$name = 'HTTP_'.strtoupper( $name );
		$name = str_replace( '-', '_', $name );

		if ( isset( $_SERVER[ $name ] ) ) {
			return apply_filters( 'redirection_request_header', $_SERVER[ $name ], $name );
		}

		return false;
	}
}
