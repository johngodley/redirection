<?php

/**
 * Regular expression helper
 */
class Red_Regex {
	private $pattern;
	private $case;

	public function __construct( $pattern, $case_insensitive = false ) {
		$this->pattern = rawurldecode( $pattern );
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

	private function encode_path( $path ) {
		return str_replace( ' ', '%20', $path );
	}

	private function encode_query( $path ) {
		return str_replace( ' ', '+', $path );
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
		$regex = $this->get_regex();
		$result = @preg_replace( $regex, $replace_pattern, $target );

		if ( is_null( $result ) ) {
			return $target;
		}

		// Space encode the target
		$split = explode( '?', $result );
		if ( count( $split ) === 2 ) {
			$result = implode( '?', [ $this->encode_path( $split[0] ), $this->encode_query( $split[1] ) ] );
		} else {
			$result = $this->encode_path( $result );
		}

		return $result;
	}

	private function get_regex() {
		$at_escaped = str_replace( '@', '\\@', $this->pattern );
		$case = '';

		if ( $this->is_ignore_case() ) {
			$case = 'i';
		}

		return '@' . $at_escaped . '@s' . $case;
	}

	public function is_ignore_case() {
		return $this->case;
	}
}
