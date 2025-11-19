<?php

require_once __DIR__ . '/database-status.php';
require_once __DIR__ . '/database-upgrade.php';
require_once __DIR__ . '/database-upgrader.php';

class Red_Database {
	/**
	 * Get all upgrades for a database version
	 *
	 * @param string $current_version
	 * @param string|false $current_stage
	 * @return list<Red_Database_Upgrade> Array of versions from self::get_upgrades()
	 */
	public function get_upgrades_for_version( $current_version, $current_stage ) {
		if ( empty( $current_version ) ) {
			return [
				new Red_Database_Upgrade( REDIRECTION_DB_VERSION, 'latest.php', 'Red_Latest_Database' ),
			];
		}

		$upgraders = [];
		$found = false;

		foreach ( $this->get_upgrades() as $upgrade ) {
			if ( ! $found ) {
				$upgrader = Red_Database_Upgrader::get( $upgrade );

				$stage_present = is_string( $current_stage ) && in_array( $current_stage, array_keys( $upgrader->get_stages() ), true );
				$same_version = $current_stage === false && version_compare( $upgrade->get_version(), $current_version, 'gt' );

				if ( $stage_present || $same_version ) {
					$found = true;
				}
			}

			if ( $found ) {
				$upgraders[] = $upgrade;
			}
		}

		return $upgraders;
	}

	/**
	 * Apply a particular upgrade stage
	 *
	 * @return void
	 */
	public function apply_upgrade( Red_Database_Status $status ) {
		$upgraders = $this->get_upgrades_for_version( $status->get_current_version(), $status->get_current_stage() );

		if ( count( $upgraders ) === 0 ) {
			$status->set_error( 'No upgrades found for version ' . $status->get_current_version() );
			return;
		}

		if ( $status->get_current_stage() === false ) {
			if ( $status->needs_installing() ) {
				$status->start_install( $upgraders );
			} else {
				$status->start_upgrade( $upgraders );
			}
		}

		// Look at first upgrade
		$upgrader = Red_Database_Upgrader::get( $upgraders[0] );

		// Perform the upgrade
		$upgrader->perform_stage( $status );

		if ( ! $status->is_error() ) {
			$status->set_next_stage();
		}
	}

	/**
	 * Apply a callback to all sites in a multisite network, or to the current site if not multisite.
	 *
	 * @param callable $callback Callback function to apply to each site.
	 * @return void
	 */
	public static function apply_to_sites( $callback ) {
		if ( is_multisite() && ( is_network_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) ) {
			$total = get_sites( [ 'count' => true ] );
			$per_page = 100;

			// Paginate through all sites and apply the callback
			for ( $offset = 0; $offset < $total; $offset += $per_page ) {
				array_map(
					function ( $site ) use ( $callback ) {
						switch_to_blog( (int) $site->blog_id );

						$callback();

						restore_current_blog();
					},
					get_sites( [ 'number' => $per_page, 'offset' => $offset ] )
				);
			}

			return;
		}

		$callback();
	}

	/**
	 * Get latest database installer
	 *
	 * @return Red_Latest_Database Red_Latest_Database
	 */
	public static function get_latest_database() {
		include_once __DIR__ . '/schema/latest.php';

		return new Red_Latest_Database();
	}

	/**
	 * List of all upgrades and their associated file
	 *
	 * @return list<Red_Database_Upgrade> Database upgrade array
	 */
	public function get_upgrades() {
		return [
			new Red_Database_Upgrade( '2.0.1', '201.php', 'Red_Database_201' ),
			new Red_Database_Upgrade( '2.1.16', '216.php', 'Red_Database_216' ),
			new Red_Database_Upgrade( '2.2', '220.php', 'Red_Database_220' ),
			new Red_Database_Upgrade( '2.3.1', '231.php', 'Red_Database_231' ),
			new Red_Database_Upgrade( '2.3.2', '232.php', 'Red_Database_232' ),
			new Red_Database_Upgrade( '2.3.3', '233.php', 'Red_Database_233' ),
			new Red_Database_Upgrade( '2.4', '240.php', 'Red_Database_240' ),
			new Red_Database_Upgrade( '4.0', '400.php', 'Red_Database_400' ),
			new Red_Database_Upgrade( '4.1', '410.php', 'Red_Database_410' ),
			new Red_Database_Upgrade( '4.2', '420.php', 'Red_Database_420' ),
		];
	}
}
