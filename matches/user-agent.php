<?php

class Agent_Match extends Red_Match {
	public $user_agent;

	function name() {
		return __( 'URL and user agent', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = array(
			'regex' => isset( $details['action_data_regex'] ) && $details['action_data_regex'] === 'true' ? true : false,
			'agent' => isset( $details['action_data_agent'] ) ? $this->sanitize_agent( $details['action_data_agent'] ) : '',
		);

		if ( $no_target_url === false ) {
			$data['url_from'] = isset( $details['action_data_url_from'] ) ? $this->sanitize_url( $details['action_data_url_from'] ) : '';
			$data['url_notfrom'] = isset( $details['action_data_url_notfrom'] ) ? $this->sanitize_url( $details['action_data_url_notfrom'] ) : '';
		}

		return $data;
	}

	private function sanitize_agent( $agent ) {
		return $this->sanitize_url( $agent );
	}

	function initialize( $url ) {
		$this->url = array( $url, '' );
	}

	function get_target( $url, $matched_url, $regex ) {
		// Check if referrer matches
		if ( preg_match( '@'.str_replace( '@', '\\@', $this->user_agent ).'@i', $_SERVER['HTTP_USER_AGENT'], $matches ) > 0 ) {
			return preg_replace( '@'.str_replace( '@', '\\@', $matched_url ).'@', $this->url_from, $url );
		} elseif ( $this->url_notfrom !== '' ) {
			return $this->url_notfrom;
		}

		return false;
	}
}
