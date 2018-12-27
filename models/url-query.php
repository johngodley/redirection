<?php

class Red_Url_Query {
	private $query = [];

	public function __construct( $url ) {
		$this->query = $this->get_url_query( $url );
	}

	public function is_match( $url, Red_Source_Flags $flags ) {
		$target = $this->get_url_query( $url );

		// All params in the source have to exist in the request, but in any order
		$matched = $this->get_query_same( $this->query, $target );

		if ( count( $matched ) !== count( $this->query ) ) {
			// Source params arent matched exactly
			return false;
		};

		// Get list of whatever is left over
		$query_diff = $this->get_query_diff( $this->query, $target );

		if ( $flags->is_query_ignore() || $flags->is_query_pass() ) {
			return true;  // This ignores all other query params
		}

		// In an exact match there shouldn't be any more params
		return count( $query_diff ) === 0;
	}

	/**
	 * Pass query params from one URL to another URL, ignoring any params that already exist on the target
	 *
	 * @param string $target_url The target URL to add params to
	 * @param string $requested_url The source URL to pass params from
	 * @param Red_Source_Flags $flags Any URL flags
	 * @return string URL, modified or not
	 */
	public static function add_to_target( $target_url, $requested_url, Red_Source_Flags $flags ) {
		if ( $flags->is_query_pass() && $target_url ) {
			$source_query = new Red_Url_Query( $target_url );
			$request_query = new Red_Url_Query( $requested_url );

			// Now add any remaining params
			$query_diff = $source_query->get_query_diff( $source_query->query, $request_query->query );

			// Remove any params from $source that are present in $request - we dont allow
			// predefined params to be overridden
			foreach ( $query_diff as $key => $value ) {
				$query_diff[ $key ] = urlencode( $value );

				if ( isset( $source_query->query[ $key ] ) ) {
					unset( $query_diff[ $key ] );
				}
			}

			return add_query_arg( $query_diff, $target_url );
		}

		return $target_url;
	}

	private function get_url_query( $url ) {
		$params = [];

		$query = wp_parse_url( $url, PHP_URL_QUERY );
		wp_parse_str( $query ? $query : '', $params );

		return $params;
	}

	private function get_query_same( array $source_query, array $target_query ) {
		return array_intersect_assoc( $source_query, $target_query );
	}

	private function get_query_diff( array $source_query, array $target_query ) {
		return array_diff_assoc( $target_query, $source_query );
	}
}
