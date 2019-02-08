<?php

class Red_Url_Path {
	public function __construct( $url ) {
		$this->path = $this->get_url_path( $url );
	}

	private function get_url_path( $url ) {
		$path = wp_parse_url( $url, PHP_URL_PATH );

		return $path ? $path : '/';
	}

	public function is_match( $url, Red_Source_Flags $flags ) {
		$source = $this->path;
		$target = $this->get_url_path( $url );

		if ( $flags->is_ignore_case() ) {
			// Case insensitive match
			$source = strtolower( $source );
			$target = strtolower( $target );
		}

		if ( $flags->is_ignore_trailing() ) {
			// Ignore trailing slashes
			$source = rtrim( $source, '/' );
			$target = rtrim( $target, '/' );
		}

		return $target === $source;
	}
}
