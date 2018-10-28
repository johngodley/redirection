<?php

/**
 * Implements example command.
 */
class Redirection_Cli extends WP_CLI_Command {
	private function get_group( $group_id ) {
		if ( $group_id === 0 ) {
			$groups = Red_Group::get_filtered( array() );

			if ( count( $groups['items'] ) > 0 ) {
				return $groups['items'][ 0 ]['id'];
			}
		} else {
			$groups = Red_Group::get( $group_id );
			if ( $groups ) {
				return $group_id;
			}
		}

		return false;
	}

	/**
	 * Import redirections from a JSON, CSV, or .htaccess file
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : The name of the file to import.
	 *
	 * [--group=<groupid>]
	 * : The group ID to import into. Defaults to the first available group. JSON
	 *   contains it's own group
	 *
	 * [--format=<importformat>]
	 * : The import format - csv, htaccess, or json. Defaults to json
	 *
	 * ## EXAMPLES
	 *
	 *     wp redirection import .htaccess --format=htaccess
	 */
	public function import( $args, $extra ) {
		$format = isset( $extra['format'] ) ? $extra['format'] : 'json';
		$group = $this->get_group( isset( $extra['group'] ) ? intval( $extra['group'], 10 ) : 0 );

		if ( ! $group ) {
			WP_CLI::error( 'Invalid group' );
			return;
		}

		$importer = Red_FileIO::create( $format );

		if ( ! $importer ) {
			WP_CLI::error( 'Invalid import format - csv, json, or htaccess supported' );
			return;
		}

		if ( $format === 'csv' ) {
			$file = fopen( $args[0], 'r' );

			if ( $file ) {
				$count = $importer->load( $group, $args[0], '' );
				WP_CLI::success( 'Imported ' . $count . ' as ' . $format );
			} else {
				WP_CLI::error( 'Invalid import file' );
			}
		} else {
			$data = @file_get_contents( $args[0] );
			if ( $data ) {
				$count = $importer->load( $group, $args[0], $data );
				WP_CLI::success( 'Imported ' . $count . ' as ' . $format );
			}
		}
	}

	/**
	 * Export redirections to a CSV, JSON, .htaccess, or rewrite.rules file
	 *
	 * ## OPTIONS
	 *
	 * <module>
	 * : The module to export - wordpress, apache, nginx, or all
	 *
	 * <filename>
	 * : The file to export to, or - for stdout
	 *
	 * [--format=<exportformat>]
	 * : The export format. One of json, csv, apache, or nginx. Defaults to json
	 *
	 * ## EXAMPLES
	 *
	 *     wp redirection export wordpress --format=apache
	 */
	public function export( $args, $extra ) {
		$format = isset( $extra['format'] ) ? $extra['format'] : 'json';
		$exporter = Red_FileIO::create( $format );

		if ( ! $exporter ) {
			WP_CLI::error( 'Invalid export format - json, csv, htaccess, or nginx supported' );
			return;
		}

		$file = fopen( $args[1] === '-' ? 'php://stdout' : $args[1], 'w' );
		if ( $file ) {
			$export = Red_FileIO::export( $args[0], $format );

			if ( $export === false ) {
				WP_CLI::error( 'Invalid module - must be wordpress, apache, nginx, or all' );
				return;
			}

			fwrite( $file, $export['data'] );
			fclose( $file );

			WP_CLI::success( 'Exported ' . $export['total'] . ' to ' . $format );
		} else {
			WP_CLI::error( 'Invalid output file' );
		}
	}
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'redirection import', array( 'Redirection_Cli', 'import' ) );
	WP_CLI::add_command( 'redirection export', array( 'Redirection_Cli', 'export' ) );

	add_action( Red_Flusher::DELETE_HOOK, function() {
		$flusher = new Red_Flusher();
		$flusher->flush();
	} );
}
