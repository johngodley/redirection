<?php

/**
 * Represent URL source flags.
 *
 * @phpstan-type FlagName 'flag_query'|'flag_case'|'flag_trailing'|'flag_regex'
 * @phpstan-type QueryType 'ignore'|'exact'|'pass'|'exactorder'
 * @phpstan-type FlagsJson array{
 *     flag_query: QueryType,
 *     flag_case: bool,
 *     flag_trailing: bool,
 *     flag_regex: bool
 * }
 * @phpstan-import-type RedirectionOptions from Red_Options
 */
class Red_Source_Flags {
	const QUERY_IGNORE = 'ignore';
	const QUERY_EXACT = 'exact';
	const QUERY_PASS = 'pass';
	const QUERY_EXACT_ORDER = 'exactorder';

	const FLAG_QUERY = 'flag_query';
	const FLAG_CASE = 'flag_case';
	const FLAG_TRAILING = 'flag_trailing';
	const FLAG_REGEX = 'flag_regex';

	/**
	 * Case insensitive matching
	 *
	 * @var boolean
	 */
	private $flag_case = false;

	/**
	 * Ignored trailing slashes
	 *
	 * @var boolean
	 */
	private $flag_trailing = false;

	/**
	 * Regular expression
	 *
	 * @var boolean
	 */
	private $flag_regex = false;

	/**
	 * Query parameter matching
	 *
	 * @var self::QUERY_EXACT|self::QUERY_IGNORE|self::QUERY_PASS|self::QUERY_EXACT_ORDER
	 */
	private $flag_query = self::QUERY_EXACT;

	/**
	 * Values that have been set (tracks which flag keys were provided)
	 *
	 * @var array<FlagName>
	 */
	private $values_set = [];

	/**
	 * Constructor
	 *
	 * @param array<string, mixed>|null $json JSON object.
	 */
	public function __construct( $json = null ) {
		if ( $json !== null ) {
			$this->set_flags( $json );
		}
	}

	/**
	 * Get list of valid query types as an array
	 *
	 * @return array<QueryType>
	 */
	private function get_allowed_query() {
		return [
			self::QUERY_IGNORE,
			self::QUERY_EXACT,
			self::QUERY_PASS,
			self::QUERY_EXACT_ORDER,
		];
	}

	/**
	 * Parse flag data.
	 *
	 * @param array<string, mixed> $json Flag data.
	 * @return void
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
		/** @var array<FlagName> $intersected */
		$intersected = array_values( array_intersect( array_keys( $json ), array_keys( $this->get_json() ) ) );
		$this->values_set = $intersected;
	}

	/**
	 * Return `true` if ignore trailing slash, `false` otherwise
	 *
	 * @return boolean
	 */
	public function is_ignore_trailing() {
		return $this->flag_trailing;
	}

	/**
	 * Return `true` if ignore case, `false` otherwise
	 *
	 * @return boolean
	 */
	public function is_ignore_case() {
		return $this->flag_case;
	}

	/**
	 * Return `true` if ignore trailing slash, `false` otherwise
	 *
	 * @return boolean
	 */
	public function is_regex() {
		return $this->flag_regex;
	}

	/**
	 * Return `true` if exact query match, `false` otherwise
	 *
	 * @return boolean
	 */
	public function is_query_exact() {
		return $this->flag_query === self::QUERY_EXACT;
	}

	/**
	 * Return `true` if exact query match in set order, `false` otherwise
	 *
	 * @return boolean
	 */
	public function is_query_exact_order() {
		return $this->flag_query === self::QUERY_EXACT_ORDER;
	}

	/**
	 * Return `true` if ignore query params, `false` otherwise
	 *
	 * @return boolean
	 */
	public function is_query_ignore() {
		return $this->flag_query === self::QUERY_IGNORE;
	}

	/**
	 * Return `true` if ignore and pass query params, `false` otherwise
	 *
	 * @return boolean
	 */
	public function is_query_pass() {
		return $this->flag_query === self::QUERY_PASS;
	}

	/**
	 * Return the flags as a JSON object
	 *
	 * @return FlagsJson
	 */
	public function get_json() {
		return [
			self::FLAG_QUERY => $this->flag_query,
			self::FLAG_CASE => $this->is_ignore_case(),
			self::FLAG_TRAILING => $this->is_ignore_trailing(),
			self::FLAG_REGEX => $this->is_regex(),
		];
	}

	/**
	 * Return flag data, with defaults removed from the data.
	 *
	 * @param RedirectionOptions $defaults Defaults to remove.
	 * @return array<string, mixed>
	 */
	public function get_json_without_defaults( $defaults ) {
		$json = $this->get_json();

		// @phpstan-ignore greater.alwaysTrue
		if ( count( $defaults ) > 0 ) {
			foreach ( $json as $key => $value ) {
				// @phpstan-ignore isset.offset
				if ( isset( $defaults[ $key ] ) && $value === $defaults[ $key ] ) {
					unset( $json[ $key ] );
				}
			}
		}

		return $json;
	}

	/**
	 * Return flag data, with defaults filling in any gaps not set.
	 *
	 * @return FlagsJson
	 */
	public function get_json_with_defaults() {
		$settings = Red_Options::get();
		$json = $this->get_json();
		$defaults = [
			self::FLAG_QUERY => $settings[ self::FLAG_QUERY ],
			self::FLAG_CASE => $settings[ self::FLAG_CASE ],
			self::FLAG_TRAILING => $settings[ self::FLAG_TRAILING ],
			self::FLAG_REGEX => $settings[ self::FLAG_REGEX ],
		];

		foreach ( $this->values_set as $key ) {
			// @phpstan-ignore isset.offset
			if ( ! isset( $json[ $key ] ) ) {
				$json[ $key ] = $defaults[ $key ];
			}
		}

		return $json;
	}
}
