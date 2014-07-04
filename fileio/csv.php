<?php

class Red_Csv_File extends Red_FileIO {
	function export( array $items ) {
		$filename = 'redirection-'.date_i18n( get_option( 'date_format' ) ).'.csv';

		header( 'Content-Type: text/csv' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Content-Disposition: attachment; filename="'.$filename.'"' );

		$stdout = fopen( 'php://output', 'w' );

		fputcsv( $stdout, array( 'source', 'target', 'regex', 'type', 'code', 'match', 'hits', 'title' ) );

		foreach ( $items AS $line ) {
			$csv = array(
				$line->url,
				$line->action_data,
				$line->regex,
				$line->action_type,
				$line->action->action_code,
				$line->match->action->type,
				$line->last_count,
				$line->title,
			);

			fputcsv( $stdout, $csv );
		}
	}

	function load( $group, $data, $filename = '' ) {
		$count = 0;
		$file  = fopen( $filename, 'r' );

		if ( $file ) {
			while ( ( $csv = fgetcsv( $file, 1000, ',' ) ) ) {
				if ( $csv[0] != 'source' && $csv[1] != 'target') {
					Red_Item::create( array(
						'source' => trim( $csv[0] ),
						'target' => trim( $csv[1] ),
						'regex'  => $this->is_regex( $csv[0] ),
						'group'  => $group,
						'match'  => 'url',
						'red_action' => 'url'
					) );

					$count++;
				}
			}
		}

		return $count;
	}

	function is_regex ($url) {
		$regex  = '()[]$^?+';
		$escape = false;

		for ( $x = 0; $x < strlen( $url ); $x++ ) {

			if ( $url{$x} == '\\' )
				$escape = true;
			elseif ( strpos( $regex, $url{$x} ) !== false && !$escape )
				return true;
			else
				$escape = false;
		}

		return false;
	}
}
