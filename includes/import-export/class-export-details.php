<?php

namespace Redirection\ImportExport;

/**
 * Shared export metadata for import/export formats.
 */
class ExportDetails {
	/**
	 * @return array{version: string, date: string}
	 */
	public static function get() {
		$version = red_get_plugin_data( dirname( __DIR__, 2 ) . '/redirection.php' );

		return [
			'version' => trim( $version['Version'] ),
			'date' => gmdate( 'r' ),
		];
	}
}
