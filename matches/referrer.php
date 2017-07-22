<?php

class Referrer_Match extends Red_Match {
	public $referrer;
	public $regex;

	function name() {
		return __( 'URL and referrer', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = array(
			'regex'       => isset( $details['action_data_regex'] ) && $details['action_data_regex'] === 'true' ? true : false,
			'referrer'    => isset( $details['action_data_referrer'] ) ? $this->sanitize_referrer( $details['action_data_referrer'] ) : '',
		);

		if ( $no_target_url === false ) {
			$data['url_from'] = isset( $details['action_data_url_from'] ) ? $this->sanitize_url( $details['action_data_url_from'] ) : '';
			$data['url_notfrom'] = isset( $details['action_data_url_notfrom'] ) ? $this->sanitize_url( $details['action_data_url_notfrom'] ) : '';
		}

		return $data;
	}

	public function sanitize_referrer( $agent ) {
		return $this->sanitize_url( $agent );
	}

	function initialize( $url ) {
		$this->url = array( $url, '' );
	}

	function get_target( $url, $matched_url, $regex ) {
		$target = false;

		// Check if referrer matches
		if ( ( $this->regex === false && Redirection_Request::get_referrer() === $this->referrer ) || ( $this->regex === true && preg_match( '@'.str_replace( '@', '\\@', $this->referrer ).'@', Redirection_Request::get_referrer(), $matches ) ) ) {
			$target = $this->url_from;

			if ( $regex ) {
				$target = preg_replace( '@'.str_replace( '@', '\\@', $matched_url ).'@', $target, $url );
			}
		} elseif ( $this->url_notfrom !== '' ) {
			$target = $this->url_notfrom;
		}
		return $target;
	}
}
