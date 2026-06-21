<?php

namespace Redirection\FileIO;

use Redirection\FileIO\Format\Apache;
use Redirection\FileIO\Format\Csv;
use Redirection\FileIO\Format\Json;
use Redirection\FileIO\Format\Nginx;
use Redirection\FileIO\Format\Rss;

/**
 * Base class for file import/export operations
 *
 * @phpstan-import-type GroupJson from \Red_Group
 * @phpstan-type UploadedFile array{
 *     name: string,
 *     type: string,
 *     tmp_name: string,
 *     error: int,
 *     size: int
 * }
 * @phpstan-type ExportResult array{
 *     data: string,
 *     total: int,
 *     exporter: FileIO
 * }
 */
abstract class FileIO {
	/**
	 * Create a file IO handler for the specified type
	 *
	 * @param string $type File format type (rss, csv, apache, nginx, json).
	 * @return FileIO|false
	 */
	public static function create( $type ) {
		$exporter = false;

		if ( $type === 'rss' ) {
			$exporter = new Rss();
		} elseif ( $type === 'csv' ) {
			$exporter = new Csv();
		} elseif ( $type === 'apache' ) {
			$exporter = new Apache();
		} elseif ( $type === 'nginx' ) {
			$exporter = new Nginx();
		} elseif ( $type === 'json' ) {
			$exporter = new Json();
		}

		return $exporter;
	}

	/**
	 * Import redirects from an uploaded file
	 *
	 * @param int $group_id Group ID to import into.
	 * @param UploadedFile $file Uploaded file data from $_FILES.
	 * @return int
	 */
	public static function import( $group_id, $file ) {
		$parts = pathinfo( $file['name'] );
		$extension = isset( $parts['extension'] ) ? $parts['extension'] : '';
		$extension = strtolower( $extension );

		if ( $extension === 'csv' || $extension === 'txt' ) {
			$importer = new Csv();
			$data = '';
		} elseif ( $extension === 'json' ) {
			$importer = new Json();
			$data = @file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		} else {
			$importer = new Apache();
			$data = @file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		}

		if ( $extension !== 'json' ) {
			$group = \Red_Group::get( $group_id );
			if ( $group === false ) {
				return 0;
			}
		}

		return $importer->load( $group_id, $file['tmp_name'], $data );
	}

	/**
	 * Set headers to force file download
	 *
	 * @return void
	 */
	public function force_download() {
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
	}

	/**
	 * Generate export filename
	 *
	 * @param string $extension File extension (without dot).
	 * @return string Generated filename.
	 */
	protected function export_filename( $extension ) {
		$name = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( $name === false || $name === null || $name === '' ) {
			$name = 'export';
		}

		$name = sanitize_text_field( $name );
		$name = str_replace( '.', '-', $name );
		$date = strtolower( date_i18n( get_option( 'date_format' ) ) );
		$date = str_replace( [ ',', ' ', '--' ], '-', $date );

		return 'redirection-' . $name . '-' . $date . '.' . sanitize_text_field( $extension );
	}

	/**
	 * Export redirects to a file format
	 *
	 * @param string|int $module_name_or_id Module name, ID, or 'all' for all modules.
	 * @param string $format Export format (rss, csv, apache, nginx, json).
	 * @return ExportResult|false Export data or false on failure.
	 */
	public static function export( $module_name_or_id, $format ) {
		$groups = false;
		$items = false;

		if ( $module_name_or_id === 'all' || $module_name_or_id === 0 ) {
			$groups = \Red_Group::get_all();
			$items = \Red_Item::get_all();
		} else {
			$module_name_or_id = is_numeric( $module_name_or_id ) ? $module_name_or_id : \Red_Module::get_id_for_name( $module_name_or_id );
			$module = \Red_Module::get( intval( $module_name_or_id, 10 ) );

			if ( $module !== false ) {
				$groups = \Red_Group::get_all_for_module( $module->get_id() );
				$items = \Red_Item::get_all_for_module( $module->get_id() );
			}
		}

		$exporter = self::create( $format );
		if ( $exporter !== false && $items !== false && $groups !== false ) {
			return [
				'data' => $exporter->get_data( $items, $groups ),
				'total' => count( $items ),
				'exporter' => $exporter,
			];
		}

		return false;
	}

	/**
	 * Get export data for items and groups
	 *
	 * @param array<\Red_Item> $items Redirect items to export.
	 * @param array<GroupJson> $groups Groups to export.
	 * @return string Formatted export data.
	 */
	abstract public function get_data( array $items, array $groups );

	/**
	 * Load and import data from a file
	 *
	 * @param int $group Group ID to import into.
	 * @param string $filename Path to the file to import.
	 * @param string|false $data File contents (or false if not pre-loaded).
	 * @return int
	 */
	abstract public function load( $group, $filename, $data );
}

if ( ! class_exists( 'Red_FileIO', false ) ) {
	\class_alias( '\Redirection\FileIO\FileIO', 'Red_FileIO' );
}
