<?php

class Red_Csv_File extends Red_FileIO {
	const CSV_SOURCE = 0;
	const CSV_TARGET = 1;
	const CSV_REGEX = 2;
	const CSV_TYPE = 3;
	const CSV_CODE = 4;

	public function export( array $items ) {
		$filename = 'redirection-'.date_i18n( get_option( 'date_format' ) ).'.csv';

		header( 'Content-Type: text/csv' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Content-Disposition: attachment; filename="'.$filename.'"' );

		$stdout = fopen( 'php://output', 'w' );

		$this->output_to_file( $stdout, $items );
	}

	public function output_to_file( $handle, array $items ) {
		fputcsv( $handle, array( 'source', 'target', 'regex', 'type', 'code', 'match', 'hits', 'title' ) );

		foreach ( $items as $line ) {
			fwrite( $handle, $this->item_as_csv( $line ).PHP_EOL );
		}
	}

	public function item_as_csv( $item ) {
		$csv = array(
			$item->get_url(),
			$item->get_action_data(),
			$item->is_regex() ? 1 : 0,
			$item->get_action_type(),
			$item->get_action_code(),
			$item->get_action_type(),
			$item->get_hits(),
			$item->get_title(),
		);

		$csv = array_map( array( $this, 'escape_csv' ), $csv );
		return join( $csv, ',' );
	}

	public function escape_csv( $item ) {
		return '"'.str_replace( '"', '""', $item ).'"';
	}

	public function load( $group, $filename, $data ) {
		$file = fopen( $filename, 'r' );

		if ( $file ) {
			return $this->load_from_file( $group, $file );
		}

		return 0;
	}

	public function load_from_file( $group_id, $file ) {
		$count = 0;

		while ( ( $csv = fgetcsv( $file, 1000, ',' ) ) ) {
			$item = $this->csv_as_item( $csv, $group_id );

			if ( $item ) {
				Red_Item::create( $item );
				$count++;
			}
		}

		return $count;
	}

	private function get_valid_code( $code ) {
		if ( get_status_header_desc( $code ) !== '' ) {
			return intval( $code, 10 );
		}

		return 301;
	}

	public function csv_as_item( $csv, $group ) {
		if ( $csv[ self::CSV_SOURCE ] !== 'source' && $csv[ self::CSV_TARGET ] !== 'target' && count( $csv ) > 1 ) {
			return array(
				'source'      => trim( $csv[ self::CSV_SOURCE ] ),
				'target'      => trim( $csv[ self::CSV_TARGET ] ),
				'regex'       => isset( $csv[ self::CSV_REGEX ] ) ? $this->parse_regex( $csv[ self::CSV_REGEX ] ) : $this->is_regex( $csv[ self::CSV_SOURCE ] ),
				'group_id'    => $group,
				'match'       => 'url',
				'red_action'  => 'url',
				'action_code' => isset( $csv[ self::CSV_CODE ] ) ? $this->get_valid_code( $csv[ self::CSV_CODE ] ) : 301,
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
