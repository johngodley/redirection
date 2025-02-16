<?php

class Red_Http_Headers {
	private $headers = [];

	public function __construct( $options = [] ) {
		if ( is_array( $options ) ) {
			$this->headers = array_filter( array_map( [ $this, 'normalize' ], $options ) );
		}
	}

	private function normalize( $header ) {
		$location = 'site';
		if ( isset( $header['location'] ) && $header['location'] === 'redirect' ) {
			$location = 'redirect';
		}

		$name = $this->sanitize( isset( $header['headerName'] ) ? sanitize_text_field( $header['headerName'] ) : '' );
		$type = $this->sanitize( isset( $header['type'] ) ? sanitize_text_field( $header['type'] ) : '' );
		$value = $this->sanitize( isset( $header['headerValue'] ) ? sanitize_text_field( $header['headerValue'] ) : '' );
		$settings = [];

		if ( isset( $header['headerSettings'] ) && is_array( $header['headerSettings'] ) ) {
			foreach ( $header['headerSettings'] as $key => $setting_value ) {
				if ( is_array( $setting_value ) ) {
					if ( isset( $setting_value['value'] ) ) {
						$settings[ $this->sanitize( sanitize_text_field( $key ) ) ] = $this->sanitize( $setting_value['value'] );
					} elseif ( isset( $setting_value['choices'] ) ) {
						$settings[ $this->sanitize( sanitize_text_field( $key ) ) ] = array_map(
							function ( $choice ) {
								return [
									'label' => $this->sanitize( isset( $choice['label'] ) ? $choice['label'] : '' ),
									'value' => $this->sanitize( isset( $choice['value'] ) ? $choice['value'] : '' ),
								];
							},
							$setting_value['choices']
						);
					}
				} else {
					$settings[ $this->sanitize( sanitize_text_field( $key ) ) ] = $this->sanitize( $setting_value );
				}
			}
		}

		if ( strlen( $name ) > 0 && strlen( $type ) > 0 ) {
			return [
				'type' => $this->dash_case( $type ),
				'headerName' => $this->dash_case( $name ),
				'headerValue' => $value,
				'location' => $location,
				'headerSettings' => $settings,
			];
		}

		return null;
	}

	public function get_json() {
		return $this->headers;
	}

	private function dash_case( $name ) {
		$name = preg_replace( '/[^A-Za-z0-9]/', ' ', $name );
		$name = preg_replace( '/\s{2,}/', ' ', $name );
		$name = trim( $name, ' ' );
		$name = ucwords( $name );
		$name = str_replace( ' ', '-', $name );

		return $name;
	}

	private function remove_dupes( $headers ) {
		$new_headers = [];

		foreach ( $headers as $header ) {
			$new_headers[ $header['headerName'] ] = $header;
		}

		return array_values( $new_headers );
	}

	public function get_site_headers() {
		$headers = array_values( $this->remove_dupes( array_filter( $this->headers, [ $this, 'is_site_header' ] ) ) );

		return apply_filters( 'redirection_headers_site', $headers );
	}

	public function get_redirect_headers() {
		// Site ones first, then redirect - redirect will override any site ones
		$headers = $this->get_site_headers();
		$headers = array_merge( $headers, array_values( array_filter( $this->headers, [ $this, 'is_redirect_header' ] ) ) );
		$headers = array_values( $this->remove_dupes( $headers ) );

		return apply_filters( 'redirection_headers_redirect', $headers );
	}

	private function is_site_header( $header ) {
		return $header['location'] === 'site';
	}

	private function is_redirect_header( $header ) {
		return $header['location'] === 'redirect';
	}

	public function run( $headers ) {
		$done = [];

		foreach ( $headers as $header ) {
			if ( ! in_array( $header['headerName'], $done, true ) ) {
				$name = $this->sanitize( $this->dash_case( $header['headerName'] ) );
				$value = $this->sanitize( $header['headerValue'] );

				// Trigger some other action
				do_action( 'redirection_header', $name, $value );

				header( sprintf( '%s: %s', $name, $value ) );
				$done[] = $header['headerName'];
			}
		}
	}

	/**
	 * Sanitize that string
	 *
	 * @param string $text
	 * @return string
	 */
	private function sanitize( $text ) {
		if ( is_array( $text ) ) {
			return '';
		}

		// No new lines
		$text = (string) preg_replace( "/[\r\n\t].*?$/s", '', $text );

		// Clean control codes
		$text = (string) preg_replace( '/[^\PC\s]/u', '', $text );

		// Try and remove bad decoding
		if ( function_exists( 'iconv' ) && is_string( $text ) ) {
			$converted = @iconv( 'UTF-8', 'UTF-8//IGNORE', $text );
			if ( $converted !== false ) {
				$text = $converted;
			}
		}

		return $text;
	}
}
