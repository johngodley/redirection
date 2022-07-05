<?php

namespace Redirection\Url;

/**
 * Get a URL suitable for matching in the database
 */
class Match {
	/**
	 * URL
	 *
	 * @var String
	 */
	private $url;

	/**
	 * Constructor
	 *
	 * @param String $url The URL to match.
	 */
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
		$url = new Path( $this->url );
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
		$path = Path::to_lower( $path );

		return $path ? $path : '/';
	}

	/**
	 * Get the URL with parameters re-ordered into alphabetical order
	 *
	 * @return String
	 */
	public function get_url_with_params() {
		$query = new Query( $this->url, new Source_Flags( [ Source_Flags::FLAG_CASE => true ] ) );

		return $query->get_url_with_query( $this->get_url() );
	}
}
