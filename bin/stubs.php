<?php
/**
 * @var String
 */
const ARRAY_A = '';

/**
 * @var String
 */
const REDIRECTION_FILE = '';

/**
 * @var String
 */
const REDIRECTION_API_JSON_RELATIVE = 1;

/**
 * @var String
 */
const REDIRECTION_MIN_WP = '';

/**
 * @var String
 */
const REDIRECTION_VERSION = '';

/**
 * @var String
 */
const REDIRECTION_BUILD = '';

/**
 * @var String
 */

const REDIRECTION_API_JSON = 2;
/**
 * @var String
 */

const REDIRECTION_API_JSON_INDEX = 3;

class Red_Item {
	/**
	 * Get a redirect
	 *
	 * @param Int $id Item ID.
	 * @return Red_Item|\WP_Error Redirect
	 */
	public static function get_by_id( $id ) {
	}

	/**
	 * Update a redirect
	 *
	 * @param Array $json Item ID.
	 * @return WP_Error|Bool
	 */
	public function update( array $json ) {
	}

	/**
	 * Convert to JSON
	 *
	 * @return Array
	 */
	public function to_json() {
	}
}

/**
 * Return Search Regex options
 *
 * @return Array Array of data
 */
function redirection_get_options() {
}

/**
 * Get the configured REST API
 *
 * @param Int|Bool $type Type of API.
 * @return String API URL
 */
function redirection_get_rest_api( $type = false ) {
}

/**
 * Set Search Regex options. Can be passed as many options as necessary and the rest will be unchanged
 *
 * @param Array $settings Array of name => value.
 * @return Array Array of name => value
 */
function redirection_set_options( array $settings = array() ) {
}
