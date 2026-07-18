<?php

namespace Redirection\Compare;

use Redirection\Request\Request;

/**
 * @phpstan-type LanguageMap array{
 *    language?: string,
 *    url_from?: string,
 *    url_notfrom?: string
 * }
 * @phpstan-type LanguageResult array{
 *    language: string,
 *    url_from?: string,
 *    url_notfrom?: string
 * }
 * @phpstan-type LanguageData array{
 *    language: string,
 *    url_from: string,
 *    url_notfrom: string
 * }
 *
 * Check the client language
 *
 * @phpstan-extends Compare<LanguageMap, LanguageResult>
 */
class Language extends Compare {
	use FromNotFrom;

	/**
	 * Language to check.
	 *
	 * @var string
	 */
	public $language = '';

	public function name() {
		return __( 'URL and language', 'redirection' );
	}

	/**
	 * @param array $details Match details.
	 * @param bool  $no_target_url Whether the action has a target URL.
	 * @phpstan-param LanguageMap $details
	 * @return LanguageResult
	 */
	public function save( array $details, $no_target_url = false ) {
		$data = [ 'language' => isset( $details['language'] ) ? $this->sanitize_language( $details['language'] ) : '' ];

		$result = $this->save_data( $details, $no_target_url, $data );
		return $result; // @phpstan-ignore-line
	}

	/**
	 * Sanitize the language value to a CSV string
	 *
	 * @param string $language User supplied language strings.
	 * @return string
	 */
	private function sanitize_language( $language ) {
		$parts = explode( ',', str_replace( ' ', '', sanitize_text_field( $language ) ) );
		return implode( ',', $parts );
	}

	public function is_match( $url ) {
		$matches = explode( ',', $this->language );
		$requested = Request::get_accept_language();

		foreach ( $matches as $match ) {
			if ( in_array( $match, $requested, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return LanguageData
	 */
	public function get_data() {
		return array_merge(
			[
				'language' => $this->language,
			],
			$this->get_from_data()
		);
	}

	/**
	 * Load the match data into this instance.
	 *
	 * @param string|LanguageMap $values Match values, as read from the database (plain text, serialized PHP, or parsed array).
	 * @return void
	 */
	public function load( $values ) {
		$data = $this->load_data( $values );
		$this->language = $data['language'] ?? ''; // @phpstan-ignore-line
	}
}
