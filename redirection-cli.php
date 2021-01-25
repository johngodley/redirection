<?php

/**
 * Implements example command.
 */
class Redirection_Cli extends WP_CLI_Command {
	private function get_group( $group_id ) {
		if ( $group_id === 0 ) {
			$groups = Red_Group::get_filtered( array() );

			if ( count( $groups['items'] ) > 0 ) {
				return $groups['items'][0]['id'];
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
	 * Import from another plugin to Redirection.
	 *
	 * Supports:
	 *   - wp-simple-redirect
	 *   - seo-redirection
	 *   - safe-redirect-manager
	 *   - wordpress-old-slugs
	 *   - rank-math
	 *   - quick-redirects
	 *
	 * ## OPTIONS
	 *
	 * <name>
	 * : The plugin name to import from (see above)
	 *
	 * [--group=<groupid>]
	 * : The group ID to import into. Defaults to the first available group.
	 *
	 * ## EXAMPLES
	 *
	 *     wp redirection plugin quick-redirects
	 */
	public function plugin( $args, $extra ) {
		include_once __DIR__ . '/models/importer.php';

		$name = $args[0];
		$group = $this->get_group( isset( $extra['group'] ) ? intval( $extra['group'], 10 ) : 0 );

		$importer = Red_Plugin_Importer::get_importer( $name );
		if ( $importer ) {
			$count = $importer->import_plugin( $group );
			WP_CLI::success( sprintf( 'Imported %d redirects from plugin %s', $count, $name ) );
			return;
		}

		WP_CLI::error( 'Invalid plugin name' );
	}

	/**
	 * Get or set a Redirection setting
	 *
	 * ## OPTIONS
	 *
	 * <name>
	 * : The setting name to get or set
	 *
	 * [--set=<value>]
	 * : The value to set
	 *
	 * ## EXAMPLES
	 *
	 *     wp redirection setting name <value>
	 */
	public function setting( $args, $extra ) {
		$name = $args[0];
		$set = isset( $extra['set'] ) ? $extra['set'] : false;

		$options = red_get_options();

		if ( ! isset( $options[ $name ] ) ) {
			WP_CLI::error( 'Unsupported setting: ' . $name );
			return;
		}

		$value = $options[ $name ];

		if ( $set ) {
			$decoded = json_decode( $set, true );
			if ( ! $decoded ) {
				$decoded = $set;
			}

			$options = [];
			$options[ $name ] = $decoded;

			$options = red_set_options( $options );
			$value = $options[ $name ];
		}

		WP_CLI::success( is_array( $value ) ? wp_json_encode( $value ) : $value );
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
	 * : The import format - csv, apache, or json. Defaults to json
	 *
	 * ## EXAMPLES
	 *
	 *     wp redirection import .htaccess --format=apache
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
			WP_CLI::error( 'Invalid import format - csv, json, or apache supported' );
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
				WP_CLI::success( 'Imported ' . $count . ' redirects as ' . $format );
			} else {
				WP_CLI::error( 'Invalid import file' );
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
			WP_CLI::error( 'Invalid export format - json, csv, apache, or nginx supported' );
			return;
		}

		$file = fopen( $args[1] === '-' ? 'php://stdout' : $args[1], 'w' );
		if ( $file ) {
			$export = Red_FileIO::export( $args[0], $format );

			if ( $export === false ) {
				// phpcs:ignore
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

	/**
	 * Perform Redirection database actions
	 *
	 * ## OPTIONS
	 *
	 * <action>
	 * : The database action to perform: install, remove, upgrade
	 *
	 * [--skip-errors]
	 * : Skip errors and keep on upgrading
	 *
	 * ## EXAMPLES
	 *
	 *     wp redirection database install
	 */
	public function database( $args, $extra ) {
		$action = false;
		$skip = isset( $extra['skip-errors'] ) ? true : false;

		if ( count( $args ) === 0 || ! in_array( $args[0], array( 'install', 'remove', 'upgrade' ), true ) ) {
			WP_CLI::error( 'Invalid database action - please use install, remove, or upgrade' );
			return;
		}

		if ( $args[0] === 'install' ) {
			Red_Database::apply_to_sites( function() {
				$latest = Red_Database::get_latest_database();
				$latest->install();

				WP_CLI::success( 'Site ' . get_current_blog_id() . ' database is installed' );
			} );

			WP_CLI::success( 'Database install finished' );
		} elseif ( $args[0] === 'upgrade' ) {
			global $wpdb;

			$wpdb->show_errors( false );

			Red_Database::apply_to_sites( function() {
				$database = new Red_Database();
				$status = new Red_Database_Status();

				if ( ! $status->needs_updating() ) {
					WP_CLI::success( 'Site ' . get_current_blog_id() . ' database is already the latest version' );
					return;
				}

				$loop = 0;

				while ( $loop < 50 ) {
					$result = $database->apply_upgrade( $status );
					$info = $status->get_json();

					if ( ! $info['inProgress'] ) {
						break;
					}

					if ( $info['result'] === 'error' ) {
						if ( $skip === false ) {
							WP_CLI::error( 'Site ' . get_current_blog_id() . ' database failed to upgrade: ' . $info['reason'] . ' - ' . $info['debug'][0] );
							return;
						}

						WP_CLI::warning( 'Site ' . get_current_blog_id() . ' database failed to upgrade: ' . $info['reason'] . ' - ' . $info['debug'][0] );
						$status->set_next_stage();
					}

					$loop++;
				}

				WP_CLI::success( 'Site ' . get_current_blog_id() . ' database upgraded' );
			} );

			WP_CLI::success( 'Database upgrade finished' );
		} elseif ( $args[0] === 'remove' ) {
			Red_Database::apply_to_sites( function() {
				$latest = Red_Database::get_latest_database();
				$latest->remove();
			} );

			WP_CLI::success( 'Database removed' );
		}
	}
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	// Register "redirection" as top-level command, and all public methods as sub-commands
	WP_CLI::add_command( 'redirection', 'Redirection_Cli' );

	add_action( Red_Flusher::DELETE_HOOK, function() {
		$flusher = new Red_Flusher();
		$flusher->flush();
	} );
}
