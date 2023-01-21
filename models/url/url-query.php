<?php

/**
 * Query parameter martching
 */
class Red_Url_Query {
	/**
	 * @type Integer
	 */
	const RECURSION_LIMIT = 10;

	/**
	 * Original query parameters (used when passing)
	 *
	 * @var array
	 */
	private $original_query = [];

	/**
	 * Match query parameters (used only for matching, and maybe be lowercased)
	 *
	 * @var array
	 */
	private $match_query = [];

	/**
	 * Is this an exact match?
	 *
	 * @var boolean|string
	 */
	private $match_exact = false;

	/**
	 * Constructor
	 *
	 * @param String           $url URL.
	 * @param Red_Source_Flags $flags URL flags.
	 */
	public function __construct( $url, $flags ) {
		$this->original_query = $this->get_url_query( $url );
		$this->match_query = $this->original_query;

		if ( $flags->is_ignore_case() ) {
			$this->match_query = $this->get_url_query( Red_Url_Path::to_lower( $url ) );
		}
	}

	/**
	 * Does this object match the URL?
	 *
	 * @param String           $url URL to match.
	 * @param Red_Source_Flags $flags Source flags.
	 * @return boolean
	 */
	public function is_match( $url, Red_Source_Flags $flags ) {
		if ( $flags->is_ignore_case() ) {
			$url = Red_Url_Path::to_lower( $url );
		}

		// If we can't parse the query params then match the params exactly
		if ( $this->match_exact !== false ) {
			return $this->is_string_match( $this->get_query_after( $url ), $this->match_exact, $flags->is_ignore_case() );
		}

		$target = $this->get_url_query( $url );

		// All params in the source have to exist in the request, but in any order
		$matched = $this->get_query_same( $this->match_query, $target, $flags->is_ignore_case() );

		if ( count( $matched ) !== count( $this->match_query ) ) {
			// Source params arent matched exactly
			return false;
		};

		// Get list of whatever is left over
		$query_diff = $this->get_query_diff( $this->match_query, $target );
		$query_diff = array_merge( $query_diff, $this->get_query_diff( $target, $this->match_query ) );

		if ( $flags->is_query_ignore() || $flags->is_query_pass() ) {
			return true;  // This ignores all other query params
		}

		// In an exact match there shouldn't be any more params
		return count( $query_diff ) === 0;
	}

	/**
	 * Return true if the two strings match, false otherwise. Pays attention to case sensitivity
	 *
	 * @param string  $first First string.
	 * @param string  $second Second string.
	 * @param boolean $case Case sensitivity.
	 * @return boolean
	 */
	private function is_string_match( $first, $second, $case ) {
		if ( $case ) {
			return Red_Url_Path::to_lower( $first ) === Red_Url_Path::to_lower( $second );
		}

		return $first === $second;
	}

	/**
	 * Pass query params from one URL to another URL, ignoring any params that already exist on the target.
	 *
	 * @param string           $target_url The target URL to add params to.
	 * @param string           $requested_url The source URL to pass params from.
	 * @param Red_Source_Flags $flags Any URL flags.
	 * @return string URL, modified or not.
	 */
	public static function add_to_target( $target_url, $requested_url, Red_Source_Flags $flags ) {
		if ( $flags->is_query_pass() && $target_url ) {
			$source_query = new Red_Url_Query( $target_url, $flags );
			$request_query = new Red_Url_Query( $requested_url, $flags );

			// Now add any remaining params
			$query_diff = $source_query->get_query_diff( $source_query->original_query, $request_query->original_query );
			$request_diff = $request_query->get_query_diff( $request_query->original_query, $source_query->original_query );

			foreach ( $request_diff as $key => $value ) {
				$query_diff[ $key ] = $value;
			}

			// Remove any params from $source that are present in $request - we dont allow
			// predefined params to be overridden
			foreach ( array_keys( $query_diff ) as $key ) {
				if ( isset( $source_query->original_query[ $key ] ) ) {
					unset( $query_diff[ $key ] );
				}
			}

			return self::build_url( $target_url, $query_diff );
		}

		return $target_url;
	}

