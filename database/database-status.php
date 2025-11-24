<?php

/**
 * @phpstan-type DatabaseStatus array{
 *   status: string|false,
 *   inProgress: bool,
 *   current?: string,
 *   next?: string,
 *   time?: float,
 *   manual?: array<int, string>,
 *   result?: 'ok'|'error',
 *   reason?: string|false,
 *   debug?: array<int, string>,
 *   complete?: int|float
 * }
 */
class Red_Database_Status {
	// Used in < 3.7 versions of Redirection, but since migrated to general settings
	const OLD_DB_VERSION = 'redirection_version';
	const DB_UPGRADE_STAGE = 'database_stage';

	const RESULT_OK = 'ok';
	const RESULT_ERROR = 'error';

	const STATUS_OK = 'ok';
	const STATUS_NEED_INSTALL = 'need-install';
	const STATUS_NEED_UPDATING = 'need-update';
	const STATUS_FINISHED_INSTALL = 'finish-install';
	const STATUS_FINISHED_UPDATING = 'finish-update';

	/**
	 * Current upgrade stage
	 *
	 * @var string|false
	 */
	private $stage = false;

	/**
	 * List of all upgrade stages
	 *
	 * @var array<int, string>
	 */
	private $stages = [];

	/**
	 * Current database status
	 *
	 * @var string|false
	 */
	private $status = false;

	/**
	 * Result of last operation
	 *
	 * @var string|false
	 */
	private $result = false;

	/**
	 * Reason for current status
	 *
	 * @var string|false
	 */
	private $reason = false;

	/**
	 * Debug information
	 *
	 * @var array<int, string>
	 */
	private $debug = [];

	public function __construct() {
		$this->status = self::STATUS_OK;

		if ( $this->needs_installing() ) {
			$this->status = self::STATUS_NEED_INSTALL;
		}

		$this->load_stage();

		if ( $this->needs_updating() ) {
			$this->status = self::STATUS_NEED_UPDATING;
		}
	}

	/**
	 * Load current upgrade stage from options
	 *
	 * @return void
	 */
	public function load_stage(): void {
		$settings = Red_Options::get();

		if ( isset( $settings[ self::DB_UPGRADE_STAGE ] ) ) {
			$this->stage = isset( $settings[ self::DB_UPGRADE_STAGE ]['stage'] ) ? $settings[ self::DB_UPGRADE_STAGE ]['stage'] : false;
			$this->stages = isset( $settings[ self::DB_UPGRADE_STAGE ]['stages'] ) ? $settings[ self::DB_UPGRADE_STAGE ]['stages'] : [];
			$this->status = isset( $settings[ self::DB_UPGRADE_STAGE ]['status'] ) ? $settings[ self::DB_UPGRADE_STAGE ]['status'] : false;
		}
	}

	/**
	 * Does the database need install
	 *
	 * @return bool true if needs installing, false otherwise
	 */
	public function needs_installing(): bool {
		$settings = Red_Options::get();

		if ( $settings['database'] === '' && $this->get_old_version() === false ) {
			return true;
		}

		return false;
	}

	/**
	 * Does the current database need updating to the target
	 *
	 * @return bool true if needs updating, false otherwise
	 */
	public function needs_updating(): bool {
		// We need updating if we don't need to install, and the current version is less than target version
		if ( $this->needs_installing() === false && version_compare( $this->get_current_version(), REDIRECTION_DB_VERSION, '<' ) ) {
			return true;
		}

		// Also if we're still in the process of upgrading
		if ( $this->get_current_stage() !== false && $this->status !== self::STATUS_NEED_INSTALL ) {
			return true;
		}

		return false;
	}

	/**
	 * Get current database version
	 *
	 * @return string Current database version
	 */
	public function get_current_version(): string {
		$settings = Red_Options::get();

		if ( $settings['database'] !== '' ) {
			if ( $settings['database'] === '+OK' ) {
				return REDIRECTION_DB_VERSION;
			}

			return $settings['database'];
		}

		if ( $this->get_old_version() !== false ) {
			$version = $this->get_old_version();

			// Upgrade the old value
			red_set_options( array( 'database' => $version ) );
			delete_option( self::OLD_DB_VERSION );
			$this->clear_cache();
			return $version;
		}

		return '';
	}

	/**
	 * Get old database version from legacy option
	 *
	 * @return string|false
	 */
	private function get_old_version() {
		return get_option( self::OLD_DB_VERSION );
	}

