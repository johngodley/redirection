<?php

class Referrer_Match extends Red_Match {
	public $referrer;
	public $regex;
	public $url_from;
	public $url_notfrom;

	function name() {
		return __( 'URL and referrer', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = array(
			'regex'    => isset( $details['regex'] ) && $details['regex'] ? true : false,
			'referrer' => isset( $details['referrer'] ) ? $this->sanitize_referrer( $details['referrer'] ) : '',
		);

		if ( $no_target_url === false ) {
			$data['url_from'] = isset( $details['url_from'] ) ? $this->sanitize_url( $details['url_from'] ) : '';
			$data['url_notfrom'] = isset( $details['url_notfrom'] ) ? $this->sanitize_url( $details['url_notfrom'] ) : '';
		}

		return $data;
	}

	public function sanitize_referrer( $agent ) {
		return $this->sanitize_url( $agent );
	}

	function get_target( $url, $matched_url, $regex ) {
		$target = false;
		$matched = Redirection_Request::get_referrer() === $this->referrer;

		if ( $this->regex ) {
			$matched = preg_match( '@' . str_replace( '@', '\\@', $this->referrer ) . '@', Redirection_Request::get_referrer(), $matches ) > 0;
		}

		// Check if referrer matches
		if ( $matched && $this->url_from !== '' ) {
			$target = $this->url_from;
		} elseif ( ! $matched && $this->url_notfrom !== '' ) {
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
			'referrer' => $this->referrer,
		);
	}

	public function load( $values ) {
		$values = unserialize( $values );
		$this->url_from = $values['url_from'];
		$this->url_notfrom = $values['url_notfrom'];
		$this->regex = $values['regex'];
		$this->referrer = $values['referrer'];
	}
}
