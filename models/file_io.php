<?php

class Red_FileIO
{
	var $items = array ();

	function export ($type)
	{
		$module = Red_Module::get (intval ($_GET['module']));
		if ($module)
		{
			include (dirname (__FILE__)."/../fileio/$type.php");

			if ($type == 'rss')
				$exporter = new Red_Rss_File ();
			else if ($type == 'csv')
				$exporter = new Red_Csv_File ();
			else if ($type == 'apache')
				$exporter = new Red_Apache_File ();

			$exporter->collect ($module);
			$exporter->feed ();
			return true;
		}

		return false;
	}

	function import( $group, $file ) {
		if ( is_uploaded_file( $file['tmp_name'] ) ) {
			$parts = pathinfo( $file['name'] );

			if ( $parts['extension'] == 'csv' ) {
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

	function load ($group, $data, $filename = '' ) { }
}
