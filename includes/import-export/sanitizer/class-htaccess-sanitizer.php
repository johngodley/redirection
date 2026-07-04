<?php

namespace Redirection\ImportExport\Sanitizer;

/**
 * Sanitize data for .htaccess rules.
 */
class HtaccessSanitizer {
	/**
	 * @param string $url URL.
	 * @return string
	 */
	public function sanitize_regex( $url ) {
		$url = (string) preg_replace( "/[\r\n\t].*?$/s", '', $url );
		$url = (string) preg_replace( '/[^\PC\s]/u', '', $url );
		$url = str_replace( ' ', '\\s', $url );
		$url = str_replace( '%24', '$', $url );
		$url = ltrim( $url, '/' );
		$url = (string) preg_replace( '@^\^/@', '^', $url );

		return $url;
	}

	/**
	 * @param string $text Text.
	 * @return string
	 */
	public function sanitize_redirect( $text ) {
		$text = str_replace( [ "\r", "\n", "\t" ], '', $text );
		$text = (string) preg_replace( '/[^\PC\s]/u', '', $text );

		return str_replace( [ '<?', '>' ], '', $text );
	}
}
