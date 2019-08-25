<?php

class URL_Match extends Red_Match {
	public $url = false;

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

	public function get_target_url( $requested_url, $source_url, Red_Source_Flags $flags, $matched ) {
		$target = $this->url;

		if ( $flags->is_regex() ) {
			$target = $this->get_target_regex_url( $source_url, $target, $requested_url, $flags );
		}

		return $target;
	}

	public function get_data() {
		if ( $this->url ) {
			return array(
				'url' => $this->url,
			);
		}

		return '';
	}

	public function load( $values ) {
		$this->url = $values;
	}
}
