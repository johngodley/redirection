<?php

/**
 * Match the referrer
 */
class Referrer_Match extends Red_Match {
	use FromNotFrom_Match;

	/**
	 * Referrer
	 *
	 * @var String
	 */
	public $referrer = '';

	/**
	 * Regex match?
	 *
	 * @var boolean
	 */
	public $regex = false;

	public function name() {
		return __( 'URL and referrer', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = array(
			'regex'    => isset( $details['regex'] ) && $details['regex'] ? true : false,
			'referrer' => isset( $details['referrer'] ) ? $this->sanitize_referrer( $details['referrer'] ) : '',
		);

		return $this->save_data( $details, $no_target_url, $data );
	}

	public function sanitize_referrer( $agent ) {
		return $this->sanitize_url( $agent );
	}

	public function is_match( $url ) {
		if ( $this->regex ) {
			$regex = new Red_Regex( $this->referrer, true );
			return $regex->is_match( Redirection_Request::get_referrer() );
		}

		return Redirection_Request::get_referrer() === $this->referrer;
	}

	public function get_data() {
		return array_merge( array(
			'regex' => $this->regex,
			'referrer' => $this->referrer,
		), $this->get_from_data() );
	}

	/**
	 * Load the match data into this instance.
	 *
	 * @param string $values Match values, as read from the database (plain text or serialized PHP).
	 * @return void
	 */
	public function load( $values ) {
		$values = $this->load_data( $values );
		$this->regex = isset( $values['regex'] ) ? $values['regex'] : false;
		$this->referrer = isset( $values['referrer'] ) ? $values['referrer'] : '';
	}
}
