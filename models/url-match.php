<?php

class Red_Url_Match {
	private $url;

	public function __construct( $url ) {
		$this->url = $url;
	}

	/**
	 * Get the plain 'matched' URL:
	 *
	 * - Lowercase
	 * - No trailing slashes
	 *
	 * @return string URL
	 */
	public function get_url() {
		// Remove query params, and decode any encoded characters
		$url = new Red_Url_Path( $this->url );
		$path = $url->get_without_trailing_slash();

		// URL encode
		$decode = [
			'/',
			':',
			'[',
			']',
			'@',
			'~',
			',',
			'(',
			')',
			';',
		];

		// URL encode everything - this converts any i10n to the proper encoding
		$path = rawurlencode( $path );

		// We also converted things we dont want encoding, such as a /. Change these back
		foreach ( $decode as $char ) {
			$path = str_replace( rawurlencode( $char ), $char, $path );
		}

		// Lowercase everything
		$path = Red_Url_Path::to_lower( $path );

		return $path ? $path : '/';
	}
}
