<?php

class Red_Database_Status {
	const DB_UPGRADE_STAGE = 'redirection_database_stage';

	/**
	 * Stop current upgrade
	 */
	public function stop_upgrade() {
		delete_option( self::DB_UPGRADE_STAGE );
	}

	/**
	 * Get current upgrade stage
	 * @return string|bool Current stage name, or false if not upgrading
	 */
	public function get_current_stage() {
		$info = get_option( self::DB_UPGRADE_STAGE );

		if ( $info === false || ! isset( $info['stage'] ) ) {
			return false;
		}

		return $info['stage'];
	}

	/**
	 * Move current stage on to the next
	 * @return string Next stage
	 */
	public function skip_current_stage() {
		$stage = $this->get_current_stage();

		if ( $stage ) {
			$next = $this->get_next_stage( $stage );

			// Save next position
			if ( $next ) {
				$this->update_stage( $next );
			} else {
				$this->stop_upgrade();
			}

			return $next;
		}

		return $stage;
	}

	/**
	 * Get current upgrade status
	 *
	 * @return array Database status array
	 */
	public function get_upgrade_status( $last_result = false ) {
		$database = new Red_Database();
		$info = get_option( self::DB_UPGRADE_STAGE );
		$stage = false;

		if ( $info ) {
			$stage = $info['stage'];
			$stages = $info['stages'];
		}

		// Base information
		$result = [
			'needUpgrade' => $database->needs_updating( REDIRECTION_DB_VERSION ),
			'needInstall' => $database->needs_installing(),
			'inProgress' => $stage !== false,
		];

		// Add on version status
		if ( $result['needUpgrade'] || $result['needInstall'] ) {
			$result = array_merge( $result, $this->get_version_upgrade( $database ) );
		}

		// Add on upgrade status
		if ( $stage !== false ) {
			$result = array_merge( $result, $this->get_upgrade_progress( $stage, $stages, $last_result ) );
		} elseif ( $last_result && ! is_wp_error( $last_result ) ) {
			$result = array_merge( $result, $this->get_finish_status( $last_result ) );
		}

		// Got an error? That overrides everything
		if ( is_wp_error( $last_result ) ) {
			$result = array_merge( $result, $this->get_error_status( $last_result ) );
		}

		return $result;
	}

	private function get_error_status( WP_Error $error ) {
		$latest = Red_Database::get_latest_database();

		$result = [
			'status' => 'error',
			'reason' => $error->get_error_message(),
			'debug' => $latest->get_table_schema(),
		];

		$error = $error->get_error_data();
		if ( ! empty( $error ) ) {
			$result['debug'] = array_merge( [ $error, '' ], $result['debug'] );
		}

		return $result;
	}

	private function get_finish_status( $last_result ) {
		return [
			'complete' => 100,
			'status' => 'ok',
			'reason' => $last_result,
		];
	}

	private function get_upgrade_progress( $stage, array $stages, $last_result ) {
		return [
			'status' => 'ok',
			'reason' => $last_result,
			'complete' => round( ( array_search( $stage, $stages, true ) / count( $stages ) ) * 100, 1 ),
		];
	}

	private function get_version_upgrade( Red_Database $database ) {
		return [
			'current' => $database->get_current_version() ? $database->get_current_version() : '-',
			'next' => REDIRECTION_DB_VERSION,
			'time' => microtime( true ),
		];
	}

	/**
	 * Set the status information for a database upgrade
	 *
	 * @return string First stage
	 */
	public function set_initial_stages( array $upgrades ) {
		$stages = [];

		foreach ( $upgrades as $upgrade ) {
			$upgrader = Red_Database_Upgrader::get( $upgrade );
			$stages = array_merge( $stages, array_keys( $upgrader->get_stages() ) );
		}

		$this->set_stage( $stages, $stages[0] );
		return $stages[0];
	}

	private function set_stage( array $stages, $stage ) {
		update_option( self::DB_UPGRADE_STAGE, array(
			'stages' => $stages,
			'stage' => $stage,
		) );
	}

	public function update_stage( $stage ) {
		$info = get_option( self::DB_UPGRADE_STAGE );

		if ( $info ) {
			$info['stage'] = $stage;
			update_option( self::DB_UPGRADE_STAGE, $info );
		}
	}

	private function get_next_stage( $stage ) {
		$info = get_option( self::DB_UPGRADE_STAGE );
		if ( ! $info ) {
			return false;
		}

		$database = new Red_Database();

		$stages = $info['stages'];
		$upgraders = $database->get_upgrades_for_version( $database->get_current_version() );
		$upgrader = Red_Database_Upgrader::get( $upgraders[0] );

		// Where are we in this?
		$pos = array_search( $stage, $stages, true );

		if ( $pos === count( $stages ) - 1 ) {
			red_set_options( array( 'database' => REDIRECTION_DB_VERSION ) );
			return false;
		}

		// Set current DB version
		$current_stages = array_keys( $upgrader->get_stages() );

		if ( array_search( $stage, $current_stages, true ) === count( $current_stages ) - 1 ) {
			red_set_options( array( 'database' => $upgraders[1]['version'] ) );
		}

		// Move on to next in current version
		return $stages[ $pos + 1 ];
	}
}
