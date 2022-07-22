<?php

/**
 * Check whether the user is logged in or out
 */
class Login_Match extends Red_Match {
	/**
	 * Target URL when logged in.
	 *
	 * @var String
	 */
	public $logged_in = '';

	/**
	 * Target URL when logged out.
	 *
	 * @var String
	 */
	public $logged_out = '';

	public function name() {
		return __( 'URL and login status', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		if ( $no_target_url ) {
			return null;
		}

		return [
			'logged_in' => isset( $details['logged_in'] ) ? $this->sanitize_url( $details['logged_in'] ) : '',
			'logged_out' => isset( $details['logged_out'] ) ? $this->sanitize_url( $details['logged_out'] ) : '',
		];
	}

	public function is_match( $url ) {
		return is_user_logged_in();
	}

	public function get_target_url( $requested_url, $source_url, Red_Source_Flags $flags, $match ) {
		$target = false;

		if ( $match && $this->logged_in !== '' ) {
			$target = $this->logged_in;
		} elseif ( ! $match && $this->logged_out !== '' ) {
			$target = $this->logged_out;
		}

		if ( $flags->is_regex() && $target ) {
			$target = $this->get_target_regex_url( $source_url, $target, $requested_url, $flags );
		}

		return $target;
	}

	public function get_data() {
		return [
			'logged_in' => $this->logged_in,
			'logged_out' => $this->logged_out,
		];
	}

	/**
	 * Load the match data into this instance.
	 *
	 * @param String $values Match values, as read from the database (plain text or serialized PHP).
	 * @return void
	 */
	public function load( $values ) {
		$values = unserialize( $values );
		$this->logged_in = isset( $values['logged_in'] ) ? $values['logged_in'] : '';
		$this->logged_out = isset( $values['logged_out'] ) ? $values['logged_out'] : '';
	}
}
