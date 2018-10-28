<?php

class Custom_Match extends Red_Match {
	use FromNotFrom_Match;

	public $filter;

	public function name() {
		return __( 'URL and custom filter', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = array(
			'filter' => isset( $details['filter'] ) ? $this->sanitize_filter( $details['filter'] ) : '',
		);

		return $this->save_data( $details, $no_target_url, $data );
	}

	public function sanitize_filter( $name ) {
		$name = preg_replace( '/[^A-Za-z0-9\-_]/', '', $name );

		return trim( $name );
	}

	function get_target( $url, $matched_url, $regex ) {
		$target = false;
		$matched = apply_filters( $this->filter, false, $url );
		$target = $this->get_matched_target( $matched );

		if ( $regex && $target ) {
			return $this->get_target_regex_url( $matched_url, $target, $url );
		}

		return $target;
	}

	public function get_data() {
		return array_merge( array(
			'filter' => $this->filter,
		), $this->get_from_data() );
	}

	public function load( $values ) {
		$values = $this->load_data( $values );
		$this->filter = $values['filter'];
	}
}
