<?php

require_once dirname( REDIRECTION_FILE ) . '/database/database.php';

/**
 * Diagnostic and repair tool for Redirection plugin
 *
 * @phpstan-type StatusItem array{
 *     id: string,
 *     name: string,
 *     message: string,
 *     status: string
 * }
 * @phpstan-type DebugInfo array{
 *     database: array{
 *         current: string,
 *         latest: string
 *     },
 *     ip_header: array<string, string|false>
 * }
 * @phpstan-type FixerJson array{
 *     status: array<StatusItem>,
 *     debug: DebugInfo
 * }
 */
class Red_Fixer {
	const REGEX_LIMIT = 200;

	/**
	 * Get JSON representation of fixer status and debug info
	 *
	 * @return FixerJson
	 */
	public function get_json() {
		return [
			'status' => $this->get_status(),
			'debug' => $this->get_debug(),
		];
	}

	/**
	 * Get debug information
	 *
	 * @return DebugInfo
	 */
	public function get_debug() {
		$status = new Red_Database_Status();
		$ip = [];

		foreach ( Redirection_Request::get_ip_headers() as $var ) {
			$ip[ $var ] = isset( $_SERVER[ $var ] ) ? sanitize_text_field( $_SERVER[ $var ] ) : false;
		}

		return [
			'database' => [
				'current' => $status->get_current_version(),
				'latest' => REDIRECTION_DB_VERSION,
			],
			'ip_header' => $ip,
		];
	}

	/**
	 * Save debug setting
	 *
	 * @param string $name Setting name.
	 * @param string $value Setting value.
	 * @return void
	 */
	public function save_debug( $name, $value ) {
		if ( $name === 'database' ) {
			$database = new Red_Database();
			$status = new Red_Database_Status();

			foreach ( $database->get_upgrades() as $upgrade ) {
				if ( $value === $upgrade->get_version() ) {
					$status->finish();
					$status->save_db_version( $value );

					// Switch to prompt mode
					red_set_options( [ 'plugin_update' => 'prompt' ] );
					break;
				}
			}
		}
	}

	/**
	 * Get status of all diagnostic checks
	 *
	 * @return array<StatusItem>
	 */
	public function get_status() {
		global $wpdb;

		$options = Red_Options::get();

		$groups = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups" ), 10 );
		$bad_group = $this->get_missing();
		$monitor_group = $options['monitor_post'];
		$valid_monitor = Red_Group::get( $monitor_group ) !== false || $monitor_group === 0;

		$status = [
			array_merge(
				[
					'id' => 'db',
					'name' => __( 'Database tables', 'redirection' ),
				],
				$this->get_database_status( Red_Database::get_latest_database() )
			),
			[
				'name' => __( 'Valid groups', 'redirection' ),
				'id' => 'groups',
				'message' => $groups === 0 ? __( 'No valid groups, so you will not be able to create any redirects', 'redirection' ) : __( 'Valid groups detected', 'redirection' ),
				'status' => $groups === 0 ? 'problem' : 'good',
			],
			[
				'name' => __( 'Valid redirect group', 'redirection' ),
				'id' => 'redirect_groups',
				'message' => count( $bad_group ) > 0 ? __( 'Redirects with invalid groups detected', 'redirection' ) : __( 'All redirects have a valid group', 'redirection' ),
				'status' => count( $bad_group ) > 0 ? 'problem' : 'good',
			],
			[
				'name' => __( 'Post monitor group', 'redirection' ),
				'id' => 'monitor',
				'message' => $valid_monitor === false ? __( 'Post monitor group is invalid', 'redirection' ) : __( 'Post monitor group is valid', 'redirection' ),
				'status' => $valid_monitor === false ? 'problem' : 'good',
			],
			$this->get_http_settings(),
		];

		$regex_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items WHERE regex=1" );
		if ( $regex_count > self::REGEX_LIMIT ) {
			$status[] = [
				'name' => __( 'Regular Expressions', 'redirection' ),
				'id' => 'regex',
				'message' => __( 'Too many regular expressions may impact site performance', 'redirection' ),
				'status' => 'problem',
			];
		}

		return $status;
	}

