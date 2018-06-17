<?php

class Header_Match extends Red_Match {
	public $name;
	public $value;
	public $regex;
	public $url_from;
	public $url_notfrom;

	function name() {
		return __( 'URL and HTTP header', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = array(
			'regex' => isset( $details['regex'] ) && $details['regex'] ? true : false,
			'name'  => isset( $details['name'] ) ? $this->sanitize_name( $details['name'] ): '',
			'value' => isset( $details['value'] ) ? $this->sanitize_value( $details['value'] ) : '',
		);

		if ( $no_target_url === false ) {
			$data['url_from'] = isset( $details['url_from'] ) ? $this->sanitize_url( $details['url_from'] ) : '';
			$data['url_notfrom'] = isset( $details['url_notfrom'] ) ? $this->sanitize_url( $details['url_notfrom'] ) : '';
		}

		return $data;
	}

	public function sanitize_name( $name ) {
		$name = $this->sanitize_url( $name );
		$name = str_replace( ' ', '', $name );
		$name = preg_replace( '/[^A-Za-z0-9\-_]/', '', $name );

		return trim( trim( $name, ':' ) );
	}

	public function sanitize_value( $value ) {
		return $this->sanitize_url( $value );
	}

	function get_target( $url, $matched_url, $regex ) {
		$target = false;
		$matched = Redirection_Request::get_header( $this->name ) === $this->value;

		if ( $this->regex ) {
			$matched = preg_match( '@' . str_replace( '@', '\\@', $this->value ) . '@', Redirection_Request::get_header( $this->name ), $matches ) > 0;
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
			'name' => $this->name,
			'value' => $this->value,
		);
	}

	public function load( $values ) {
		$values = unserialize( $values );

		if ( isset( $values['url_from'] ) ) {
			$this->url_from = $values['url_from'];
		}

		if ( isset( $values['url_notfrom'] ) ) {
			$this->url_notfrom = $values['url_notfrom'];
		}

		$this->regex = $values['regex'];
		$this->name = $values['name'];
		$this->value = $values['value'];
	}
}
