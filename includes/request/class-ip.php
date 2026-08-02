<?php

namespace Redirection\Request;

/**
 * IP address handler for validating and normalizing IP addresses
 */
class Ip {
	/**
	 * Validated and normalized IP address
	 *
	 * @var string
	 */
	private $ip = '';

	/**
	 * Constructor. Validates and normalizes an IP address
	 *
	 * @param string $ip IP address to validate (may be comma-separated list; the last value is used, as it is the one appended by the nearest, trusted, proxy - earlier values are client-controlled).
	 */
	public function __construct( $ip = '' ) {
		$ip = sanitize_text_field( $ip );
		$ip = explode( ',', $ip );
		$ip = array_pop( $ip );
		$ip = filter_var( $ip, FILTER_VALIDATE_IP );
		if ( $ip === false ) {
			return;
		}

		// Convert to binary
		// phpcs:ignore
		$ip = @inet_pton( trim( $ip ) );
		if ( $ip !== false ) {
			// phpcs:ignore
			$ip = @inet_ntop( $ip );  // Convert back to string;
			if ( $ip === false ) {
				return;
			}

			$this->ip = $ip;
		}
	}

	/**
	 * Get the validated IP address
	 *
	 * @return string Validated IP address, or empty string if invalid.
	 */
	public function get() {
		return $this->ip;
	}
}
