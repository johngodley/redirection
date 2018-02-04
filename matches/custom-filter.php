<?php

class Custom_Match extends Red_Match {
	public $filter;
	public $url_from;
	public $url_notfrom;

	function name() {
		return __( 'URL and custom filter', 'redirection' );
	}

	public function save( array $details, $no_target_url = false ) {
		$data = array(
			'filter' => isset( $details['filter'] ) ? $this->sanitize_filter( $details['filter'] ) : '',
		);

		if ( $no_target_url === false ) {
			$data['url_from'] = isset( $details['url_from'] ) ? $this->sanitize_url( $details['url_from'] ) : '';
			$data['url_notfrom'] = isset( $details['url_notfrom'] ) ? $this->sanitize_url( $details['url_notfrom'] ) : '';
		}

		return $data;
	}

	public function sanitize_filter( $name ) {
		$name = preg_replace( '/[^A-Za-z0-9\-_]/', '', $name );

		return trim( $name );
	}

	function get_target( $url, $matched_url, $regex ) {
		$target = false;
		$matched = apply_filters( $this->filter, false, $url );

		// Check if referrer matches
		if ( $matched && $this->url_from !== '' ) {
			$target = $this->url_from;
		} elseif ( ! $matched && $this->url_notfrom !== '' ) {
			$target = $this->url_notfrom;
		}

		return $target;
	}

	public function get_data() {
		return array(
			'url_from' => $this->url_from,
			'url_notfrom' => $this->url_notfrom,
			'filter' => $this->filter,
		);
	}

	public function load( $values ) {
		$values = unserialize( $values );
		$this->url_from = $values['url_from'];
		$this->url_notfrom = $values['url_notfrom'];
		$this->filter = $values['filter'];
	}
}