	/**
	 * Get database table status
	 *
	 * @param Red_Latest_Database $database Database instance.
	 * @return array{status: string, message: string}
	 */
	private function get_database_status( $database ) {
		$missing = $database->get_missing_tables();

		return array(
			'status' => count( $missing ) === 0 ? 'good' : 'error',
			'message' => count( $missing ) === 0 ? __( 'All tables present', 'redirection' ) : __( 'The following tables are missing:', 'redirection' ) . ' ' . join( ',', $missing ),
		);
	}

	/**
	 * Get HTTP settings status
	 *
	 * @return StatusItem
	 */
	private function get_http_settings() {
		$site = wp_parse_url( get_site_url(), PHP_URL_SCHEME );
		$home = wp_parse_url( get_home_url(), PHP_URL_SCHEME );

		$message = __( 'Site and home are consistent', 'redirection' );
		if ( $site !== $home ) {
			/* translators: 1: Site URL, 2: Home URL */
			$message = sprintf( __( 'Site and home URL are inconsistent. Please correct from your Settings > General page: %1$1s is not %2$2s', 'redirection' ), get_site_url(), get_home_url() );
		}

		return array(
			'name' => __( 'Site and home protocol', 'redirection' ),
			'id' => 'redirect_url',
			'message' => $message,
			'status' => $site === $home ? 'good' : 'problem',
		);
	}

	/**
	 * Fix all issues found in status
	 *
	 * @param array<StatusItem> $status Status items to fix.
	 * @return array<StatusItem>|WP_Error Updated status or error.
	 */
	public function fix( $status ) {
		foreach ( $status as $item ) {
			if ( $item['status'] !== 'good' ) {
				$fixer = 'fix_' . $item['id'];

				$result = true;
				if ( method_exists( $this, $fixer ) ) {
					// @phpstan-ignore method.dynamicName
					$result = $this->$fixer();
				}

				if ( is_wp_error( $result ) ) {
					return $result;
				}
			}
		}

		return $this->get_status();
	}

	/**
	 * Get redirects with missing groups
	 *
	 * @return list<object{id: string}>
	 */
	private function get_missing() {
		global $wpdb;

		return $wpdb->get_results( "SELECT {$wpdb->prefix}redirection_items.id FROM {$wpdb->prefix}redirection_items LEFT JOIN {$wpdb->prefix}redirection_groups ON {$wpdb->prefix}redirection_items.group_id = {$wpdb->prefix}redirection_groups.id WHERE {$wpdb->prefix}redirection_groups.id IS NULL" );
	}

	/**
	 * Fix database tables
	 *
	 * @return bool|WP_Error
	 */
	private function fix_db() {
		$database = Red_Database::get_latest_database();
		return $database->install();
	}

	/**
	 * Fix missing groups
	 *
	 * @return bool|WP_Error
	 */
	private function fix_groups() {
		if ( Red_Group::create( 'new group', 1 ) === false ) {
			return new WP_Error( 'Unable to create group' );
		}

		return true;
	}

	/**
	 * Fix redirects with invalid groups
	 *
	 * @return void
	 */
	private function fix_redirect_groups() {
		global $wpdb;

		$missing = $this->get_missing();

		foreach ( $missing as $row ) {
			$wpdb->update( $wpdb->prefix . 'redirection_items', array( 'group_id' => $this->get_valid_group() ), array( 'id' => $row->id ) );
		}
	}

	/**
	 * Fix invalid monitor group setting
	 *
	 * @return void
	 */
	private function fix_monitor() {
		red_set_options( array( 'monitor_post' => $this->get_valid_group() ) );
	}

	/**
	 * Get a valid group ID
	 *
	 * @return int
	 */
	private function get_valid_group() {
		$groups = Red_Group::get_all();

		return $groups[0]['id'];
	}
}
