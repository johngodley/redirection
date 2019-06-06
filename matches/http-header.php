<?php

class Header_Match extends Red_Match {
	use FromNotFrom_Match;

	public $name;
	public $value;
	public $regex;

	public function name() {
		return __( 'URL and HTTP header', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = array(
			'regex' => isset( $details['regex'] ) && $details['regex'] ? true : false,
			'name'  => isset( $details['name'] ) ? $this->sanitize_name( $details['name'] ) : '',
			'value' => isset( $details['value'] ) ? $this->sanitize_value( $details['value'] ) : '',
		);

		return $this->save_data( $details, $no_target_url, $data );
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

	public function is_match( $url ) {
		if ( $this->regex ) {
			$regex = new Red_Regex( $this->value, true );
			return $regex->is_match( Redirection_Request::get_header( $this->name ) );
		}

		return Redirection_Request::get_header( $this->name ) === $this->value;
	}

	public function get_data() {
		return array_merge( array(
			'regex' => $this->regex,
			'name' => $this->name,
			'value' => $this->value,
		), $this->get_from_data() );
	}

	public function load( $values ) {
		$values = $this->load_data( $values );
		$this->regex = isset( $values['regex'] ) ? $values['regex'] : false;
		$this->name = isset( $values['name'] ) ? $values['name'] : '';
		$this->value = isset( $values['value'] ) ? $values['value'] : '';
	}
}
