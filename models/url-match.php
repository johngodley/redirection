<?php

class Red_Url_Match {
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
		// Remove query params
		$path = wp_parse_url( $this->url, PHP_URL_PATH );

		// Lowercase everything
		$path = strtolower( $path );

		// Trim trailing slash
		$path = rtrim( $path, '/' );

		return $path ? $path : '/';
	}
}
