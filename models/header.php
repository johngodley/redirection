<?php

/**
 * Manages HTTP headers for site-wide and redirect-specific use
 *
 * @phpstan-type HeaderSettingChoice array{
 *     label: string,
 *     value: string
 * }
 * @phpstan-type HeaderSettings array<string, string|array<HeaderSettingChoice>>
 * @phpstan-type HeaderData array{
 *     type: string,
 *     headerName: string,
 *     headerValue: string,
 *     location: string,
 *     headerSettings: HeaderSettings
 * }
 * @phpstan-type RawHeaderInput array{
 *     headerName?: string,
 *     type?: string,
 *     headerValue?: string,
 *     location?: string,
 *     headerSettings?: array<string, mixed>
 * }
 */
class Red_Http_Headers {
	/**
	 * Normalized headers
	 *
	 * @var array<HeaderData>
	 */
	private $headers = [];

	/**
	 * Constructor
	 *
	 * @param array<RawHeaderInput> $options Raw header configuration.
	 */
	public function __construct( $options = [] ) {
		// @phpstan-ignore function.alreadyNarrowedType
		if ( is_array( $options ) ) {
			$this->headers = array_filter( array_map( [ $this, 'normalize' ], $options ) );
		}
	}

	/**
	 * Normalize a raw header input
	 *
	 * @param RawHeaderInput $header Raw header data.
	 * @return HeaderData|null Normalized header or null if invalid.
	 */
	private function normalize( $header ) {
		$location = 'site';
		if ( isset( $header['location'] ) && $header['location'] === 'redirect' ) {
			$location = 'redirect';
		}

		$name = $this->sanitize( isset( $header['headerName'] ) ? sanitize_text_field( $header['headerName'] ) : '' );
		$type = $this->sanitize( isset( $header['type'] ) ? sanitize_text_field( $header['type'] ) : '' );
		$value = $this->sanitize( isset( $header['headerValue'] ) ? sanitize_text_field( $header['headerValue'] ) : '' );
		$settings = [];

		// @phpstan-ignore booleanAnd.rightAlwaysTrue
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

	/**
	 * Get headers as JSON
	 *
	 * @return array<HeaderData>
	 */
	public function get_json() {
		return $this->headers;
	}

	/**
	 * Convert a string to Dash-Case format
	 *
	 * @param string $name Input string.
	 * @return string Dash-Case formatted string.
	 */
	private function dash_case( $name ) {
		$name = (string) preg_replace( '/[^A-Za-z0-9]/', ' ', $name );
		$name = (string) preg_replace( '/\s{2,}/', ' ', $name );
		$name = trim( $name, ' ' );
		$name = ucwords( $name );
		$name = str_replace( ' ', '-', $name );

		return $name;
	}

	/**
	 * Remove duplicate headers by name
	 *
	 * @param array<HeaderData> $headers Headers to deduplicate.
	 * @return array<HeaderData>
	 */
	private function remove_dupes( $headers ) {
		$new_headers = [];

		foreach ( $headers as $header ) {
			$new_headers[ $header['headerName'] ] = $header;
		}

		return array_values( $new_headers );
	}

	/**
	 * Get headers for site-wide application
	 *
	 * @return array<HeaderData>
	 */
	public function get_site_headers() {
		$headers = array_values( $this->remove_dupes( array_filter( $this->headers, [ $this, 'is_site_header' ] ) ) );

		return apply_filters( 'redirection_headers_site', $headers );
	}

	/**
	 * Get headers for redirects (combines site and redirect-specific headers)
	 *
	 * @return array<HeaderData>
	 */
	public function get_redirect_headers() {
		// Site ones first, then redirect - redirect will override any site ones
		$headers = $this->get_site_headers();
		$headers = array_merge( $headers, array_values( array_filter( $this->headers, [ $this, 'is_redirect_header' ] ) ) );
		$headers = array_values( $this->remove_dupes( $headers ) );

		return apply_filters( 'redirection_headers_redirect', $headers );
	}

	/**
	 * Check if header is a site header
	 *
	 * @param HeaderData $header Header to check.
	 * @return bool
	 */
	private function is_site_header( $header ) {
		return $header['location'] === 'site';
	}

	/**
	 * Check if header is a redirect header
	 *
	 * @param HeaderData $header Header to check.
	 * @return bool
	 */
	private function is_redirect_header( $header ) {
		return $header['location'] === 'redirect';
	}

	/**
	 * Apply headers to the current request
	 *
	 * @param array<HeaderData> $headers Headers to apply.
	 * @return void
	 */
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
	 * Sanitize a string for use in headers
	 *
	 * @param string|array<mixed> $text Text to sanitize.
	 * @return string Sanitized text.
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
		// @phpstan-ignore function.alreadyNarrowedType
		if ( function_exists( 'iconv' ) && is_string( $text ) ) {
			$converted = @iconv( 'UTF-8', 'UTF-8//IGNORE', $text );
			if ( $converted !== false ) {
				$text = $converted;
			}
		}

		return $text;
	}
}
