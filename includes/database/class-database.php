<?php

namespace Redirection\Database;

use Redirection\Database\Schema;

class Database {
	/**
	 * Get all upgrades for a database version
	 *
	 * @param string $current_version
	 * @param string|false $current_stage
	 * @return list<Upgrade> Array of versions from self::get_upgrades()
	 */
	public function get_upgrades_for_version( $current_version, $current_stage ) {
		if ( $current_version === '' ) {
			return [
				new Upgrade( REDIRECTION_DB_VERSION, Schema\Latest::class ),
			];
		}

		$upgraders = [];
		$found = false;

		foreach ( $this->get_upgrades() as $upgrade ) {
			if ( ! $found ) {
				$upgrader = Upgrader::get( $upgrade );

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
	 * @param Status $status Database status.
	 * @return void
	 */
	public function apply_upgrade( Status $status ) {
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
		$upgrader = Upgrader::get( $upgraders[0] );

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
	 * @return Schema\Latest Latest database installer.
	 */
	public static function get_latest_database() {
		return new Schema\Latest();
	}

	/**
	 * List of all upgrades
	 *
	 * @return list<Upgrade> Database upgrade array
	 */
	public function get_upgrades() {
		return [
			new Upgrade( '2.0.1', Schema\Upgrade201::class ),
			new Upgrade( '2.1.16', Schema\Upgrade216::class ),
			new Upgrade( '2.2', Schema\Upgrade220::class ),
			new Upgrade( '2.3.1', Schema\Upgrade231::class ),
			new Upgrade( '2.3.2', Schema\Upgrade232::class ),
			new Upgrade( '2.3.3', Schema\Upgrade233::class ),
			new Upgrade( '2.4', Schema\Upgrade240::class ),
			new Upgrade( '4.0', Schema\Upgrade400::class ),
			new Upgrade( '4.1', Schema\Upgrade410::class ),
			new Upgrade( '4.2', Schema\Upgrade420::class ),
		];
	}
}
