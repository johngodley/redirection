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

	// Note this currently only supports IP4
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

		$ip = trim( $ip );
		$ip = filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';

		return apply_filters( 'redirection_request_ip', $ip );
	}
}
