<?php

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
				$this->flag_trailing = false;
			}
		}
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

	public function get_json( $defaults = [] ) {
		$json = [
			self::FLAG_QUERY => $this->flag_query,
			self::FLAG_CASE => $this->is_ignore_case(),
			self::FLAG_TRAILING => $this->is_ignore_trailing(),
			self::FLAG_REGEX => $this->is_regex(),
		];

		if ( count( $defaults ) > 0 ) {
			foreach ( $json as $key => $value ) {
				if ( isset( $defaults[ $key ] ) && $value === $defaults[ $key ] ) {
					unset( $json[ $key ] );
				}
			}
		}

		return $json;
	}
}
