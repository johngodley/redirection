<?php

class Red_Url_Query {
	const RECURSION_LIMIT = 10;

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
		$query_diff = array_merge( $query_diff, $this->get_query_diff( $target, $this->query ) );

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
			$query_diff = array_merge( $query_diff, $request_query->get_query_diff( $request_query->query, $source_query->query ) );

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

	private function get_url_query( $url ) {
		$params = [];
		$query = $this->get_query_after( $url );

		wp_parse_str( $query ? $query : '', $params );

		return $params;
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

				if ( ! empty( $add ) || $add === '' ) {
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