	/**
	 * Check if required database tables exist
	 *
	 * @return void
	 */
	public function check_tables_exist(): void {
		$latest = Red_Database::get_latest_database();
		$missing = $latest->get_missing_tables();

		// No tables installed - do a fresh install
		if ( count( $missing ) === count( $latest->get_all_tables() ) ) {
			delete_option( self::OLD_DB_VERSION );
			red_set_options( [ 'database' => '' ] );
			$this->clear_cache();

			$this->status = self::STATUS_NEED_INSTALL;
			$this->stop_update();
		} elseif ( count( $missing ) > 0 && version_compare( $this->get_current_version(), '2.3.3', 'ge' ) ) {
			// Some tables are missing - try and fill them in
			$latest->install();
		}
	}

	/**
	 * Does the current database support a particular version
	 *
	 * @param string $version Target version
	 * @return bool true if supported, false otherwise
	 */
	public function does_support( string $version ): bool {
		return version_compare( $this->get_current_version(), $version, 'ge' );
	}

	/**
	 * Check if last operation resulted in error
	 *
	 * @return bool
	 */
	public function is_error(): bool {
		return $this->result === self::RESULT_ERROR;
	}

	/**
	 * Set error status
	 *
	 * @param string $error Error message.
	 * @return void
	 */
	public function set_error( string $error ): void {
		global $wpdb;

		$this->result = self::RESULT_ERROR;
		$this->reason = str_replace( "\t", ' ', $error );

		if ( $wpdb->last_error ) {
			$this->debug[] = $wpdb->last_error;

			if ( strpos( $wpdb->last_error, 'command denied to user' ) !== false ) {
				$this->reason .= ' - ' . __( 'Insufficient database permissions detected. Please give your database user appropriate permissions.', 'redirection' );
			}
		}

		$latest = Red_Database::get_latest_database();
		$this->debug = array_merge( $this->debug, $latest->get_table_schema() );
		$this->debug[] = 'Stage: ' . $this->get_current_stage();
	}

	/**
	 * Set success status
	 *
	 * @param string $reason Success message.
	 * @return void
	 */
	public function set_ok( string $reason ): void {
		$this->reason = $reason;
		$this->result = self::RESULT_OK;
		$this->debug = [];
	}

	/**
	 * Stop current upgrade
	 *
	 * @return void
	 */
	public function stop_update(): void {
		$this->stage = false;
		$this->stages = [];
		$this->debug = [];

		red_set_options( [ self::DB_UPGRADE_STAGE => false ] );
		$this->clear_cache();
	}

	/**
	 * Finish upgrade process
	 *
	 * @return void
	 */
	public function finish(): void {
		$this->stop_update();

		if ( $this->status === self::STATUS_NEED_INSTALL ) {
			$this->status = self::STATUS_FINISHED_INSTALL;
		} elseif ( $this->status === self::STATUS_NEED_UPDATING ) {
			$this->status = self::STATUS_FINISHED_UPDATING;
		}
	}

	/**
	 * Get current upgrade stage
	 *
	 * @return string|false Current stage name, or false if not upgrading
	 */
	public function get_current_stage() {
		return $this->stage;
	}

	/**
	 * Move current stage on to the next
	 *
	 * @return void
	 */
	public function set_next_stage(): void {
		$this->debug = [];
		$stage = $this->get_current_stage();

		if ( $stage !== false ) {
			$stage = $this->get_next_stage( $stage );

			// Save next position
			if ( $stage !== false ) {
				$this->set_stage( $stage );
			} else {
				$this->finish();
			}
		}
	}

	/**
	 * Get current upgrade status
	 *
	 * @return DatabaseStatus Database status array
	 */
	public function get_json() {
		// Base information
		$result = [
			'status' => $this->status,
			'inProgress' => $this->stage !== false,
		];

		// Add on version status
		if ( $this->status === self::STATUS_NEED_INSTALL || $this->status === self::STATUS_NEED_UPDATING ) {
			$result = array_merge(
				$result,
				$this->get_version_upgrade(),
				[ 'manual' => $this->get_manual_upgrade() ]
			);
		}

		// Add on upgrade status
		if ( $this->is_error() ) {
			$result = array_merge( $result, $this->get_version_upgrade(), $this->get_progress_status(), $this->get_error_status() );
		} elseif ( $result['inProgress'] ) {
			$result = array_merge( $result, $this->get_progress_status() );
		} elseif ( $this->status === self::STATUS_FINISHED_INSTALL || $this->status === self::STATUS_FINISHED_UPDATING ) {
			$result['complete'] = 100;
			$result['reason'] = $this->reason;
		}

		return $result;
	}

	/**
	 * Get error status information
	 *
	 * @phpstan-return array{reason: string|false, result: 'error', debug: array<int, string>}
	 * @return array<string, mixed>
	 */
	private function get_error_status(): array {
		return [
			'reason' => $this->reason,
			'result' => self::RESULT_ERROR,
			'debug' => $this->debug,
		];
	}

