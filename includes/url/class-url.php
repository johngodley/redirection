<?php

namespace Redirection\Url;

use Redirection\Core\Regex;

class Url {
	/**
	 * URL
	 *
	 * @var string
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
	 * @param string      $requested_url Target URL.
	 * @param SourceFlags $flags Match flags.
	 * @return boolean
	 */
	public function is_match( $requested_url, SourceFlags $flags ) {
		if ( $flags->is_regex() ) {
			$regex = new Regex( $this->url, $flags->is_ignore_case() );

			return $regex->is_match( $requested_url );
		}

		$path = new Path( $this->url );
		$query = new Query( $this->url, $flags );

		return $path->is_match( $requested_url, $flags ) && $query->is_match( $requested_url, $flags );
	}
}
