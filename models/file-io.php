<?php

abstract class Red_FileIO {
	public static function create( $type ) {
		$exporter = false;

		if ( $type === 'rss' ) {
			include_once dirname( dirname( __FILE__ ) ).'/fileio/rss.php';
			$exporter = new Red_Rss_File();
		} elseif ( $type === 'csv' ) {
			include_once dirname( dirname( __FILE__ ) ).'/fileio/csv.php';
			$exporter = new Red_Csv_File();
		} elseif ( $type === 'apache' ) {
			include_once dirname( dirname( __FILE__ ) ).'/fileio/apache.php';
			$exporter = new Red_Apache_File();
		} elseif ( $type === 'nginx' ) {
			include_once dirname( dirname( __FILE__ ) ).'/fileio/nginx.php';
			$exporter = new Red_Nginx_File();
		} elseif ( $type === 'json' ) {
			include_once dirname( dirname( __FILE__ ) ).'/fileio/json.php';
			$exporter = new Red_Json_File();
		}

		return $exporter;
	}

	public static function import( $group_id, $file ) {
		$parts = pathinfo( $file['name'] );
		$extension = isset( $parts['extension'] ) ? $parts['extension'] : '';
		$extension = strtolower( $extension );

		if ( $extension === 'csv' ) {
			include_once dirname( dirname( __FILE__ ) ).'/fileio/csv.php';
			$importer = new Red_Csv_File();
			$data = '';
		} else if ( $extension === 'json' ) {
			include_once dirname( dirname( __FILE__ ) ).'/fileio/json.php';
			$importer = new Red_Json_File();
			$data = @file_get_contents( $file['tmp_name'] );
		} else {
			include_once dirname( dirname( __FILE__ ) ).'/fileio/apache.php';
			$importer = new Red_Apache_File();
			$data = @file_get_contents( $file['tmp_name'] );
		}

		if ( $extension !== 'json' ) {
			$group = Red_Group::get( $group_id );
			if ( ! $group ) {
				return false;
			}
		}

		return $importer->load( $group_id, $file['tmp_name'], $data );
	}

	public function force_download() {
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
	}

	public static function export( $module_name_or_id, $format ) {
		$groups = $items = false;

		if ( $module_name_or_id === 'all' || $module_name_or_id === 0 ) {
			$groups = Red_Group::get_all();
			$items = Red_Item::get_all();
		} else {
			$module_name_or_id = is_numeric( $module_name_or_id ) ? $module_name_or_id : Red_Module::get_id_for_name( $module_name_or_id );
			$module = Red_Module::get( intval( $module_name_or_id, 10 ) );

			if ( $module ) {
				$groups = Red_Group::get_all_for_module( $module->get_id() );
				$items = Red_Item::get_all_for_module( $module->get_id() );
			}
		}

		$exporter = self::create( $format );
		if ( $exporter && $items !== false && $groups !== false ) {
			return array(
				'data' => $exporter->get_data( $items, $groups ),
				'total' => count( $items ),
				'exporter' => $exporter,
			);
		}

		return false;
	}

	abstract function get_data( array $items, array $groups );
	abstract function load( $group, $filename, $data );
}