	/**
	 * Get progress status information
	 *
	 * @return array{complete: int|float, result: 'ok', reason: string|false}
	 */
	private function get_progress_status() {
		$complete = 0;

		if ( $this->stage !== false ) {
			$total = count( $this->stages );
			$pos = array_search( $this->stage, $this->stages, true );

			if ( $pos !== false && $total > 0 ) {
				$complete = round( ( $pos / $total ) * 100, 1 );
			}
		}

		return [
			'complete' => $complete,
			'result' => self::RESULT_OK,
			'reason' => $this->reason,
		];
	}

	/**
	 * Get version upgrade information
	 *
	 * @phpstan-return array{current: string, next: string, time: float}
	 * @return array<string, mixed>
	 */
	private function get_version_upgrade(): array {
		return [
			'current' => $this->get_current_version() ? $this->get_current_version() : '-',
			'next' => REDIRECTION_DB_VERSION,
			'time' => microtime( true ),
		];
	}

	/**
	 * Set the status information for a database upgrade
	 *
	 * @param Red_Database_Upgrade[] $upgrades List of upgrade versions.
	 * @return void
	 */
	public function start_install( array $upgrades ) {
		$this->set_stages( $upgrades );
		$this->status = self::STATUS_NEED_INSTALL;
	}

	/**
	 * Start database upgrade process
	 *
	 * @param Red_Database_Upgrade[] $upgrades List of upgrade versions.
	 * @return void
	 */
	public function start_upgrade( array $upgrades ) {
		$this->set_stages( $upgrades );
		$this->status = self::STATUS_NEED_UPDATING;
	}

	/**
	 * Set upgrade stages
	 *
	 * @param Red_Database_Upgrade[] $upgrades List of upgrade versions.
	 * @return void
	 */
	private function set_stages( array $upgrades ) {
		$this->stages = [];

		foreach ( $upgrades as $upgrade ) {
			$upgrader = Red_Database_Upgrader::get( $upgrade );
			$this->stages = array_merge( $this->stages, array_keys( $upgrader->get_stages() ) );
		}

		if ( count( $this->stages ) > 0 ) {
			$this->set_stage( $this->stages[0] );
		}
	}

	/**
	 * @param string|false $stage
	 * @return void
	 */
	public function set_stage( $stage ): void {
		$this->stage = $stage;
		$this->save_details();
	}

	/**
	 * @return void
	 */
	private function save_details(): void {
		$stages = [
			self::DB_UPGRADE_STAGE => [
				'stage' => $this->stage,
				'stages' => $this->stages,
				'status' => $this->status,
			],
		];

		red_set_options( $stages );

		$this->clear_cache();
	}

	/**
	 * @return array<int, string>
	 */
	private function get_manual_upgrade(): array {
		$queries = [];
		$database = new Red_Database();
		$upgraders = $database->get_upgrades_for_version( $this->get_current_version(), false );

		foreach ( $upgraders as $upgrade ) {
			$upgrade = Red_Database_Upgrader::get( $upgrade );

			$stages = $upgrade->get_stages();
			foreach ( array_keys( $stages ) as $stage ) {
				$queries = array_merge( $queries, $upgrade->get_queries_for_stage( $stage ) );
			}
		}

		return $queries;
	}

	/**
	 * @param string $stage
	 * @return string|false
	 */
	private function get_next_stage( string $stage ) {
		$database = new Red_Database();
		$upgraders = $database->get_upgrades_for_version( $this->get_current_version(), $this->get_current_stage() );

		if ( count( $upgraders ) === 0 ) {
			$upgraders = $database->get_upgrades_for_version( $this->get_current_version(), false );
		}

		if ( count( $upgraders ) === 0 ) {
			return false;
		}

		$upgrader = Red_Database_Upgrader::get( $upgraders[0] );

		// Where are we in this?
		$pos = is_string( $this->stage ) ? array_search( $this->stage, $this->stages, true ) : false;

		if ( $pos === false ) {
			return false;
		}

		if ( $pos === count( $this->stages ) - 1 ) {
			$this->save_db_version( REDIRECTION_DB_VERSION );
			return false;
		}

		// Set current DB version
		$current_stages = array_keys( $upgrader->get_stages() );

		$current_position = is_string( $this->stage ) ? array_search( $this->stage, $current_stages, true ) : false;

		if ( $current_position !== false && $current_position === count( $current_stages ) - 1 && isset( $upgraders[1] ) ) {
			$this->save_db_version( $upgraders[1]->get_version() );
		}

		// Move on to next in current version
		return $this->stages[ $pos + 1 ];
	}

	/**
	 * @param string $version
	 * @return void
	 */
	public function save_db_version( string $version ): void {
		red_set_options( array( 'database' => $version ) );
		delete_option( self::OLD_DB_VERSION );

		$this->clear_cache();
	}

	/**
	 * @return void
	 */
	private function clear_cache(): void {
		if ( file_exists( WP_CONTENT_DIR . '/object-cache.php' ) && function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}
	}
}
