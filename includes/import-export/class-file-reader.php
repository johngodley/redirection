<?php

namespace Redirection\ImportExport;

/**
 * Read import/export files from disk.
 */
class FileReader {
	/**
	 * @param string $filename Path to file.
	 * @return string|false
	 */
	public function read( $filename ) {
		return @file_get_contents( $filename ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}

	/**
	 * @param string $filename Path to file.
	 * @return resource|false
	 */
	public function open_read( $filename ) {
		return fopen( $filename, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	}
}
