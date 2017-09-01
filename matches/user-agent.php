<?php

class Agent_Match extends Red_Match {
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
}
