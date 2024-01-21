<?php

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

	public function get_data( array $items, array $groups ) {
		$lines = [ implode( ',', array( 'source', 'target', 'regex', 'code', 'type', 'hits', 'title', 'status' ) ) ];

		foreach ( $items as $line ) {
			$lines[] = $this->item_as_csv( $line );
		}

		return implode( PHP_EOL, $lines ) . PHP_EOL;
	}

	public function item_as_csv( $item ) {
		$data = $item->match->get_data();

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

	public function escape_csv( $item ) {
		if ( is_numeric( $item ) ) {
			return $item;
		}

		return '"' . str_replace( '"', '""', $item ) . '"';
	}

	public function load( $group, $filename, $data ) {
		$file = fopen( $filename, 'r' );

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
		$group = Red_Group::get( $group_id );
		if ( ! $group ) {
			return 0;
		}

		while ( ( $csv = fgetcsv( $file, 5000, $separator ) ) ) {
			$item = $this->csv_as_item( $csv, $group );

			if ( $item && $this->item_is_valid( $item ) ) {
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

	private function item_is_valid( array $csv ) {
		if ( strlen( $csv['url'] ) === 0 ) {
			return false;
		}

		if ( $csv['action_data']['url'] === $csv['url'] ) {
			return false;
		}

		return true;
	}

	private function get_valid_code( $code ) {
		if ( get_status_header_desc( $code ) !== '' ) {
			return intval( $code, 10 );
		}

		return 301;
	}

	private function get_action_type( $code ) {
		if ( $code > 400 && $code < 500 ) {
			return 'error';
		}

		return 'url';
	}

	public function csv_as_item( $csv, Red_Group $group ) {
		if ( count( $csv ) > 1 && $csv[ self::CSV_SOURCE ] !== 'source' && $csv[ self::CSV_TARGET ] !== 'target' ) {
			$code = isset( $csv[ self::CSV_CODE ] ) ? $this->get_valid_code( $csv[ self::CSV_CODE ] ) : 301;

			return array(
				'url'         => trim( $csv[ self::CSV_SOURCE ] ),
				'action_data' => array( 'url' => trim( $csv[ self::CSV_TARGET ] ) ),
				'regex'       => isset( $csv[ self::CSV_REGEX ] ) ? $this->parse_regex( $csv[ self::CSV_REGEX ] ) : $this->is_regex( $csv[ self::CSV_SOURCE ] ),
				'group_id'    => $group->get_id(),
				'match_type'  => 'url',
				'action_type' => $this->get_action_type( $code ),
				'action_code' => $code,
				'status'      => $group->is_enabled() ? 'enabled' : 'disabled',
			);
		}

		return false;
	}

	private function parse_regex( $value ) {
		return intval( $value, 10 ) === 1 ? true : false;
	}

	private function is_regex( $url ) {
		$regex = '()[]$^*';

		if ( strpbrk( $url, $regex ) === false ) {
			return false;
		}

		return true;
	}
}
