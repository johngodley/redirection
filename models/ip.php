<?php

class Redirection_IP {
	private $ip;

	public function __construct( $ip = '' ) {
		$this->ip = '';

		$ip = sanitize_text_field( $ip );
		$ip = explode( ',', $ip );
		$ip = array_shift( $ip );
		$ip = filter_var( $ip, FILTER_VALIDATE_IP );

		// Convert to binary
		// phpcs:ignore
		$ip = @inet_pton( trim( $ip ) );
		if ( $ip !== false ) {
			// phpcs:ignore
			$this->ip = @inet_ntop( $ip );  // Convert back to string
		}
	}

	public function get() {
		return $this->ip;
	}
}