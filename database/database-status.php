<?php

class Red_Database_Status {
	// Used in < 3.7 versions of Redirection, but since migrated to general settings
	const OLD_DB_VERSION = 'redirection_version';
	const DB_UPGRADE_STAGE = 'redirection_database_stage';

	const RESULT_OK = 'ok';
	const RESULT_ERROR = 'error';

	const STATUS_OK = 'ok';
	const STATUS_NEED_INSTALL = 'need-install';
	const STATUS_NEED_UPDATING = 'need-update';
	const STATUS_FINISHED_INSTALL = 'finish-install';
	const STATUS_FINISHED_UPDATING = 'finish-update';

	private $stage = false;
	private $stages = [];

	private $status = false;
	private $result = false;
	private $reason = false;
	private $debug = [];

	public function __construct() {
		$this->status = self::STATUS_OK;

		if ( $this->needs_installing() ) {
			$this->status = self::STATUS_NEED_INSTALL;
		} elseif ( $this->needs_updating() ) {
			$this->status = self::STATUS_NEED_UPDATING;
		}

		$info = get_option( self::DB_UPGRADE_STAGE );
		if ( $info ) {
			$this->stage = isset( $info['stage'] ) ? $info['stage'] : false;
			$this->stages = isset( $info['stages'] ) ? $info['stages'] : [];
			$this->status = isset( $info['status'] ) ? $info['status'] : false;
		}
	}

	/**
	 * Does the database need install
	 *
	 * @return bool true if needs installing, false otherwise
	 */
	public function needs_installing() {
		$settings = red_get_options();

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
	public function needs_updating() {
		// We need updating if we don't need to install, and the current version is less than target version
		if ( $this->needs_installing() === false && version_compare( $this->get_current_version(), REDIRECTION_DB_VERSION, '<' ) ) {
			return true;
		}

		// Also if we're still in the process of upgrading
		if ( $this->get_current_stage() ) {
			return true;
		}

		return false;
	}

	/**
	 * Get current database version
	 *
	 * @return string Current database version
	 */
	public function get_current_version() {
		$settings = red_get_options();

		if ( $settings['database'] !== '' ) {
			return $settings['database'];
		} elseif ( $this->get_old_version() !== false ) {
			$version = $this->get_old_version();

			// Upgrade the old value
			red_set_options( array( 'database' => $version ) );
			delete_option( self::OLD_DB_VERSION );
			$this->clear_cache();
			return $version;
		}

		return '';
	}

	private function get_old_version() {
		return get_option( self::OLD_DB_VERSION );
	}

	public function check_tables_exist() {
		$latest = Red_Database::get_latest_database();
		$missing = $latest->get_missing_tables();

		// No tables installed - do a fresh install
		if ( count( $missing ) === count( $latest->get_all_tables() ) ) {
			delete_option( Red_Database_Status::OLD_DB_VERSION );
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
	public function does_support( $version ) {
		return version_compare( $this->get_current_version(), $version, 'ge' );
	}

	public function is_error() {
		return $this->result === self::RESULT_ERROR;
	}

	public function set_error( $error ) {
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

	public function set_ok( $reason ) {
		$this->reason = $reason;
		$this->result = self::RESULT_OK;
		$this->debug = [];
	}

	/**
	 * Stop current upgrade
	 */
	public function stop_update() {
		$this->stage = false;
		$this->stages = [];
		$this->debug = [];

		delete_option( self::DB_UPGRADE_STAGE );
		$this->clear_cache();
	}

	public function finish() {
		$this->stop_update();

		if ( $this->status === self::STATUS_NEED_INSTALL ) {
			$this->status = self::STATUS_FINISHED_INSTALL;
		} elseif ( $this->status === self::STATUS_NEED_UPDATING ) {
			$this->status = self::STATUS_FINISHED_UPDATING;
		}
	}

	/**
	 * Get current upgrade stage
	 * @return string|bool Current stage name, or false if not upgrading
	 */
	public function get_current_stage() {
		return $this->stage;
	}

	/**
	 * Move current stage on to the next
	 */
	public function set_next_stage() {
		$this->debug = [];
		$stage = $this->get_current_stage();

		if ( $stage ) {
			$stage = $this->get_next_stage( $stage );

			// Save next position
			if ( $stage ) {
				$this->set_stage( $stage );
			} else {
				$this->finish();
			}
		}
	}

	/**
	 * Get current upgrade status
	 *
	 * @return array Database status array
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

	private function get_error_status() {
		return [
			'reason' => $this->reason,
			'result' => self::RESULT_ERROR,
			'debug' => $this->debug,
		];
	}

	private function get_progress_status() {
		$complete = 0;

		if ( $this->stage ) {
			$complete = round( ( array_search( $this->stage, $this->stages, true ) / count( $this->stages ) ) * 100, 1 );
		}

		return [
			'complete' => $complete,
			'result' => self::RESULT_OK,
			'reason' => $this->reason,
		];
	}

	private function get_version_upgrade() {
		return [
			'current' => $this->get_current_version() ? $this->get_current_version() : '-',
			'next' => REDIRECTION_DB_VERSION,
			'time' => microtime( true ),
		];
	}

	/**
	 * Set the status information for a database upgrade
	 */
	public function start_install( array $upgrades ) {
		$this->set_stages( $upgrades );
		$this->status = self::STATUS_NEED_INSTALL;
	}

	public function start_upgrade( array $upgrades ) {
		$this->set_stages( $upgrades );
		$this->status = self::STATUS_NEED_UPDATING;
	}

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

	public function set_stage( $stage ) {
		$this->stage = $stage;
		$this->save_details();
	}

	private function save_details() {
		update_option( self::DB_UPGRADE_STAGE, [
			'stage' => $this->stage,
			'stages' => $this->stages,
			'status' => $this->status,
		] );

		$this->clear_cache();
	}

	private function get_manual_upgrade() {
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

	private function get_next_stage( $stage ) {
		$database = new Red_Database();
		$upgraders = $database->get_upgrades_for_version( $this->get_current_version(), $this->get_current_stage() );

		if ( count( $upgraders ) === 0 ) {
			$upgraders = $database->get_upgrades_for_version( $this->get_current_version(), false );
		}

		$upgrader = Red_Database_Upgrader::get( $upgraders[0] );

		// Where are we in this?
		$pos = array_search( $this->stage, $this->stages, true );

		if ( $pos === count( $this->stages ) - 1 ) {
			$this->save_db_version( REDIRECTION_DB_VERSION );
			return false;
		}

		// Set current DB version
		$current_stages = array_keys( $upgrader->get_stages() );

		if ( array_search( $this->stage, $current_stages, true ) === count( $current_stages ) - 1 ) {
			$this->save_db_version( $upgraders[1]['version'] );
		}

		// Move on to next in current version
		return $this->stages[ $pos + 1 ];
	}

	public function save_db_version( $version ) {
		red_set_options( array( 'database' => $version ) );
		delete_option( self::OLD_DB_VERSION );

		$this->clear_cache();
	}

	private function clear_cache() {
		if ( file_exists( WP_CONTENT_DIR . '/object-cache.php' ) && function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}
	}
}
