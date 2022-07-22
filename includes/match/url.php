<?php

/**
 * Match the URL only.
 */
class URL_Match extends Red_Match {
	/**
	 * URL
	 *
	 * @var String
	 */
	public $url = '';

	public function name() {
		return __( 'URL only', 'redirection' );
	}

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

	public function get_data() {
		if ( $this->url ) {
			return [
				'url' => $this->url,
			];
		}

		return null;
	}

	public function load( $values ) {
		$this->url = $values;
	}
}
