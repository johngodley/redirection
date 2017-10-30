<?php

class URL_Match extends Red_Match {
	public $url = false;

	function name () {
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

	function get_target( $url, $matched_url, $regex ) {
		$target = $this->url;
		if ( $regex ) {
			$target = $this->get_target_regex_url( $matched_url, $this->url, $url );
		}

		if ( $target === '' ) {
			return $matched_url;
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
