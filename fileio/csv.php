<?php

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
 * @phpstan-import-type GroupJson from Red_Group
 */
class Red_Csv_File extends Red_FileIO {
	const CSV_SOURCE = 0;
	const CSV_TARGET = 1;
	const CSV_REGEX = 2;
	const CSV_CODE = 3;

	public function force_download() {
		parent::force_download();

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $this->export_filename( 'csv' ) . '"' );
	}

	/**
	 * @param array<Red_Item> $items
	 * @param array<GroupJson> $groups
	 * @return string
	 */
	public function get_data( array $items, array $groups ) {
		$lines = [ implode( ',', array( 'source', 'target', 'regex', 'code', 'type', 'hits', 'title', 'status' ) ) ];

		foreach ( $items as $line ) {
			$lines[] = $this->item_as_csv( $line );
		}

		return implode( PHP_EOL, $lines ) . PHP_EOL;
	}

	/**
	 * @param Red_Item $item
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

	/**
	 * @param string|int|float $item
	 * @return string|int|float
	 */
	public function escape_csv( $item ) {
		if ( is_numeric( $item ) ) {
			return $item;
		}

		return '"' . str_replace( '"', '""', $item ) . '"';
	}

	/**
	 * @param int $group Group ID to import into.
	 * @param string $filename Path to the file to import.
	 * @param string|false $data File contents (or false if not pre-loaded).
	 * @return int
	 */
	public function load( $group, $filename, $data ) {
		$file = fopen( $filename, 'r' );

		if ( $file !== false ) {
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

	/**
	 * @param int      $group_id
	 * @param resource $file
	 * @param string   $separator
	 * @return int
	 */
	public function load_from_file( $group_id, $file, $separator ) {
		global $wpdb;

		$count = 0;
		$group = Red_Group::get( $group_id );
		if ( $group === false ) {
			return 0;
		}

		/** @var Red_Group $group */

		while ( ( $csv = fgetcsv( $file, 5000, $separator ) ) !== false ) {
			if ( $csv === null ) {
				continue;
			}

			/** @var array<int, string> $csv */
			$csv = array_map(
				function ( $v ) {
					return (string) $v;
				},
				$csv
			);
			$item = $this->csv_as_item( $csv, $group );

			if ( $item !== false && $this->item_is_valid( $item ) ) {
				$created = Red_Item::create( $item );

				// The query log can use up all the memory
				$wpdb->queries = [];

				if ( ! is_wp_error( $created ) ) {
					$count++;
				}
			}
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
	 * @param array<int, string> $csv
	 * @param Red_Group          $group
	 * @return CsvItem|false
	 */
	public function csv_as_item( $csv, Red_Group $group ) {
		if ( count( $csv ) > 1 && $csv[ self::CSV_SOURCE ] !== 'source' && $csv[ self::CSV_TARGET ] !== 'target' ) {
			$code = isset( $csv[ self::CSV_CODE ] ) ? $this->get_valid_code( $csv[ self::CSV_CODE ] ) : 301;

			return array(
				'url' => trim( $csv[ self::CSV_SOURCE ] ),
				'action_data' => array( 'url' => trim( $csv[ self::CSV_TARGET ] ) ),
				'regex' => isset( $csv[ self::CSV_REGEX ] ) ? $this->parse_regex( $csv[ self::CSV_REGEX ] ) : $this->is_regex( $csv[ self::CSV_SOURCE ] ),
				'group_id' => $group->get_id(),
				'match_type' => 'url',
				'action_type' => $this->get_action_type( $code ),
				'action_code' => $code,
				'status' => $group->is_enabled() ? 'enabled' : 'disabled',
			);
		}

		return false;
	}

	/**
	 * @param string|int $value
	 * @return bool
	 */
	private function parse_regex( $value ) {
		return intval( $value, 10 ) === 1 ? true : false;
	}

	/**
	 * @param string $url
	 * @return bool
	 */
	private function is_regex( $url ) {
		$regex = '()[]$^*';

		if ( strpbrk( $url, $regex ) === false ) {
			return false;
		}

		return true;
	}
}
