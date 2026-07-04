<?php

namespace Redirection\ImportExport\Parser;

use Redirection\ImportExport\Sanitizer\CsvSanitizer;

/**
 * Parse CSV rows into redirect payloads.
 *
 * @phpstan-type CsvItem array{
 *     url: string,
 *     action_data: array{url: string},
 *     regex: bool,
 *     group_id: int,
 *     match_type: 'url',
 *     action_type: 'url'|'error',
 *     action_code: int,
 *     status?: 'enabled'|'disabled'
 * }
 */
class CsvParser {
	const CSV_SOURCE = 0;
	const CSV_TARGET = 1;
	const CSV_REGEX = 2;
	const CSV_CODE = 3;

	/**
	 * @var CsvSanitizer
	 */
	private $sanitizer;

	public function __construct( ?CsvSanitizer $sanitizer = null ) {
		$this->sanitizer = $sanitizer ? $sanitizer : new CsvSanitizer();
	}

	/**
	 * @param string $value
	 * @return string
	 */
	private function normalize_import_value( $value ) {
		return trim( $this->sanitizer->unescape( trim( $value ) ) );
	}

	/**
	 * @param array<int, string> $csv
	 * @param \Red_Group|\Redirection\ImportExport\ImportPreviewGroup|false|null $group
	 * @return CsvItem|false
	 */
	public function parse_row( array $csv, $group = null ) {
		if ( count( $csv ) <= 1 || $this->is_header_row( $csv ) ) {
			return false;
		}

		$code = isset( $csv[ self::CSV_CODE ] ) ? $this->get_valid_code( $csv[ self::CSV_CODE ] ) : 301;
		$source = $this->normalize_import_value( $csv[ self::CSV_SOURCE ] );
		$target = $this->normalize_import_value( $csv[ self::CSV_TARGET ] );

		return [
			'url' => $source,
			'action_data' => [ 'url' => $target ],
			'regex' => isset( $csv[ self::CSV_REGEX ] ) ? $this->parse_regex( $csv[ self::CSV_REGEX ] ) : $this->is_regex( $source ),
			'group_id' => is_object( $group ) && method_exists( $group, 'get_id' ) ? $group->get_id() : 0,
			'match_type' => 'url',
			'action_type' => $this->get_action_type( $code ),
			'action_code' => $code,
		];
	}

	/**
	 * @param mixed $code
	 * @return int
	 */
	private function get_valid_code( $code ) {
		if ( get_status_header_desc( $code ) !== '' ) {
			return intval( $code, 10 );
		}

		return 301;
	}

	/**
	 * @param int $code
	 * @return 'url'|'error'
	 */
	private function get_action_type( $code ) {
		if ( $code > 400 && $code < 500 ) {
			return 'error';
		}

		return 'url';
	}

	/**
	 * @param string|int $value
	 * @return bool
	 */
	private function parse_regex( $value ) {
		return intval( $value, 10 ) === 1;
	}

	/**
	 * @param string $url
	 * @return bool
	 */
	private function is_regex( $url ) {
		if ( strpbrk( $url, '()[]$^*' ) === false ) {
			return false;
		}

		return true;
	}

	/**
	 * @param array<int, string> $csv
	 * @return bool
	 */
	private function is_header_row( array $csv ) {
		$source = strtolower( trim( $csv[ self::CSV_SOURCE ] ) );
		$target = strtolower( trim( $csv[ self::CSV_TARGET ] ) );

		return in_array( $source, [ 'source', 'source url' ], true ) && in_array( $target, [ 'target', 'target url' ], true );
	}
}
