<?php

/**
 * Check the client language
 */
class Language_Match extends Red_Match {
	use FromNotFrom_Match;

	/**
	 * Language to check.
	 *
	 * @var String
	 */
	public $language = '';

	public function name() {
		return __( 'URL and language', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = array( 'language' => isset( $details['language'] ) ? $this->sanitize_language( $details['language'] ) : '' );

		return $this->save_data( $details, $no_target_url, $data );
	}

	/**
	 * Sanitize the language value to a CSV string
	 *
	 * @param String $language User supplied language strings.
	 * @return String
	 */
	private function sanitize_language( $language ) {
		$parts = explode( ',', str_replace( ' ', '', sanitize_text_field( $language ) ) );
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

	/**
	 * Load the match data into this instance.
	 *
	 * @param String $values Match values, as read from the database (plain text or serialized PHP).
	 * @return void
	 */
	public function load( $values ) {
		$values = $this->load_data( $values );
		$this->language = isset( $values['language'] ) ? $values['language'] : '';
	}
}
