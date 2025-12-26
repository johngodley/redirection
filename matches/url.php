<?php

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
 * @phpstan-extends Red_Match<UrlMap, string>
 */
class URL_Match extends Red_Match {
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
	 * @param UrlMap $details
	 * @return string|null
	 */
	public function save( array $details, $no_target_url = false ) {
		$data = isset( $details['url'] ) ? $details['url'] : '';

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

	public function get_target_url( $original_url, $matched_url, Red_Source_Flags $flag, $is_matched ) {
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
			$this->url = isset( $values['url'] ) ? $values['url'] : '';
			return;
		}

		$this->url = $values;
	}
}
