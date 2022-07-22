<?php

/**
 * Perform a check against the results of a custom filter
 */
class Custom_Match extends Red_Match {
	use FromNotFrom_Match;

	/**
	 * Filter name
	 *
	 * @var string
	 */
	public $filter = '';

	public function name() {
		return __( 'URL and custom filter', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = [
			'filter' => isset( $details['filter'] ) ? $this->sanitize_filter( $details['filter'] ) : '',
		];

		return $this->save_data( $details, $no_target_url, $data );
	}

	public function sanitize_filter( $name ) {
		$name = preg_replace( '/[^A-Za-z0-9\-_]/', '', $name );

		return trim( $name );
	}

	public function is_match( $url ) {
		return apply_filters( $this->filter, false, $url );
	}

	public function get_data() {
		return array_merge( [
			'filter' => $this->filter,
		], $this->get_from_data() );
	}

	public function load( $values ) {
		$values = $this->load_data( $values );
		$this->filter = isset( $values['filter'] ) ? $values['filter'] : '';
	}
}
