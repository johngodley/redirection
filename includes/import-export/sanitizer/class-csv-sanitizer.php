<?php

namespace Redirection\ImportExport\Sanitizer;

/**
 * Sanitize values for CSV export.
 */
class CsvSanitizer {
	const ESCAPE_PREFIX = '[FORMULA] ';

	/**
	 * @var array<int, string>
	 */
	const FULL_WIDTH_PREFIXES = array(
		'＝',
		'＋',
		'－',
		'＠',
	);

	/**
	 * Sanitize a value for CSV export.
	 *
	 * @param mixed $value CSV value.
	 * @return string
	 */
	public function escape( $value ) {
		$value = $this->normalize_value( $value );

		if ( $value === '' ) {
			return $value;
		}

		if ( strncmp( $value, self::ESCAPE_PREFIX, strlen( self::ESCAPE_PREFIX ) ) === 0 ) {
			return self::ESCAPE_PREFIX . $value;
		}

		if ( $this->is_dangerous( $value ) ) {
			// Add a plain text marker so spreadsheet apps cannot treat the value as a formula.
			// A bracketed prefix keeps accidental collisions with exported data less likely.
			return self::ESCAPE_PREFIX . $value;
		}

		return $value;
	}

	/**
	 * Remove a prefix previously added for CSV export.
	 *
	 * @param mixed $value CSV value.
	 * @return string
	 */
	public function unescape( $value ) {
		$value = $this->normalize_value( $value );
		$doubled_prefix = self::ESCAPE_PREFIX . self::ESCAPE_PREFIX;

		if ( strncmp( $value, $doubled_prefix, strlen( $doubled_prefix ) ) === 0 ) {
			return substr( $value, strlen( self::ESCAPE_PREFIX ) );
		}

		if ( strncmp( $value, self::ESCAPE_PREFIX, strlen( self::ESCAPE_PREFIX ) ) === 0 ) {
			$remainder = substr( $value, strlen( self::ESCAPE_PREFIX ) );

			if ( $this->is_dangerous( $remainder ) ) {
				return $remainder;
			}
		}

		return $value;
	}

	/**
	 * Determine if a value should be protected for CSV export.
	 *
	 * This is byte-safe and does not rely on the `/u` regex modifier, so
	 * malformed UTF-8 cannot cause the detection to fail open.
	 *
	 * @param string $value CSV value.
	 * @return bool
	 */
	private function is_dangerous( $value ) {
		if ( $value === '' ) {
			return false;
		}

		$length = strlen( $value );
		for ( $pos = 0; $pos < $length; $pos++ ) {
			$char = substr( $value, $pos, 1 );
			$ord = ord( $char );

			if ( $ord === 9 || $ord === 10 || $ord === 13 || $ord === 32 ) {
				continue;
			}

			if ( $ord <= 31 ) {
				continue;
			}

			if ( $char === '=' || $char === '+' || $char === '-' || $char === '@' ) {
				return true;
			}

			foreach ( self::FULL_WIDTH_PREFIXES as $prefix ) {
				if ( strncmp( substr( $value, $pos ), $prefix, strlen( $prefix ) ) === 0 ) {
					return true;
				}
			}

			return false;
		}

		return false;
	}

	/**
	 * Normalize a CSV value to a string.
	 *
	 * @param mixed $value CSV value.
	 * @return string
	 */
	private function normalize_value( $value ) {
		if ( $value === null ) {
			return '';
		}

		if ( is_scalar( $value ) ) {
			return (string) $value;
		}

		return '';
	}
}
