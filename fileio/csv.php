<?php

class Red_Csv_File extends Red_FileIO {
	var $id;
	var $items;

	function collect( $module ) {
		$this->id = $module->id;

		$items = Red_Item::get_by_module( $module->id );

		if ( count( $items ) > 0 ) {
			foreach ( $items AS $item ) {
				$this->items[] = array(
					'source' => $item->url,
					'target' => ( $item->action_type == 'url' ? $item->action_data : '' ),
					'last_count' => $item->last_count
				);
			}
		}
	}

	function feed( $filename = '', $heading = '' ) {
		$filename = sprintf( __( 'module_%d.csv', 'redirection' ), $this->id );

		header( 'Content-Type: text/csv' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Content-Disposition: attachment; filename="'.$filename.'"' );

		if ( count( $this->items ) > 0 ) {
			echo "source,target,hits\r\n";

			$stdout = fopen( 'php://output', 'w' );

			foreach ( $this->items AS $line ) {
				fputcsv( $stdout, $line );
			}
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
