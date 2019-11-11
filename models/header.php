<?php

class Red_Http_Headers {
	private $headers = [];

	public function __construct( $options ) {
		if ( is_array( $options ) ) {
			$this->headers = array_filter( array_map( [ $this, 'normalize' ], $options ) );
		}
	}

	private function normalize( $header ) {
		$location = 'site';
		if ( $header['location'] === 'redirect' ) {
			$location = 'redirect';
		}

		$name = $this->sanitize( isset( $header['headerName'] ) ? $header['headerName'] : '' );
		$value = $this->sanitize( isset( $header['headerValue'] ) ? $header['headerValue'] : '' );
		$settings = [];

		if ( isset( $header['headerSettings'] ) && is_array( $header['headerSettings'] ) ) {
			foreach ( $header['headerSettings'] as $key => $value ) {
				$settings[ $this->sanitize( $key ) ] = $this->sanitize( $value );
			}
		}

		if ( strlen( $name ) > 0 ) {
			$this->add_header( $this->dash_case( $name ), $value, $location, $settings );
		}

		return $header;
	}

	private function add_header( $name, $value, $location, $settings ) {
		$this->headers[] = [
			'headerName' => $name,
			'headerValue' => $value,
			'location' => $location,
			'headerSettings' => $settings,
		];
	}

	public function get_json() {
		return $this->headers;
	}

	private function dash_case( $name ) {
		$name = str_replace( ' ', '-', $name );
		$name = ucwords( $name );

		return $name;
	}

	public function run_site() {
		$this->run( array_filter( $this->headers, [ $this, 'is_site_header' ] ) );
	}

	public function run_redirect() {
		// Redirect headers first, then site ones
		$headers = array_filter( $this->headers, [ $this, 'is_redirect_header' ] );
		$headers = array_merge( $headers, array_filter( $this->headers, [ $this, 'is_site_header' ] ) );
		$this->run( $headers );
	}

	private function is_site_header( $header ) {
		return $header['location'] === 'site';
	}

	private function is_redirect_header( $header ) {
		return $header['location'] === 'redirect';
	}

	private function run( $headers ) {
		$done = [];

		foreach ( $headers as $header ) {
			if ( ! in_array( $header['headerName'], $done, true ) ) {
				header( sprintf( '%s: %s', $this->sanitize( $this->dash_case( $header['headerName'] ) ), $this->sanitize( $header['headerValue'] ) ) );
				$done[] = $header['headerName'];
			}
		}
	}

	private function sanitize( $text ) {
		// No new lines
		$text = preg_replace( "/[\r\n\t].*?$/s", '', $text );

		// Clean control codes
		$text = preg_replace( '/[^\PC\s]/u', '', $text );

		// Try and remove bad decoding
		if ( function_exists( 'iconv' ) ) {
			$converted = @iconv( 'UTF-8', 'UTF-8//IGNORE', $text );
			if ( $converted !== false ) {
				$text = $converted;
			}
		}

		return $text;
	}
}
