<?php

class Red_Source_Flags {
	const QUERY_IGNORE = 'ignore';
	const QUERY_EXACT = 'exact';

	const FLAG_QUERY_MATCH = 'queryMatch';
	const FLAG_CASE = 'case';
	const FLAG_TRAILING = 'trailing';
	const FLAG_QUERY_PASS = 'queryPass';
	const FLAG_REGEX = 'regex';

	private $ignore_case;
	private $ignore_trailing;
	private $regex;
	private $query_match;
	private $query_pass;

	public function __construct( $json = null ) {
		// TODO: should allow defaults + tests
		$this->ignore_case = false;
		$this->ignore_trailing = false;
		$this->regex = false;
		$this->query_match = self::QUERY_EXACT;
		$this->query_pass = false;

		if ( $json ) {
			$this->set_flags( $json );
		}
	}

	public function set_flags( array $json ) {
		if ( isset( $json[ self::FLAG_QUERY_MATCH ] ) && in_array( $json[ self::FLAG_QUERY_MATCH ], [ self::QUERY_IGNORE, self::QUERY_EXACT ], true ) ) {
			$this->query_match = $json[ self::FLAG_QUERY_MATCH ];
		}

		if ( isset( $json[ self::FLAG_CASE ] ) && is_bool( $json[ self::FLAG_CASE ] ) ) {
			$this->ignore_case = $json[ self::FLAG_CASE ] ? true : false;
		}

		if ( isset( $json[ self::FLAG_TRAILING ] ) && is_bool( $json[ self::FLAG_TRAILING ] ) ) {
			$this->ignore_trailing = $json[ self::FLAG_TRAILING ] ? true : false;
		}

		if ( isset( $json[ self::FLAG_QUERY_PASS ] ) && is_bool( $json[ self::FLAG_QUERY_PASS ] ) ) {
			$this->query_pass = $json[ self::FLAG_QUERY_PASS ] ? true : false;
		}

		if ( isset( $json[ self::FLAG_REGEX ] ) && is_bool( $json[ self::FLAG_REGEX ] ) ) {
			// Regex auto-disables other things
			$this->regex = $json[ self::FLAG_REGEX ] ? true : false;
			$this->query_match = self::QUERY_EXACT;
			$this->query_pass = false;
			$this->ignore_trailing = false;
		}
	}

	public function is_ignore_trailing() {
		return $this->ignore_trailing;
	}

	public function is_ignore_case() {
		return $this->ignore_case;
	}

	public function is_regex() {
		return $this->regex;
	}

	public function is_query_exact() {
		return $this->query_match === self::QUERY_EXACT;
	}

	public function is_query_ignore() {
		return $this->query_match === self::QUERY_IGNORE;
	}

	public function is_query_pass() {
		return $this->query_pass;
	}

	public function get_json() {
		// TODO: should remove defaults
		return [
			self::FLAG_QUERY_MATCH => $this->query_match,
			self::FLAG_QUERY_PASS => $this->query_pass,
			self::FLAG_CASE => $this->ignore_case,
			self::FLAG_TRAILING => $this->ignore_trailing,
			self::FLAG_REGEX => $this->regex,
		];
	}
}