	/**
	 * Build a URL from a base and query parameters
	 *
	 * @param String $url Base URL.
	 * @param Array  $query_array Query parameters.
	 * @return String
	 */
	public static function build_url( $url, $query_array ) {
		$query = http_build_query( array_map( function( $value ) {
			if ( $value === null ) {
				return '';
			}

			return $value;
		}, $query_array ) );

		$query = preg_replace( '@%5B\d*%5D@', '[]', $query );  // Make these look like []

		foreach ( $query_array as $key => $value ) {
			if ( $value === null ) {
				$search = str_replace( '%20', '+', rawurlencode( $key ) . '=' );
				$replace = str_replace( '%20', '+', rawurlencode( $key ) );

				$query = str_replace( $search, $replace, $query );
			}
		}

		$query = str_replace( '%252B', '+', $query );

		if ( $query ) {
			// Get any fragment
			$target_fragment = wp_parse_url( $url, PHP_URL_FRAGMENT );

			// If we have a fragment we need to ensure it comes after the query parameters, not before
			if ( $target_fragment ) {
				// Remove fragment
				$url = str_replace( '#' . $target_fragment, '', $url );

				// Add to the end of the query
				$query .= '#' . $target_fragment;
			}

			return $url . ( strpos( $url, '?' ) === false ? '?' : '&' ) . $query;
		}

		return $url;
	}

	/**
	 * Get a URL with the given base and query parameters from this Url_Query
	 *
	 * @param String $url Base URL.
	 * @return String
	 */
	public function get_url_with_query( $url ) {
		return self::build_url( $url, $this->original_query );
	}

	/**
	 * Get the query parameters
	 *
	 * @return Array
	 */
	public function get() {
		return $this->original_query;
	}

