<?php

/**
 * Represent URL source flags
 */
class Red_Source_Flags {
	const QUERY_IGNORE = 'ignore';
	const QUERY_EXACT = 'exact';
	const QUERY_PASS = 'pass';

	const FLAG_QUERY = 'flag_query';
	const FLAG_CASE = 'flag_case';
	const FLAG_TRAILING = 'flag_trailing';
	const FLAG_REGEX = 'flag_regex';

	private $flag_case = false;
	private $flag_trailing = false;
	private $flag_regex = false;
	private $flag_query = self::QUERY_EXACT;
	private $values_set = [];

	public function __construct( $json = null ) {
		if ( $json ) {
			$this->set_flags( $json );
		}
	}

	private function get_allowed_query() {
		return [
			self::QUERY_IGNORE,
			self::QUERY_EXACT,
			self::QUERY_PASS,
		];
	}

	/**
	 * Parse flag data
	 *
	 * @param array $json Flag data
	 */
	public function set_flags( array $json ) {
		if ( isset( $json[ self::FLAG_QUERY ] ) && in_array( $json[ self::FLAG_QUERY ], $this->get_allowed_query(), true ) ) {
			$this->flag_query = $json[ self::FLAG_QUERY ];
		}

		if ( isset( $json[ self::FLAG_CASE ] ) && is_bool( $json[ self::FLAG_CASE ] ) ) {
			$this->flag_case = $json[ self::FLAG_CASE ] ? true : false;
		}

		if ( isset( $json[ self::FLAG_TRAILING ] ) && is_bool( $json[ self::FLAG_TRAILING ] ) ) {
			$this->flag_trailing = $json[ self::FLAG_TRAILING ] ? true : false;
		}

		if ( isset( $json[ self::FLAG_REGEX ] ) && is_bool( $json[ self::FLAG_REGEX ] ) ) {
			$this->flag_regex = $json[ self::FLAG_REGEX ] ? true : false;

			if ( $this->flag_regex ) {
				// Regex auto-disables other things
				$this->flag_query = self::QUERY_EXACT;
			}
		}

		// Keep track of what values have been set, so we know what to override with defaults later
		$this->values_set = array_intersect( array_keys( $json ), array_keys( $this->get_json() ) );
	}

	public function is_ignore_trailing() {
		return $this->flag_trailing;
	}

	public function is_ignore_case() {
		return $this->flag_case;
	}

	public function is_regex() {
		return $this->flag_regex;
	}

	public function is_query_exact() {
		return $this->flag_query === self::QUERY_EXACT;
	}

	public function is_query_ignore() {
		return $this->flag_query === self::QUERY_IGNORE;
	}

	public function is_query_pass() {
		return $this->flag_query === self::QUERY_PASS;
	}

	public function get_json() {
		return [
			self::FLAG_QUERY => $this->flag_query,
			self::FLAG_CASE => $this->is_ignore_case(),
			self::FLAG_TRAILING => $this->is_ignore_trailing(),
			self::FLAG_REGEX => $this->is_regex(),
		];
	}

	/**
	 * Return flag data, with defaults removed from the data
	 */
	public function get_json_without_defaults( $defaults ) {
		$json = $this->get_json();

		if ( count( $defaults ) > 0 ) {
			foreach ( $json as $key => $value ) {
				if ( isset( $defaults[ $key ] ) && $value === $defaults[ $key ] ) {
					unset( $json[ $key ] );
				}
			}
		}

		return $json;
	}

	/**
	 * Return flag data, with defaults filling in any gaps not set
	 */
	public function get_json_with_defaults() {
		$settings = red_get_options();
		$json = $this->get_json();
		$defaults = [
			self::FLAG_QUERY => $settings[ self::FLAG_QUERY ],
			self::FLAG_CASE => $settings[ self::FLAG_CASE ],
			self::FLAG_TRAILING => $settings[ self::FLAG_TRAILING ],
			self::FLAG_REGEX => $settings[ self::FLAG_REGEX ],
		];

		foreach ( $this->values_set as $key ) {
			if ( ! isset( $json[ $key ] ) ) {
				$json[ $key ] = $defaults[ $key ];
			}
		}

		return $json;
	}
}
