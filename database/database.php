<?php

include_once dirname( __FILE__ ) . '/database-status.php';
include_once dirname( __FILE__ ) . '/database-upgrader.php';

class Red_Database {
	// Used in < 3.7 versions of Redirection, but since migrated to general settings
	const OLD_DB_VERSION = 'redirection_version';

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
	 * @param string $version Target version
	 * @return bool true if needs updating, false otherwise
	 */
	public function needs_updating( $version ) {
		// We need updating if we don't need to install, and the current version is less than target version
		if ( $this->needs_installing() === false && version_compare( $this->get_current_version(), $version, '<' ) ) {
			return true;
		}

		// Also if we're still in the process of upgrading
		$status = new Red_Database_Status();
		if ( $status->get_current_stage() ) {
			return true;
		}

		return false;
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
			$old = $this->get_old_version();
			delete_option( self::OLD_DB_VERSION );
			return $old;
		}

		return '';
	}

	private function get_old_version() {
		return get_option( self::OLD_DB_VERSION );
	}

	/**
	 * Get all upgrades for a database version
	 *
	 * @return array Array of versions from self::get_upgrades()
	 */
	public function get_upgrades_for_version( $current_version ) {
		if ( $current_version === '' ) {
			return [ [
				'version' => REDIRECTION_DB_VERSION,
				'file' => 'latest.php',
				'class' => 'Red_Latest_Database',
			] ];
		}

		$upgraders = [];

		foreach ( $this->get_upgrades() as $upgrade ) {
			if ( version_compare( $upgrade['version'], $current_version, 'ge' ) ) {
				$upgraders[] = $upgrade;
			}
		}

		return $upgraders;
	}

	/**
	 * Apply a particular upgrade stage
	 *
	 * @return mixed Result for upgrade
	 */
	public function apply_upgrade( $stage ) {
		$status = new Red_Database_Status();
		$upgraders = $this->get_upgrades_for_version( $this->get_current_version() );

		if ( count( $upgraders ) === 0 ) {
			return new WP_Error( 'redirect', 'No upgrades found for version ' . $this->get_current_version() );
		}

		if ( $stage === false ) {
			$stage = $status->set_initial_stages( $upgraders );
		}

		// Look at first upgrade
		$upgrader = Red_Database_Upgrader::get( $upgraders[0] );

		// Perform the upgrade
		$result = $upgrader->perform_stage( $stage );

		global $wpdb;
		if ( is_wp_error( $result ) && $wpdb->last_error ) {
			$result->add_data( $wpdb->last_error );
		} elseif ( ! is_wp_error( $result ) ) {
			$status->skip_current_stage();
		}

		return $upgrader->get_reason( $stage );
	}

	/**
	 * Get latest database installer
	 *
	 * @return object Red_Latest_Database
	 */
	public static function get_latest_database() {
		include_once dirname( __FILE__ ) . '/schema/latest.php';

		return new Red_Latest_Database();
	}

	/**
	 * List of all upgrades and their associated file
	 *
	 * @return array Database upgrade array
	 */
	public function get_upgrades() {
		return [
			[
				'version' => '2.0.1',
				'file' => '201.php',
				'class' => 'Red_Database_201',
			],
			[
				'version' => '2.1.16',
				'file' => '216.php',
				'class' => 'Red_Database_216',
			],
			[
				'version' => '2.2',
				'file' => '220.php',
				'class' => 'Red_Database_220',
			],
			[
				'version' => '2.3.1',
				'file' => '231.php',
				'class' => 'Red_Database_231',
			],
			[
				'version' => '2.3.2',
				'file' => '232.php',
				'class' => 'Red_Database_232',
			],
			[
				'version' => '2.3.3',
				'file' => '233.php',
				'class' => 'Red_Database_233',
			],
			[
				'version' => '2.4',
				'file' => '240.php',
				'class' => 'Red_Database_240',
			],
		];
	}
}
