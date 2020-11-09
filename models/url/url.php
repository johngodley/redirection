<?php

require_once __DIR__ . '/url-query.php';
require_once __DIR__ . '/url-path.php';
require_once __DIR__ . '/url-match.php';
require_once __DIR__ . '/url-flags.php';
require_once __DIR__ . '/url-request.php';
require_once __DIR__ . '/url-transform.php';

class Red_Url {
	/**
	 * URL
	 *
	 * @var String
	 */
	private $url;

	/**
	 * Constructor
	 *
	 * @param string $url URL.
	 */
	public function __construct( $url = '' ) {
		$this->url = $url;
		$this->url = str_replace( ' ', '%20', $this->url );  // deprecated
	}

	/**
	 * Get the raw URL
	 *
	 * @return string URL
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * Match a target URL against the current URL, using any match flags
	 *
	 * @param string           $requested_url Target URL.
	 * @param Red_Source_Flags $flags Match flags.
	 * @return boolean
	 */
	public function is_match( $requested_url, Red_Source_Flags $flags ) {
		if ( $flags->is_regex() ) {
			$regex = new Red_Regex( $this->url, $flags->is_ignore_case() );

			return $regex->is_match( $requested_url );
		}

		$path = new Red_Url_Path( $this->url );
		$query = new Red_Url_Query( $this->url, $flags );

		return $path->is_match( $requested_url, $flags ) && $query->is_match( $requested_url, $flags );
	}
}
