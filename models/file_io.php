<?php

abstract class Red_FileIO {
	public $items = array();

	public static function create( $type ) {
		$exporter = false;

		if ( $type == 'rss' ) {
			include dirname( dirname( __FILE__ ) )."/fileio/rss.php";
			$exporter = new Red_Rss_File();
		}
		elseif ( $type == 'csv' ) {
			include dirname( dirname( __FILE__ ) )."/fileio/csv.php";
			$exporter = new Red_Csv_File();
		}
		elseif ( $type == 'apache' ) {
			include dirname( dirname( __FILE__ ) )."/fileio/apache.php";
			$exporter = new Red_Apache_File();
		}

		return $exporter;
	}

	public static function import( $group, $file ) {
		if ( is_uploaded_file( $file['tmp_name'] ) ) {
			$parts = pathinfo( $file['name'] );

			if ( isset( $parts['extension'] ) && $parts['extension'] == 'csv' ) {
				include dirname( dirname( __FILE__ ) ).'/fileio/csv.php';
				$importer = new Red_Csv_File();
				$data = '';
			}
			else {
				include dirname( dirname( __FILE__ ) ).'/fileio/apache.php';
				$importer = new Red_Apache_File();
				$data = @file_get_contents ($file['tmp_name']);
			}

			return $importer->load( $group, $data, $file['tmp_name'] );
		}

		return 0;
	}

	abstract function export( array $items );
	abstract function load( $group, $data, $filename = '' );
}
