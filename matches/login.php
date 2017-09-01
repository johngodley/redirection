<?php

class Login_Match extends Red_Match {
	function name() {
		return __( 'URL and login status', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		if ( $no_target_url ) {
			return null;
		}

		return array(
			'logged_in' => isset( $details['action_data_logged_in'] ) ? $this->sanitize_url( $details['action_data_logged_in'] ) : '',
			'logged_out' => isset( $details['action_data_logged_out'] ) ? $this->sanitize_url( $details['action_data_logged_out'] ) : '',
		);
	}

	function get_target( $url, $matched_url, $regex ) {
		$target = false;

		if ( is_user_logged_in() && $this->logged_in !== '' ) {
			$target = $this->logged_in;
		} else if ( ! is_user_logged_in() && $this->logged_out !== '' ) {
			$target = $this->logged_out;
		}

		if ( $regex && $target ) {
			$target = $this->get_target_regex_url( $matched_url, $target, $url );
		}

		return $target;
	}
}
