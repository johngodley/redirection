<?php

namespace Redirection\ImportExport;

/**
 * Base class for import/export formats.
 *
 * @phpstan-import-type GroupExport from \Red_Group
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
 *     exporter: FormatHandler
 * }
 * @phpstan-type ImportPreviewRow array{
 *     source: string,
 *     target: string,
 *     code: int,
 *     regex: bool,
 *     group: string,
 *     result: 'created'|'updated'|'ignored',
 *     redirect_id?: int
 * }
 * @phpstan-type ImportResult array{
 *     created: int,
 *     updated: int,
 *     ignored: int,
 *     groups_created: int,
 *     groups_updated: int,
 *     groups_ignored: int,
 *     logs_imported: int,
 *     errors_imported: int,
 *     settings_imported: int,
 *     preview: array<int, ImportPreviewRow>
 * }
 */
abstract class FormatHandler {
	/**
	 * @return array{version: string, date: string}
	 */
	protected function get_export_details() {
		return ExportDetails::get();
	}

	/**
	 * Set headers to force file download
	 *
	 * @return void
	 */
	public function force_download() {
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'X-Content-Type-Options: nosniff' );
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
	 * Get export data for items and groups
	 *
	 * @param array<\Red_Item> $items Redirect items to export.
	 * @param array<GroupExport> $groups Groups to export.
	 * @return string Formatted export data.
	 */
	abstract public function get_data( array $items, array $groups );

	/**
	 * Load and import data from a file
	 *
	 * @param ImportGroup $group Group resolver to import into.
	 * @param ImportRedirect $redirect Redirect saver.
	 * @param string $filename Path to the file to import.
	 * @param bool $is_dry_run Whether this is a dry run.
	 * @param array<string, mixed> $options Import options.
	 * @return ImportResult
	 */
	abstract public function load( $group, $redirect, $filename, $is_dry_run, array $options = [] );

	/**
	 * @param ImportGroup $group Group resolver.
	 * @param ImportRedirect $redirect Redirect saver.
	 * @return ImportResult
	 */
	protected function get_import_result( ImportGroup $group, ImportRedirect $redirect ) {
		return [
			'created' => $redirect->get_created(),
			'updated' => $redirect->get_updated(),
			'ignored' => $redirect->get_ignored(),
			'groups_created' => $group->get_groups_created(),
			'groups_updated' => $group->get_groups_updated(),
			'groups_ignored' => $group->get_groups_ignored(),
			'logs_imported' => 0,
			'errors_imported' => 0,
			'settings_imported' => 0,
			'preview' => $redirect->get_preview_items(),
		];
	}
}
