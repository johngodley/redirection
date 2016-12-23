<?php

class Red_Csv_File extends Red_FileIO {
	public function export( array $items ) {
		$filename = 'redirection-'.date_i18n( get_option( 'date_format' ) ).'.csv';

		header( 'Content-Type: text/csv' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Content-Disposition: attachment; filename="'.$filename.'"' );

		$stdout = fopen( 'php://output', 'w' );

		fputcsv( $stdout, array( 'source', 'target', 'regex', 'type', 'code', 'match', 'hits', 'title' ) );

		foreach ( $items as $line ) {
			fwrite( $stdout, $this->item_as_csv( $line ) );
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
		$count = 0;
		$file  = fopen( $filename, 'r' );

		if ( $file ) {
			while ( ( $csv = fgetcsv( $file, 1000, ',' ) ) ) {
				$item = $this->csv_as_item( $csv, $group );

				if ( $item ) {
					Red_Item::create( $item );
					$count++;
				}
			}
		}

		return $count;
	}

	public function csv_as_item( $csv, $group ) {
		if ( $csv[0] !== 'source' && $csv[1] !== 'target' && count( $csv ) > 1 ) {
			return array(
				'source'      => trim( $csv[0] ),
				'target'      => trim( $csv[1] ),
				'regex'       => isset( $csv[2] ) ? $this->parse_regex( $csv[2] ) : $this->is_regex( $csv[0] ),
				'group_id'    => $group,
				'match'       => 'url',
				'red_action'  => 'url',
				'action_code' => isset( $csv[3] ) ? intval( $csv[3], 10 ) : 301,
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
