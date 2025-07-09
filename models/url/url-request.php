<?php

/**
 * Decode request URLs
 */
class Red_Url_Request {
	/**
	 * Original URL
	 *
	 * @var String
	 */
	private $original_url;

	/**
	 * Decoded URL
	 *
	 * @var String
	 */
	private $decoded_url;

	/**
	 * Constructor
	 *
	 * @param string $url URL.
	 */
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

			if ( count( $original ) > 1 ) {
				return $decoded[0] . '?' . $original[1];
			}
		}

		return $decoded_url;
	}

	/**
	 * Get the original URL
	 *
	 * @return string
	 */
	public function get_original_url() {
		return $this->original_url;
	}

	/**
	 * Get the decoded URL
	 *
	 * @return string
	 */
	public function get_decoded_url() {
		return $this->decoded_url;
	}

	/**
	 * Is this a valid URL?
	 *
	 * @return boolean
	 */
	public function is_valid() {
		return strlen( $this->get_decoded_url() ) > 0;
	}

	/**
	 * Protect certain URLs from being redirected. Note we don't need to protect wp-admin, as this code doesn't run there
	 *
	 * @return boolean
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

	/**
	 * Check if the current URL's post type is in the ignored post types list.
	 *
	 * This function resolves the original URL to a post ID, determines its post type,
	 * and compares it against the `ignore_posttypes` setting.
	 *
	 * @return bool True if the post type is in the ignore list, false otherwise.
	 */
	public function is_ignore_posttypes(){

		// Resolve the original URL to a post ID.
		$post_id = url_to_postid( $this->original_url );
		if( empty( $post_id ) ){
			return false;
		}

		$post_type = get_post_type( $post_id );
		if( empty( $post_type ) ){
			return;
		}

		$settings = red_get_options();

		// Check if the post type is listed in ignored post types.
		return is_array( $settings['ignore_posttypes'] ) && in_array( $post_type, $settings['ignore_posttypes'], true );
	}
}
