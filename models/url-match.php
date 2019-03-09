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
		// Remove query params
		$url = new Red_Url_Path( $this->url );
		$path = $url->get_without_trailing_slash();

		// Lowercase everything
		$path = strtolower( $path );

		return $path ? $path : '/';
	}
}
