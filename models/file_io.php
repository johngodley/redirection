<?php

class Red_FileIO
{
	var $items = array ();

	function export ($type)
	{
		include (dirname (__FILE__).'/../models/pager.php');

		$module = Red_Module::get (intval ($_GET['module']));
		if ($module)
		{
			include (dirname (__FILE__)."/../fileio/$type.php");

			if ($type == 'rss')
				$exporter = new Red_Rss_File ();
			else if ($type == 'xml')
				$exporter = new Red_Xml_File ();
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

			if ( $parts['extension'] == 'xml') {
				include dirname( __FILE__ ).'/../fileio/xml.php';
				$importer = new Red_Xml_File();
				$data = @file_get_contents ($file['tmp_name']);
			}
			elseif ( $parts['extension'] == 'csv' ) {
				include dirname( __FILE__ ).'/../fileio/csv.php';
				$importer = new Red_Csv_File();
				$data = '';
			}
			else {
				include dirname( __FILE__ ).'/../fileio/apache.php';
				$importer = new Red_Apache_File();
				$data = @file_get_contents ($file['tmp_name']);
			}

			return $importer->load( $group, $data, $file['tmp_name'] );
		}

		return 0;
	}

	function load ($group, $data) { }
}
