<?php

class URL_Match extends Red_Match {
	public $url;

	function name () {
		return __( 'URL only', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		if ( ! isset( $details['action_data'] ) || strlen( $details['action_data'] ) === 0 ) {
			$details['action_data'] = '/';
		}

		if ( $no_target_url ) {
			return null;
		}

		return $this->sanitize_url( $details['action_data'] );
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
		return $this->url;
	}

	public function load( $values ) {
		$this->url = $values;
	}
}
