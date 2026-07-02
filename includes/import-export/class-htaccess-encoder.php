<?php

namespace Redirection\ImportExport;

use Redirection\ImportExport\Sanitizer\HtaccessSanitizer;

/**
 * Encode data for .htaccess rules.
 */
class HtaccessEncoder {
	/**
	 * @var HtaccessSanitizer
	 */
	private $sanitizer;

	/**
	 * @param HtaccessSanitizer|null $sanitizer
	 */
	public function __construct( ?HtaccessSanitizer $sanitizer = null ) {
		$this->sanitizer = $sanitizer ? $sanitizer : new HtaccessSanitizer();
	}

	/**
	 * @param string $url From URL.
	 * @param bool $ignore_trailing Ignore trailing slashes.
	 * @return string
	 */
	public function encode_from( $url, $ignore_trailing ) {
		$url = $this->encode_path( $url );
		$url = ltrim( $url, '/' );

		if ( $ignore_trailing ) {
			$url = rtrim( $url, '/' ) . '/?';
		}

		return '^' . $url . '$';
	}

	/**
	 * @param string $url URL.
	 * @return string
	 */
	public function encode_target( $url ) {
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

		return $this->replace_encoding( rawurlencode( $url ), $allowed );
	}

	/**
	 * @param string $url URL.
	 * @return string
	 */
	public function encode_path( $url ) {
		$allowed = [
			'%2F' => '/',
			'%3F' => '?',
			'+' => '\\s',
			'.' => '\\.',
			'%20' => '\\s',
		];

		return $this->replace_encoding( rawurlencode( $url ), $allowed );
	}

	/**
	 * @param string $url URL.
	 * @return string
	 */
	public function encode_regex( $url ) {
		return $this->sanitizer->sanitize_regex( $url );
	}

	/**
	 * @param string $text Text.
	 * @return string
	 */
	public function sanitize_redirect( $text ) {
		return $this->sanitizer->sanitize_redirect( $text );
	}

	/**
	 * @param string $str Source string.
	 * @param array<string, string> $allowed Allowed encodings.
	 * @return string
	 */
	private function replace_encoding( $str, array $allowed ) {
		foreach ( $allowed as $before => $after ) {
			$str = str_replace( $before, $after, $str );
		}

		return $str;
	}
}