	/**
	 * Does the URL and the query params contain no parameters?
	 *
	 * @param String $url URL.
	 * @param Array  $params Query params.
	 * @return boolean
	 */
	private function is_exact_match( $url, $params ) {
		// No parsed query params but we have query params on the URL - some parsing error with wp_parse_str
		if ( count( $params ) === 0 && $this->has_query_params( $url ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get query parameters from a URL
	 *
	 * @param String $url URL.
	 * @return array
	 */
	private function get_url_query( $url ) {
		$params = [];
		$query = $this->get_query_after( $url );
		$internal = $this->parse_str( $query );

		wp_parse_str( $query ? $query : '', $params );

		// For exactness and due to the way parse_str works we go through and check any query param without a value
		foreach ( $params as $key => $value ) {
			if ( is_string( $value ) && strlen( $value ) === 0 && strpos( $url, $key . '=' ) === false ) {
				$params[ $key ] = null;
			}
		}

		// A work-around until we replace parse_str with internal function
		foreach ( $internal as $pos => $internal_param ) {
			if ( $internal_param['parse_str'] !== $internal_param['name'] ) {
				foreach ( $params as $key => $value ) {
					if ( $key === $internal_param['parse_str'] ) {
						unset( $params[ $key ] );
						unset( $internal[ $pos ] );
						$params[ $internal_param['name'] ] = $value;
					}
				}
			}
		}

		if ( $this->is_exact_match( $url, $params ) ) {
			$this->match_exact = $query;
		}

		return $params;
	}

	/**
	 * A replacement for parse_str, which behaves oddly in some situations (spaces and no param value)
	 *
	 * TODO: use this in preference to parse_str
	 *
	 * @param string $query Query.
	 * @return string
	 */
	private function parse_str( $query ) {
		$params = [];

		if ( strlen( $query ) === 0 ) {
			return $params;
		}

		$parts = explode( '&', $query ? $query : '' );

		foreach ( $parts as $part ) {
			$param = explode( '=', $part );
			$parse_str = [];

			wp_parse_str( $part, $parse_str );

			$params[] = [
				'name' => str_replace( [ '[', ']', '%5B', '%5D' ], '', str_replace( '+', ' ', $param[0] ) ),
				'value' => isset( $param[1] ) ? str_replace( '+', ' ', $param[1] ) : null,
				'parse_str' => implode( '', array_keys( $parse_str ) ),
			];
		}

		return $params;
	}

	/**
	 * Does the URL contain query parameters?
	 *
	 * @param String $url URL.
	 * @return boolean
	 */
	public function has_query_params( $url ) {
		$qpos = strpos( $url, '?' );

		if ( $qpos === false ) {
			return false;
		}

		return true;
	}

	/**
	 * Get parameters after the ?
	 *
	 * @param String $url URL.
	 * @return String
	 */
	public function get_query_after( $url ) {
		$qpos = strpos( $url, '?' );
		$qrpos = strpos( $url, '\\?' );

		// No ? anywhere - no query
		if ( $qpos === false ) {
			return '';
		}

		// Found an escaped ? and it comes before the non-escaped ?
		if ( $qrpos !== false && $qrpos < $qpos ) {
			return substr( $url, $qrpos + 2 );
		}

		// Standard query param
		return substr( $url, $qpos + 1 );
	}

	private function get_query_case( array $query ) {
		$keys = [];
		foreach ( array_keys( $query ) as $key ) {
			$keys[ Red_Url_Path::to_lower( $key ) ] = $key;
		}

		return $keys;
	}

	/**
	 * Get query parameters that are the same in both query arrays
	 *
	 * @param array   $source_query Source query params.
	 * @param array   $target_query Target query params.
	 * @param bool    $is_ignore_case Ignore case.
	 * @param integer $depth Current recursion depth.
	 * @return array
	 */
	public function get_query_same( array $source_query, array $target_query, $is_ignore_case, $depth = 0 ) {
		if ( $depth > self::RECURSION_LIMIT ) {
			return [];
		}

		$source_keys = $this->get_query_case( $source_query );
		$target_keys = $this->get_query_case( $target_query );

		$same = [];
		foreach ( $source_keys as $key => $original_key ) {
			// Does the key exist in the target
			if ( isset( $target_keys[ $key ] ) ) {
				// Key exists. Now match the value
				$source_value = $source_query[ $original_key ];
				$target_value = $target_query[ $target_keys[ $key ] ];
				$add = false;

				if ( is_array( $source_value ) && is_array( $target_value ) ) {
					$add = $this->get_query_same( $source_value, $target_value, $is_ignore_case, $depth + 1 );

					if ( count( $add ) !== count( $source_value ) ) {
						$add = false;
					}
				} elseif ( is_string( $source_value ) && is_string( $target_value ) ) {
					$add = $this->is_string_match( $source_value, $target_value, $is_ignore_case ) ? $source_value : false;
				} elseif ( $source_value === null && $target_value === null ) {
					$add = null;
				}

				if ( ! empty( $add ) || is_numeric( $add ) || $add === '' || $add === null ) {
					$same[ $original_key ] = $add;
				}
			}
		}

		return $same;
	}

	/**
	 * Get the difference in query parameters
	 *
	 * @param array   $source_query Source query params.
	 * @param array   $target_query Target query params.
	 * @param integer $depth Current recursion depth.
	 * @return array
	 */
	public function get_query_diff( array $source_query, array $target_query, $depth = 0 ) {
		if ( $depth > self::RECURSION_LIMIT ) {
			return [];
		}

		$diff = [];
		foreach ( $source_query as $key => $value ) {
			if ( array_key_exists( $key, $target_query ) && is_array( $value ) && is_array( $target_query[ $key ] ) ) {
				$add = $this->get_query_diff( $source_query[ $key ], $target_query[ $key ], $depth + 1 );

				if ( ! empty( $add ) ) {
					$diff[ $key ] = $add;
				}
			} elseif ( ! array_key_exists( $key, $target_query ) || ! $this->is_value( $value ) || ! $this->is_value( $target_query[ $key ] ) || $target_query[ $key ] !== $source_query[ $key ] ) {
				$diff[ $key ] = $value;
			}
		}

		return $diff;
	}

	private function is_value( $value ) {
		return is_string( $value ) || $value === null;
	}
}
