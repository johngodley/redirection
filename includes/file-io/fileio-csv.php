<?php

namespace Redirection\FileIO;

use Redirection\Redirect;

class Csv extends FileIO {
	const CSV_SOURCE = 0;
	const CSV_TARGET = 1;
	const CSV_REGEX = 2;
	const CSV_CODE = 3;

	public function force_download() {
		parent::force_download();

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $this->export_filename( 'csv' ) . '"' );
	}

	public function get_data( array $items, array $groups ) {
		$lines = [ implode( ',', array( 'source', 'target', 'regex', 'code', 'type', 'match', 'hits', 'title', 'status' ) ) ];

		foreach ( $items as $line ) {
			$lines[] = $this->item_as_csv( $line );
		}

		return implode( PHP_EOL, $lines ) . PHP_EOL;
	}

	public function item_as_csv( $item ) {
		$data = $item->match->get_data();
		$data = isset( $data['url'] ) ? $data = $data['url'] : '/unknown';

		$csv = array(
			$item->get_url(),
			$data,
			$item->is_regex() ? 1 : 0,
			$item->get_action_code(),
			$item->get_action_type(),
			$item->get_hits(),
			$item->get_title(),
			$item->is_enabled() ? 'active' : 'disabled',
		);

		$csv = array_map( array( $this, 'escape_csv' ), $csv );
		return implode( ',', $csv );
	}

	public function escape_csv( $item ) {
		if ( is_numeric( $item ) ) {
			return $item;
		}

		return '"' . str_replace( '"', '""', $item ) . '"';
	}

	public function load( $group, $filename, $data ) {
		ini_set( 'auto_detect_line_endings', true );

		$file = fopen( $filename, 'r' );

		ini_set( 'auto_detect_line_endings', false );

		if ( $file ) {
			$separators = [
				',',
				';',
				'|',
			];

			foreach ( $separators as $separator ) {
				fseek( $file, 0 );
				$count = $this->load_from_file( $group, $file, $separator );

				if ( $count > 0 ) {
					return $count;
				}
			}
		}

		return 0;
	}

	public function load_from_file( $group_id, $file, $separator ) {
		global $wpdb;

		$count = 0;

		while ( ( $csv = fgetcsv( $file, 5000, $separator ) ) ) {
			$item = $this->csv_as_item( $csv, $group_id );

			if ( $item ) {
				$created = Redirect\Redirect::create( $item );

				// The query log can use up all the memory
				$wpdb->queries = [];

				if ( ! is_wp_error( $created ) ) {
					$count++;
				}
			}
		}

		return $count;
	}

	private function get_valid_code( $code ) {
		if ( get_status_header_desc( intval( $code, 10 ) ) !== '' ) {
			return intval( $code, 10 );
		}

		return 301;
	}

	public function csv_as_item( $csv, $group ) {
		$source = isset( $csv[ self::CSV_SOURCE ] ) ? $csv[ self::CSV_SOURCE ] : null;
		$target = isset( $csv[ self::CSV_TARGET ] ) ? $csv[ self::CSV_TARGET ] : null;

		if ( $source && $target && $source !== 'source' && $target !== 'target' ) {
			$code = $this->get_valid_code( isset( $csv[ self::CSV_CODE ] ) ? $this->get_valid_code( $csv[ self::CSV_CODE ] ) : 301 );

			return [
				'url'         => trim( $source ),
				'action_data' => array( 'url' => trim( $target ) ),
				'regex'       => isset( $csv[ self::CSV_REGEX ] ) ? $this->parse_regex( $csv[ self::CSV_REGEX ] ) : $this->is_regex( $csv[ self::CSV_SOURCE ] ),
				'group_id'    => $group,
				'match_type'  => 'url',
				'action_type' => $code > 400 && $code < 500 ? 'error' : 'url',
				'action_code' => $code,
			];
		}

		return false;
	}

	private function parse_regex( $value ) {
		return intval( $value, 10 ) === 1 ? true : false;
	}

	private function is_regex( $url ) {
		$regex = '()[]$^*';

		if ( $url && strpbrk( $url, $regex ) === false ) {
			return false;
		}

		return true;
	}
}
