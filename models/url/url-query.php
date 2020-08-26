<?php

class Red_Url_Query {
	const RECURSION_LIMIT = 10;

	private $query = [];
	private $match_exact = false;

	public function __construct( $url, $flags ) {
		if ( $flags->is_ignore_case() ) {
			$url = Red_Url_Path::to_lower( $url );
		}

		$this->query = $this->get_url_query( $url );
	}

	public function is_match( $url, Red_Source_Flags $flags ) {
		if ( $flags->is_ignore_case() ) {
			$url = Red_Url_Path::to_lower( $url );
		}

		// If we can't parse the query params then match the params exactly
		if ( $this->match_exact !== false ) {
			return $this->get_query_after( $url ) === $this->match_exact;
		}

		$target = $this->get_url_query( $url );

		// All params in the source have to exist in the request, but in any order
		$matched = $this->get_query_same( $this->query, $target );

		if ( count( $matched ) !== count( $this->query ) ) {
			// Source params arent matched exactly
			return false;
		};

		// Get list of whatever is left over
		$query_diff = $this->get_query_diff( $this->query, $target );
		$query_diff = array_merge( $query_diff, $this->get_query_diff( $target, $this->query ) );

		if ( $flags->is_query_ignore() || $flags->is_query_pass() ) {
			return true;  // This ignores all other query params
		}

		// In an exact match there shouldn't be any more params
		return count( $query_diff ) === 0;
	}

	/**
	 * Pass query params from one URL to another URL, ignoring any params that already exist on the target.
	 *
	 * @param string $target_url The target URL to add params to.
	 * @param string $requested_url The source URL to pass params from.
	 * @param Red_Source_Flags $flags Any URL flags.
	 * @return string URL, modified or not.
	 */
	public static function add_to_target( $target_url, $requested_url, Red_Source_Flags $flags ) {
		if ( $flags->is_query_pass() && $target_url ) {
			$source_query = new Red_Url_Query( $target_url, $flags );
			$request_query = new Red_Url_Query( $requested_url, $flags );

			// Now add any remaining params
			$query_diff = $source_query->get_query_diff( $source_query->query, $request_query->query );
			$request_diff = $request_query->get_query_diff( $request_query->query, $source_query->query );

			foreach ( $request_diff as $key => $value ) {
				$query_diff[ $key ] = $value;
			}

			// Remove any params from $source that are present in $request - we dont allow
			// predefined params to be overridden
			foreach ( $query_diff as $key => $value ) {
				if ( isset( $source_query->query[ $key ] ) ) {
					unset( $query_diff[ $key ] );
				}
			}

			$query = http_build_query( $query_diff );
			$query = preg_replace( '@%5B\d*%5D@', '[]', $query );  // Make these look like []

			if ( $query ) {
				return $target_url . ( strpos( $target_url, '?' ) === false ? '?' : '&' ) . $query;
			}
		}

		return $target_url;
	}

	public function get() {
		return $this->query;
	}

	private function is_exact_match( $url, $params ) {
		// No parsed query params but we have query params on the URL - some parsing error with wp_parse_str
		if ( count( $params ) === 0 && $this->has_query_params( $url ) ) {
			return true;
		}

		return false;
	}

	private function get_url_query( $url ) {
		$params = [];
		$query = $this->get_query_after( $url );

		wp_parse_str( $query ? $query : '', $params );

		if ( $this->is_exact_match( $url, $params ) ) {
			$this->match_exact = $query;
		}

		return $params;
	}

	public function has_query_params( $url ) {
		$qpos = strpos( $url, '?' );

		if ( $qpos === false ) {
			return false;
		}

		return true;
	}

	public function get_query_after( $url ) {
		$qpos = strpos( $url, '?' );
		$qrpos = strpos( $url, '\\?' );

		if ( $qpos === false ) {
			return '';
		}

		if ( $qrpos !== false && $qrpos < $qpos ) {
			return substr( $url, $qrpos + strlen( $qrpos ) );
		}

		return substr( $url, $qpos + 1 );
	}

	public function get_query_same( array $source_query, array $target_query, $depth = 0 ) {
		if ( $depth > self::RECURSION_LIMIT ) {
			return [];
		}

		$same = [];
		foreach ( $source_query as $key => $value ) {
			if ( isset( $target_query[ $key ] ) ) {
				$add = false;

				if ( is_array( $value ) && is_array( $target_query[ $key ] ) ) {
					$add = $this->get_query_same( $source_query[ $key ], $target_query[ $key ], $depth + 1 );

					if ( count( $add ) !== count( $source_query[ $key ] ) ) {
						$add = false;
					}
				} elseif ( is_string( $value ) && is_string( $target_query[ $key ] ) ) {
					$add = $value === $target_query[ $key ] ? $value : false;
				}

				if ( ! empty( $add ) || is_numeric( $add ) || $add === '' ) {
					$same[ $key ] = $add;
				}
			}
		}

		return $same;
	}

	public function get_query_diff( array $source_query, array $target_query, $depth = 0 ) {
		if ( $depth > self::RECURSION_LIMIT ) {
			return [];
		}

		$diff = [];
		foreach ( $source_query as $key => $value ) {
			$found = false;

			if ( isset( $target_query[ $key ] ) && is_array( $value ) && is_array( $target_query[ $key ] ) ) {
				$add = $this->get_query_diff( $source_query[ $key ], $target_query[ $key ], $depth + 1 );

				if ( ! empty( $add ) ) {
					$diff[ $key ] = $add;
				}
			} elseif ( ! isset( $target_query[ $key ] ) || ! is_string( $value ) || ! is_string( $target_query[ $key ] ) || $target_query[ $key ] !== $source_query[ $key ] ) {
				$diff[ $key ] = $value;
			}
		}

		return $diff;
	}
}
