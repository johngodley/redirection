<?php

namespace Redirection\Core;

/**
 * Regular expression helper
 */
class Regex {
	/**
	 * @var string
	 */
	private $pattern;

	/**
	 * @var bool
	 */
	private $case;

	/**
	 * @param string $pattern
	 * @param bool $case_insensitive
	 */
	public function __construct( $pattern, $case_insensitive = false ) {
		$this->pattern = rawurldecode( $pattern );
		$this->case = $case_insensitive;
	}

	/**
	 * Does $target match the regex pattern, applying case insensitivity if set.
	 *
	 * Note: if the pattern is invalid it will not match
	 *
	 * @param string $target Text to match the regex against.
	 * @return boolean match
	 */
	public function is_match( $target ) {
		return @preg_match( $this->get_regex(), $target, $matches ) > 0;
	}

	/**
	 * @param string $path
	 * @return string
	 */
	private function encode_path( $path ) {
		return str_replace( ' ', '%20', $path );
	}

	/**
	 * @param string $path
	 * @return string
	 */
	private function encode_query( $path ) {
		return str_replace( ' ', '+', $path );
	}

	/**
	 * Regex replace the current pattern with $replace_pattern, applied to $target
	 *
	 * Note: if the pattern is invalid it will return $target
	 *
	 * @param string $replace_pattern The regex replace pattern.
	 * @param string $target Text to match the regex against.
	 * @param bool $keep_before Keep any text before the first match, as preg_replace does.
	 * @param array<string, string> $placeholders Characters in text from the target to replace with a placeholder.
	 * @return string Replaced text
	 */
	public function replace( $replace_pattern, $target, $keep_before = true, array $placeholders = [] ) {
		$regex = $this->get_regex();

		if ( @preg_match_all( $regex, $target, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) === false ) {
			return $target;
		}

		// This is the same as preg_replace, except that text from the target can use placeholders
		$result = '';
		$offset = ! $keep_before && count( $matches ) > 0 ? $matches[0][0][1] : 0;

		foreach ( $matches as $match ) {
			$result .= strtr( substr( $target, $offset, $match[0][1] - $offset ), $placeholders );
			$result .= $this->expand_references( $replace_pattern, $match, $placeholders );
			$offset = $match[0][1] + strlen( $match[0][0] );
		}

		$result .= strtr( substr( $target, $offset ), $placeholders );

		// Space encode the target
		$split = explode( '?', $result );
		if ( count( $split ) === 2 ) {
			$result = implode( '?', [ $this->encode_path( $split[0] ), $this->encode_query( $split[1] ) ] );
		} else {
			$result = $this->encode_path( $result );
		}

		return $result;
	}

	/**
	 * Expand the `$1`, `${1}`, and `\1` references in a replace pattern, in the same way as preg_replace.
	 *
	 * @param string $replace_pattern The regex replace pattern.
	 * @param array<int, array{0: string, 1: int}> $match Match, with offsets.
	 * @param array<string, string> $placeholders Characters in a reference to replace with a placeholder.
	 * @return string
	 */
	private function expand_references( $replace_pattern, array $match, array $placeholders ) {
		return (string) preg_replace_callback(
			'@\\\\([\\\\$])|\\\\([0-9]{1,2})|\$([0-9]{1,2})|\$\{([0-9]{1,2})\}@',
			function ( $reference ) use ( $match, $placeholders ) {
				// An escaped backslash or dollar
				if ( $reference[1] !== '' ) {
					return $reference[1];
				}

				$index = intval( implode( '', array_slice( $reference, 2 ) ), 10 );

				if ( isset( $match[ $index ] ) && $match[ $index ][1] !== -1 ) {
					return strtr( $match[ $index ][0], $placeholders );
				}

				return '';
			},
			$replace_pattern
		);
	}

	/**
	 * @return string
	 */
	private function get_regex() {
		$at_escaped = str_replace( '@', '\\@', $this->pattern );
		$case = '';

		if ( $this->is_ignore_case() ) {
			$case = 'i';
		}

		return '@' . $at_escaped . '@s' . $case;
	}

	/**
	 * @return bool
	 */
	public function is_ignore_case() {
		return $this->case;
	}
}
