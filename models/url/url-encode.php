<?php

class Red_Url_Encode {
	/**
	 * URL
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Is regex?
	 *
	 * @var boolean
	 */
	private $is_regex;

	/**
	 * Constructor
	 *
	 * @param string  $url      URL.
	 * @param boolean $is_regex Is Regex.
	 */
	public function __construct( $url, $is_regex = false ) {
		// Remove any newlines
		$url = preg_replace( "/[\r\n\t].*?$/s", '', $url );

		// Remove invalid characters
		$url = preg_replace( '/[^\PC\s]/u', '', $url );

		// Make sure spaces are quoted
		$url = str_replace( ' ', '%20', $url );
		$url = str_replace( '%24', '$', $url );

		$this->url = $url;
		$this->is_regex = $is_regex;
	}

	/**
	 * URL encode some things, but other things can be passed through
	 *
	 * @return string
	 */
	public function get_as_target() {
		$allowed = [
			'%2F' => '/',
			'%3F' => '?',
			'%3A' => ':',
			'%3D' => '=',
			'%26' => '&',
			'%25' => '%',
			'+' => '%20',
			'%24' => '$',
			'%23' => '#',
		];

		$url = rawurlencode( $this->url );
		$url = $this->replace_encoding( $url, $allowed );

		return $this->encode_regex( $url );
	}

	/**
	 * Encode a URL
	 *
	 * @return string
	 */
	public function get_as_source() {
		$allowed = [
			'%2F' => '/',
			'%3F' => '?',
			'+' => '%20',
			'.' => '\\.',
		];

		$url = $this->replace_encoding( rawurlencode( $this->url ), $allowed );
		return $this->encode_regex( $url );
	}


	/**
	 * Replace encoded characters in a URL
	 *
	 * @param string $str Source string.
	 * @param array  $allowed Allowed encodings.
	 * @return string
	 */
	private function replace_encoding( $str, $allowed ) {
		foreach ( $allowed as $before => $after ) {
			$str = str_replace( $before, $after, $str );
		}

		return $str;
	}

	/**
	 * Encode a regex URL
	 *
	 * @param string $url URL.
	 * @return string
	 */
	private function encode_regex( $url ) {
		if ( $this->is_regex ) {
			// No leading slash
			$url = ltrim( $url, '/' );

			// If pattern has a ^ at the start then ensure we don't have a slash immediatley after
			$url = preg_replace( '@^\^/@', '^', $url );

			$url = $this->replace_encoding( $url, [
				'%2A' => '*',
				'%3F' => '?',
				'%28' => '(',
				'%29' => ')',
				'%5B' => '[',
				'%5C' => ']',
				'%24' => '$',
				'%2B' => '+',
				'%7C' => '|',
				'\\.' => '.',
			] );
		}

		return $url;
	}
}