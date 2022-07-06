<?php

namespace Redirection\FileIO;

use Redirection\Group;
use Redirection\Redirect;
use Redirection\Module;

abstract class FileIO {
	public static function create( $type ) {
		$exporter = false;

		if ( $type === 'rss' ) {
			include_once __DIR__ . '/fileio-rss.php';
			$exporter = new Rss();
		} elseif ( $type === 'csv' ) {
			include_once __DIR__ . '/fileio-csv.php';
			$exporter = new Csv();
		} elseif ( $type === 'apache' ) {
			include_once __DIR__ . '/fileio-apache.php';
			$exporter = new Apache();
		} elseif ( $type === 'nginx' ) {
			include_once __DIR__ . '/fileio-nginx.php';
			$exporter = new Nginx();
		} elseif ( $type === 'json' ) {
			include_once __DIR__ . '/fileio-json.php';
			$exporter = new Json();
		}

		return $exporter;
	}

	public static function import( $group_id, $file ) {
		$parts = pathinfo( $file['name'] );
		$extension = isset( $parts['extension'] ) ? $parts['extension'] : '';
		$extension = strtolower( $extension );

		if ( $extension === 'csv' || $extension === 'txt' ) {
			include_once __DIR__ . '/fileio-csv.php';
			$importer = new Csv();
			$data = '';
		} elseif ( $extension === 'json' ) {
			include_once __DIR__ . '/fileio-json.php';
			$importer = new Json();
			$data = @file_get_contents( $file['tmp_name'] );
		} else {
			include_once __DIR__ . '/fileio-apache.php';
			$importer = new Apache();
			$data = @file_get_contents( $file['tmp_name'] );
		}

		if ( $extension !== 'json' ) {
			$group = Group\Group::get( $group_id );
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

	protected function export_filename( $extension ) {
		$name = wp_parse_url( home_url(), PHP_URL_HOST );
		$name = str_replace( '.', '-', $name );
		$date = strtolower( date_i18n( get_option( 'date_format' ) ) );
		$date = str_replace( [ ',', ' ', '--' ], '-', $date );

		return 'redirection-' . $name . '-' . $date . '.' . $extension;
	}

	public static function export( $module_name_or_id, $format ) {
		$groups = false;
		$items = false;

		if ( $module_name_or_id === 'all' || $module_name_or_id === 0 ) {
			$groups = Group\Group::get_all();
			$items = Redirect\Redirect::get_all();
		} else {
			$module_name_or_id = is_numeric( $module_name_or_id ) ? $module_name_or_id : Module\Module::get_id_for_name( $module_name_or_id );
			$module = Module\Module::get( intval( $module_name_or_id, 10 ) );

			if ( $module ) {
				$groups = Group\Group::get_all_for_module( $module->get_id() );
				$items = Redirect\Redirect::get_all_for_module( $module->get_id() );
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

	abstract public function get_data( array $items, array $groups );
	abstract public function load( $group, $filename, $data );
}
