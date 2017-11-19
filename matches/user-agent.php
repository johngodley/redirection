<?php

class Agent_Match extends Red_Match {
	public $agent;
	public $regex;
	public $url_from;
	public $url_notfrom;

	function name() {
		return __( 'URL and user agent', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = array(
			'regex' => isset( $details['regex'] ) && $details['regex'] ? true : false,
			'agent' => isset( $details['agent'] ) ? $this->sanitize_agent( $details['agent'] ) : '',
		);

		if ( $no_target_url === false ) {
			$data['url_from'] = isset( $details['url_from'] ) ? $this->sanitize_url( $details['url_from'] ) : '';
			$data['url_notfrom'] = isset( $details['url_notfrom'] ) ? $this->sanitize_url( $details['url_notfrom'] ) : '';
		}

		return $data;
	}

	private function sanitize_agent( $agent ) {
		return $this->sanitize_url( $agent );
	}

	function get_target( $url, $matched_url, $regex ) {
		// Check if referrer matches
		$matched = $this->agent === Redirection_Request::get_user_agent();
		if ( $this->regex ) {
			$matched = preg_match( '@'.str_replace( '@', '\\@', $this->agent ).'@i', Redirection_Request::get_user_agent() ) > 0;
		}

		$target = false;
		if ( $this->url_from !== '' && $matched ) {
			$target = $this->url_from;
		} elseif ( $this->url_notfrom !== '' && ! $matched ) {
			$target = $this->url_notfrom;
		}

		if ( $regex && $target ) {
			$target = $this->get_target_regex_url( $matched_url, $target, $url );
		}

		return $target;
	}

	public function get_data() {
		return array(
			'url_from' => $this->url_from,
			'url_notfrom' => $this->url_notfrom,
			'regex' => $this->regex,
			'agent' => $this->agent,
		);
	}

	public function load( $values ) {
		$values = unserialize( $values );
		$this->url_from = $values['url_from'];
		$this->url_notfrom = $values['url_notfrom'];
		$this->regex = $values['regex'];
		$this->agent = $values['agent'];
	}
}
