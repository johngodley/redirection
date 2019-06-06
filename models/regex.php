<?php

/**
 * Regular expression helper
 */
class Red_Regex {
	private $pattern;
	private $case;

	public function __construct( $pattern, $case_insensitive = false ) {
		$this->pattern = $pattern;
		$this->case = $case_insensitive;
	}

	/**
	 * Does $target match the regex pattern, applying case insensitivity if set.
	 *
	 * Note: if the pattern is invalid it will not match
	 *
	 * @param string $target Text to match the regex against
	 * @return boolean match
	 */
	public function is_match( $target ) {
		return @preg_match( $this->get_regex(), $target, $matches ) > 0;
	}

	/**
	 * Regex replace the current pattern with $replace_pattern, applied to $target
	 *
	 * Note: if the pattern is invalid it will return $target
	 *
	 * @param string $replace_pattern The regex replace pattern
	 * @param string $target Text to match the regex against
	 * @return string Replaced text
	 */
	public function replace( $replace_pattern, $target ) {
		$result = @preg_replace( $this->get_regex(), $replace_pattern, $target );
		return is_null( $result ) ? $target : $result;
	}

	private function get_regex() {
		$at_escaped = str_replace( '@', '\\@', $this->pattern );
		$case = '';

		if ( $this->is_ignore_case() ) {
			$case = 'i';
		}

		return '@' . $at_escaped . '@' . $case;
	}

	public function is_ignore_case() {
		return $this->case;
	}
}
