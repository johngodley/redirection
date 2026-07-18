<?php

namespace Redirection\Compare;

/**
 * @phpstan-type UrlMap array{
 *    url?: string
 * }
 * @phpstan-type UrlData array{
 *    url: string
 * }
 *
 * Match the URL only.
 *
 * @phpstan-extends Compare<UrlMap, string>
 */
class Url extends Compare {
	/**
	 * URL
	 *
	 * @var string
	 */
	public $url = '';

	public function name() {
		return __( 'URL only', 'redirection' );
	}

	/**
	 * @param array $details Match details.
	 * @param bool  $no_target_url Whether the action has a target URL.
	 * @phpstan-param UrlMap $details
	 * @return string|null
	 */
	public function save( array $details, $no_target_url = false ) {
		$data = $details['url'] ?? '';

		if ( strlen( $data ) === 0 ) {
			$data = '/';
		}

		if ( $no_target_url ) {
			return null;
		}

		return $this->sanitize_url( $data );
	}

	public function is_match( $url ) {
		return true;
	}

	public function get_target_url( $original_url, $matched_url, \Redirection\Url\SourceFlags $flag, $is_matched ) {
		$target = $this->url;

		if ( $flag->is_regex() ) {
			$target = $this->get_target_regex_url( $matched_url, $target, $original_url, $flag );
		}

		return $target;
	}

	/**
	 * @return UrlData|null
	 */
	public function get_data() {
		if ( $this->url !== '' ) {
			return [
				'url' => $this->url,
			];
		}

		return null;
	}

	/**
	 * Load the match data into this instance.
	 *
	 * @param string|UrlMap $values Match values, as read from the database (plain text, serialized PHP, or parsed array).
	 * @return void
	 */
	public function load( $values ) {
		if ( is_array( $values ) ) {
			$this->url = $values['url'] ?? '';
			return;
		}

		$this->url = $values;
	}
}
