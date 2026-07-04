<?php

namespace Redirection\ImportExport\Format;

use Redirection\ImportExport\FormatHandler;
use Redirection\ImportExport\FileReader;
use Redirection\ImportExport\ImportGroup;
use Redirection\ImportExport\ImportRedirect;
use Redirection\ImportExport\Parser\CsvParser;
use Redirection\ImportExport\Sanitizer\CsvSanitizer;

/**
 * CSV import/export handler
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
 * @phpstan-import-type GroupExport from \Red_Group
 * @phpstan-import-type ImportResult from \Redirection\ImportExport\FormatHandler
 */
class Csv extends FormatHandler {
	/**
	 * @var CsvParser
	 */
	private $parser;

	/**
	 * @var FileReader
	 */
	private $files;

	/**
	 * @var CsvSanitizer
	 */
	private $sanitizer;

	/**
	 * @param CsvParser|null $parser CSV row parser.
	 * @param FileReader|null $files File reader.
	 * @param CsvSanitizer|null $sanitizer CSV sanitizer.
	 */
	public function __construct( ?CsvParser $parser = null, ?FileReader $files = null, ?CsvSanitizer $sanitizer = null ) {
		$this->sanitizer = $sanitizer ? $sanitizer : new CsvSanitizer();
		$this->parser = $parser ? $parser : new CsvParser( $this->sanitizer );
		$this->files = $files ? $files : new FileReader();
	}

	public function force_download() {
		parent::force_download();

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $this->export_filename( 'csv' ) . '"' );
	}

	/**
	 * @param array<\Red_Item> $items
	 * @param array<GroupExport> $groups
	 * @return string
	 */
	public function get_data( array $items, array $groups ) {
		$lines = [ implode( ',', [ 'source', 'target', 'regex', 'code', 'type', 'hits', 'title', 'status' ] ) ];

		foreach ( $items as $line ) {
			$lines[] = $this->item_as_csv( $line );
		}

		return implode( PHP_EOL, $lines ) . PHP_EOL;
	}

	/**
	 * @param \Red_Item $item
	 * @return string
	 */
	public function item_as_csv( $item ) {
		$data = [];

		if ( $item->match !== null ) {
			$data = $item->match->get_data();
		}

		if ( isset( $data['url'] ) ) {
			$data = $data['url'];
		} else {
			$data = '/unknown';
		}

		if ( $item->get_action_code() > 400 && $item->get_action_code() < 500 ) {
			$data = '';
		}

		$csv = [
			$item->get_url(),
			$data,
			$item->is_regex() ? 1 : 0,
			$item->get_action_code(),
			$item->get_action_type(),
			$item->get_hits(),
			$item->get_title(),
			$item->is_enabled() ? 'active' : 'disabled',
		];

		$csv = array_map( [ $this, 'escape_csv' ], $csv );
		return implode( ',', $csv );
	}

	/**
	 * @param string|int|float $item
	 * @return string|int|float
	 */
	public function escape_csv( $item ) {
		if ( is_numeric( $item ) ) {
			return $item;
		}

		$item = $this->sanitizer->escape( $item );

		return '"' . str_replace( '"', '""', $item ) . '"';
	}

	/**
	 * @param ImportGroup $group Group resolver to import into.
	 * @param ImportRedirect $redirect Redirect saver.
	 * @param string $filename Path to the file to import.
	 * @param bool $is_dry_run Whether this is a dry run.
	 * @return ImportResult
	 */
	public function load( $group, $redirect, $filename, $is_dry_run, array $options = [] ) {
		unset( $options );
		$file = $this->files->open_read( $filename );

		if ( $file === false ) {
			return $this->get_import_result( $group, $redirect );
		}

		try {
			foreach ( $this->get_separators() as $separator ) {
				$before = $redirect->get_total_handled();
				fseek( $file, 0 );
				$count = $this->load_from_file( $group, $file, $separator, $is_dry_run, $redirect );
				$after = $redirect->get_total_handled();

				if ( $count > 0 || $after > $before ) {
					return $this->get_import_result( $group, $redirect );
				}
			}

			return $this->get_import_result( $group, $redirect );
		} finally {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Import reads from a temporary uploaded file handle.
			fclose( $file );
		}
	}

	/**
	 * @return array<int, string>
	 */
	private function get_separators() {
		return [
			',',
			';',
			'|',
		];
	}

	/**
	 * @param ImportGroup $group
	 * @param resource $file
	 * @param string   $separator
	 * @param bool $is_dry_run
	 * @param ImportRedirect|null $redirect
	 * @return int
	 */
	public function load_from_file( $group, $file, $separator, $is_dry_run = false, $redirect = null ) {
		global $wpdb;

		$count = 0;
		$csv = fgetcsv( $file, 5000, $separator, '"', '\\' );

		while ( $csv !== false ) {
			if ( $csv === null ) {
				$csv = fgetcsv( $file, 5000, $separator, '"', '\\' );
				continue;
			}

			/** @var array<int, string> $csv */
			$csv = array_map(
				static function ( $value ) {
					return (string) $value;
				},
				$csv
			);
			if ( $redirect !== null ) {
				$item = $this->csv_as_item( $csv );
			} else {
				$resolved_group = $group->get_group();
				if ( $resolved_group === false ) {
					return 0;
				}

				$item = $this->csv_as_item( $csv, $resolved_group );
			}

			if ( $item !== false && $this->item_is_valid( $item ) ) {
				if ( $redirect !== null ) {
					$before = $redirect->get_created() + $redirect->get_updated();

					if ( $redirect->save( $item, $group ) && ( $redirect->get_created() + $redirect->get_updated() ) > $before ) {
						$count++;
					}
				} elseif ( $is_dry_run ) {
					$count++;
				} else {
					$created = \Red_Item::create( $item );

					if ( ! is_wp_error( $created ) ) {
						$count++;
					}
				}

				// The query log can use up all the memory
				$wpdb->queries = [];
			}

			$csv = fgetcsv( $file, 5000, $separator, '"', '\\' );
		}

		return $count;
	}

	/**
	 * @param CsvItem $csv
	 * @return bool
	 */
	private function item_is_valid( array $csv ) {
		if ( strlen( $csv['url'] ) === 0 ) {
			return false;
		}

		if ( $csv['action_data']['url'] === $csv['url'] ) {
			return false;
		}

		return true;
	}

	/**
	 * @param array<int, string> $csv
	 * @param \Red_Group|\Redirection\ImportExport\ImportPreviewGroup|false|null $group
	 * @return CsvItem|false
	 */
	public function csv_as_item( $csv, $group = null ) {
		return $this->parser->parse_row( $csv, $group );
	}
}
