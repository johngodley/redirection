<?php

class Login_Match extends Red_Match {
	public $user_agent = '';

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

	function initialize( $url ) {
		$this->url = array( $url, '' );
	}

	function get_target( $url, $matched_url, $regex ) {
		if ( is_user_logged_in() === false ) {
			$target = $this->url_loggedout;
		} else {
			$target = $this->url_loggedin;
		}

		if ( $regex ) {
			$target = preg_replace( '@'.str_replace( '@', '\\@', $matched_url ).'@', $target, $url );
		}

		return $target;
	}
}
