<?php

namespace Redirection\Match;

use Redirection\Site;

/**
 * Match the user agent
 */
class User_Agent extends Match {
	use FromNotFrom_Match;

	/**
	 * User agent.
	 *
	 * @var String
	 */
	public $agent = '';

	/**
	 * Is this a regex match?
	 *
	 * @var boolean
	 */
	public $regex = false;

	public function name() {
		return __( 'URL and user agent', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = array(
			'regex' => isset( $details['regex'] ) && $details['regex'] ? true : false,
			'agent' => isset( $details['agent'] ) ? $this->sanitize_agent( $details['agent'] ) : '',
		);

		return $this->save_data( $details, $no_target_url, $data );
	}

	private function sanitize_agent( $agent ) {
		return $this->sanitize_url( $agent );
	}

	public function is_match( $url ) {
		if ( $this->regex ) {
			$regex = new Site\Regex( $this->agent, true );
			return $regex->is_match( Site\Request::get_user_agent() );
		}

		return $this->agent === Site\Request::get_user_agent();
	}

	public function get_data() {
		return array_merge( array(
			'regex' => $this->regex,
			'agent' => $this->agent,
		), $this->get_from_data() );
	}

	/**
	 * Load the match data into this instance.
	 *
	 * @param String $values Match values, as read from the database (plain text or serialized PHP).
	 * @return void
	 */
	public function load( $values ) {
		$values = $this->load_data( $values );
		$this->regex = isset( $values['regex'] ) ? $values['regex'] : false;
		$this->agent = isset( $values['agent'] ) ? $values['agent'] : '';
	}
}
