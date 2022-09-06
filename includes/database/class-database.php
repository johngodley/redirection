<?php

namespace Redirection\Database;

require_once __DIR__ . '/class-status.php';
require_once __DIR__ . '/class-upgrader.php';

class Database {
	/**
	 * Get all upgrades for a database version
	 *
	 * @return array Array of versions from self::get_upgrades()
	 */
	public function get_upgrades_for_version( $current_version, $current_stage ) {
		if ( empty( $current_version ) ) {
			return [
				[
					'version' => REDIRECTION_DB_VERSION,
					'file' => 'latest.php',
					'class' => '\Redirection\Database\Schema\Schema_Latest',
				],
			];
		}

		$upgraders = [];
		$found = false;

		foreach ( $this->get_upgrades() as $upgrade ) {
			if ( ! $found ) {
				$upgrader = Upgrader::get( $upgrade );

				$stage_present = in_array( $current_stage, array_keys( $upgrader->get_stages() ), true );
				$same_version = $current_stage === false && version_compare( $upgrade['version'], $current_version, 'gt' );

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
	 * @param Status $status Status.
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

	public static function apply_to_sites( $callback ) {
		if ( is_multisite() && ( is_network_admin() || defined( 'WP_CLI' ) && WP_CLI ) ) {
			$total = get_sites( [ 'count' => true ] );
			$per_page = 100;

			// Paginate through all sites and apply the callback
			for ( $offset = 0; $offset < $total; $offset += $per_page ) {
				array_map( function( $site ) use ( $callback ) {
					switch_to_blog( $site->blog_id );

					$callback();

					restore_current_blog();
				}, get_sites( [ 'number' => $per_page, 'offset' => $offset ] ) );
			}

			return;
		}

		$callback();
	}

	/**
	 * Get latest database installer
	 *
	 * @return object Database\Schema\Schema_Latest
	 */
	public static function get_latest_database() {
		include_once dirname( __FILE__ ) . '/schema/schema-latest.php';

		return new Schema\Schema_Latest();
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
				'class' => '\Redirection\Database\Schema\Schema_201',
			],
			[
				'version' => '2.1.16',
				'file' => '216.php',
				'class' => '\Redirection\Database\Schema\Schema_216',
			],
			[
				'version' => '2.2',
				'file' => '220.php',
				'class' => '\Redirection\Database\Schema\Schema_220',
			],
			[
				'version' => '2.3.1',
				'file' => '231.php',
				'class' => '\Redirection\Database\Schema\Schema_231',
			],
			[
				'version' => '2.3.2',
				'file' => '232.php',
				'class' => '\Redirection\Database\Schema\Schema_232',
			],
			[
				'version' => '2.3.3',
				'file' => '233.php',
				'class' => '\Redirection\Database\Schema\Schema_233',
			],
			[
				'version' => '2.4',
				'file' => '240.php',
				'class' => '\Redirection\Database\Schema\Schema_240',
			],
			[
				'version' => '4.0',
				'file' => '400.php',
				'class' => '\Redirection\Database\Schema\Schema_400',
			],
			[
				'version' => '4.1',
				'file' => '410.php',
				'class' => '\Redirection\Database\Schema\Schema_410',
			],
			[
				'version' => '4.2',
				'file' => '420.php',
				'class' => '\Redirection\Database\Schema\Schema_420',
			],
		];
	}
}
