<?php

class Language_Match extends Red_Match {
	use FromNotFrom_Match;

	public $language;

	public function name() {
		return __( 'URL and language', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = array( 'language' => isset( $details['language'] ) ? $this->sanitize_language( $details['language'] ) : '' );

		return $this->save_data( $details, $no_target_url, $data );
	}

	private function sanitize_language( $language ) {
		$parts = explode( ',', str_replace( ' ', '', $language ) );
		return implode( ',', $parts );
	}

	public function is_match( $url ) {
		$matches = explode( ',', $this->language );
		$requested = Redirection_Request::get_accept_language();

		foreach ( $matches as $match ) {
			if ( in_array( $match, $requested, true ) ) {
				return true;
			}
		}

		return false;
	}

	public function get_data() {
		return array_merge( array(
			'language' => $this->language,
		), $this->get_from_data() );
	}

	public function load( $values ) {
		$values = $this->load_data( $values );
		$this->language = isset( $values['language'] ) ? $values['language'] : '';
	}
}
