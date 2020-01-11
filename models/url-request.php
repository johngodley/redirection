<?php

class Red_Url_Request {
	private $original_url;
	private $decoded_url;

	public function __construct( $url ) {
		$this->original_url = apply_filters( 'redirection_url_source', $url );
		$this->decoded_url = rawurldecode( $this->original_url );

		// Replace the decoded query params with the original ones
		$this->original_url = $this->replace_query_params( $this->original_url, $this->decoded_url );
	}

	/**
	 * Take the decoded path part, but keep the original query params. This ensures any redirects keep the encoding.
	 *
	 * @param string $original_url Original unencoded URL.
	 * @param string $decoded_url Decoded URL.
	 * @return string
	 */
	private function replace_query_params( $original_url, $decoded_url ) {
		$decoded = explode( '?', $decoded_url );

		if ( count( $decoded ) > 1 ) {
			$original = explode( '?', $original_url );

			return $decoded[0] . '?' . $original[1];
		}

		return $decoded_url;
	}

	public function get_original_url() {
		return $this->original_url;
	}

	public function get_decoded_url() {
		return $this->decoded_url;
	}

	public function is_valid() {
		return strlen( $this->get_decoded_url() ) > 0;
	}

	/*
	 * Protect certain URLs from being redirected. Note we don't need to protect wp-admin, as this code doesn't run there
	 */
	public function is_protected_url() {
		$rest = wp_parse_url( red_get_rest_api() );
		$rest_api = $rest['path'] . ( isset( $rest['query'] ) ? '?' . $rest['query'] : '' );

		if ( substr( $this->get_decoded_url(), 0, strlen( $rest_api ) ) === $rest_api ) {
			// Never redirect the REST API
			return true;
		}

		return false;
	}

}
