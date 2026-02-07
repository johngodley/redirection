<?php

/**
 * Implements example command.
 */
class Redirection_Cli extends WP_CLI_Command {
	/**
	 * Resolve a group ID, or return the first available group.
	 *
	 * @param int $group_id Group ID, or 0 to auto-select the first group.
	 * @return int|false Group ID or false when not available.
	 */
	private function get_group( $group_id ) {
		if ( $group_id === 0 ) {
			$groups = Red_Group::get_filtered( array() );

			if ( count( $groups['items'] ) > 0 ) {
				return $groups['items'][0]['id'];
			}
		} else {
			$groups = Red_Group::get( $group_id );
			if ( $groups !== false ) {
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
	 *
	 * @param list<string>            $args  Positional arguments.
	 * @param array<string, mixed>    $extra Associative flags.
	 * @return void
	 */
	public function plugin( $args, $extra ) {
		include_once __DIR__ . '/models/importer.php';

		$name = $args[0];
		$group = $this->get_group( isset( $extra['group'] ) ? intval( $extra['group'], 10 ) : 0 );

		$importer = Red_Plugin_Importer::get_importer( $name );
		if ( $importer !== false && $group !== false ) {
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
	 * : The value to set. Use true/false for boolean settings, or JSON for complex values.
	 *
	 * [--verbose]
	 * : Display setting name along with value (e.g., "flag_case: true" instead of just "true")
	 *
	 * ## EXAMPLES
	 *
	 *     wp redirection setting flag_case
	 *     wp redirection setting flag_case --verbose
	 *     wp redirection setting flag_case --set=true
	 *     wp redirection setting cache_key --set=false
	 *     wp redirection setting aliases --set='["example.com"]'
	 *
	 * @param list<string>            $args  Positional arguments.
	 * @param array<string, mixed>    $extra Associative flags.
	 * @return void
	 */
	public function setting( $args, $extra ) {
		$name = $args[0];
		$set = isset( $extra['set'] ) ? $extra['set'] : null;
		$verbose = isset( $extra['verbose'] );

		$options = Red_Options::get();

		if ( ! array_key_exists( $name, $options ) ) {
			WP_CLI::error( 'Unsupported setting: ' . $name );
			return;
		}

		$old_value = $options[ $name ];

		if ( $set !== null ) {
			if ( ! is_string( $set ) ) {
				WP_CLI::error( 'No value provided for --set; please provide a value, for example: --set=true or --set=\'["example.com"]\'.' );
				return;
			}

			$decoded = $this->parse_setting_value( $set );

			$update = [];
			$update[ $name ] = $decoded;

			$options = Red_Options::save( $update );
			$new_value = array_key_exists( $name, $options ) ? $options[ $name ] : null;

			$this->display_setting_result( $name, $old_value, $new_value );
			return;
		}

		// Just display the current value
		$this->display_setting_value( $name, $old_value, $verbose );
	}

	/**
	 * Parse a setting value from CLI input.
	 *
	 * @param string $value The raw CLI value.
	 * @return mixed The parsed value.
	 */
	private function parse_setting_value( $value ) {
		// Handle explicit boolean strings
		if ( $value === 'true' ) {
			return true;
		}
		if ( $value === 'false' ) {
			return false;
		}

		// Try JSON decode for arrays/objects (but not null, which should be literal string "null")
		$decoded = json_decode( $value, true );
		if ( $decoded !== null ) {
			return $decoded;
		}

		// Return as-is (string value, including literal "null")
		return $value;
	}

	/**
	 * Display a setting value.
	 *
	 * @param string $name    Setting name.
	 * @param mixed  $value   Setting value.
	 * @param bool   $verbose Whether to include setting name in output.
	 * @return void
	 */
	private function display_setting_value( $name, $value, $verbose = false ) {
		$display = $this->format_value_for_display( $value );
		if ( $verbose ) {
			WP_CLI::success( sprintf( '%s: %s', $name, $display ) );
		} else {
			WP_CLI::success( $display );
		}
	}

	/**
	 * Display the result of setting a value.
	 *
	 * @param string $name      Setting name.
	 * @param mixed  $old_value Previous value.
	 * @param mixed  $new_value New value.
	 * @return void
	 */
	private function display_setting_result( $name, $old_value, $new_value ) {
		$old_display = $this->format_value_for_display( $old_value );
		$new_display = $this->format_value_for_display( $new_value );

		// Compare raw values to avoid issues with formatted display strings
		if ( $old_value === $new_value ) {
			WP_CLI::success( sprintf( '%s is already set to: %s', $name, $new_display ) );
		} else {
			WP_CLI::success( sprintf( '%s updated: %s → %s', $name, $old_display, $new_display ) );
		}
	}

	/**
	 * Format a value for display in CLI output.
	 *
	 * @param mixed $value The value to format.
	 * @return string Formatted string for display.
	 */
	private function format_value_for_display( $value ) {
		if ( is_bool( $value ) ) {
			return $value ? 'true' : 'false';
		}
		if ( is_array( $value ) ) {
			$encoded = wp_json_encode( $value );
			return is_string( $encoded ) ? $encoded : '[]';
		}
		if ( $value === '' ) {
			return '(empty)';
		}
		return (string) $value;
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
	 *
	 * @param list<string>            $args  Positional arguments.
	 * @param array<string, mixed>    $extra Associative flags.
	 * @return void
	 */
	public function import( $args, $extra ) {
		$format = isset( $extra['format'] ) ? $extra['format'] : 'json';
		$group = $this->get_group( isset( $extra['group'] ) ? intval( $extra['group'], 10 ) : 0 );

		if ( $group === false ) {
			WP_CLI::error( 'Invalid group' );
			return;
		}

		$importer = Red_FileIO::create( $format );

		if ( $importer === false ) {
			WP_CLI::error( 'Invalid import format - csv, json, or apache supported' );
			return;
		}

		if ( $format === 'csv' ) {
			$file = fopen( $args[0], 'r' );

			if ( $file !== false ) {
				$count = $importer->load( $group, $args[0], '' );

				WP_CLI::success( 'Imported ' . $count . ' as ' . $format );
			} else {
				WP_CLI::error( 'Invalid import file' );
			}
		} else {
			$data = @file_get_contents( $args[0] );

			if ( $data !== false ) {
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
	 *
	 * @param list<string>            $args  Positional arguments.
	 * @param array<string, mixed>    $extra Associative flags.
	 * @return void
	 */
	public function export( $args, $extra ) {
		$format = isset( $extra['format'] ) ? $extra['format'] : 'json';
		$exporter = Red_FileIO::create( $format );

		if ( $exporter === false ) {
			WP_CLI::error( 'Invalid export format - json, csv, apache, or nginx supported' );
			return;
		}

		$file = fopen( $args[1] === '-' ? 'php://stdout' : $args[1], 'w' );
		if ( $file !== false ) {
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
	 *
	 * @param list<string>            $args  Positional arguments.
	 * @param array<string, mixed>    $extra Associative flags.
	 * @return void
	 */
	public function database( $args, $extra ) {
		$skip = isset( $extra['skip-errors'] ) ? true : false;

		if ( count( $args ) === 0 || ! in_array( $args[0], array( 'install', 'remove', 'upgrade' ), true ) ) {
			WP_CLI::error( 'Invalid database action - please use install, remove, or upgrade' );
			return;
		}

		if ( $args[0] === 'install' ) {
			Red_Database::apply_to_sites(
				function () {
					$latest = Red_Database::get_latest_database();
					$latest->install();

					WP_CLI::success( 'Site ' . get_current_blog_id() . ' database is installed' );
				}
			);

			WP_CLI::success( 'Database install finished' );
		} elseif ( $args[0] === 'upgrade' ) {
			global $wpdb;

			$wpdb->show_errors( false );

			Red_Database::apply_to_sites(
				function () use ( $skip ) {
					$database = new Red_Database();
					$status = new Red_Database_Status();

					if ( ! $status->needs_updating() ) {
						WP_CLI::success( 'Site ' . get_current_blog_id() . ' database is already the latest version' );
						return;
					}

					$loop = 0;

					while ( $loop < 50 ) {
						$database->apply_upgrade( $status );
						$info = $status->get_json();

						if ( ! $info['inProgress'] ) {
							break;
						}

						if ( isset( $info['result'] ) && $info['result'] === 'error' && isset( $info['reason'] ) && isset( $info['debug'] ) ) {
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
				}
			);

			WP_CLI::success( 'Database upgrade finished' );
		} elseif ( $args[0] === 'remove' ) {
			Red_Database::apply_to_sites(
				function () {
					$latest = Red_Database::get_latest_database();
					$latest->remove();
				}
			);

			WP_CLI::success( 'Database removed' );
		}
	}
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	// Register "redirection" as top-level command, and all public methods as sub-commands
	WP_CLI::add_command( 'redirection', 'Redirection_Cli' );

	add_action(
		Red_Flusher::DELETE_HOOK,
		function () {
			$flusher = new Red_Flusher();
			$flusher->flush();
		}
	);
}
