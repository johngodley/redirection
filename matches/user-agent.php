<?php

class Agent_Match extends Red_Match {
	use FromNotFrom_Match;

	public $agent;
	public $regex;

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
			$regex = new Red_Regex( $this->agent, true );
			return $regex->is_match( Redirection_Request::get_user_agent() );
		}

		return $this->agent === Redirection_Request::get_user_agent();
	}

	public function get_data() {
		return array_merge( array(
			'regex' => $this->regex,
			'agent' => $this->agent,
		), $this->get_from_data() );
	}

	public function load( $values ) {
		$values = $this->load_data( $values );
		$this->regex = isset( $values['regex'] ) ? $values['regex'] : false;
		$this->agent = isset( $values['agent'] ) ? $values['agent'] : '';
	}
}
