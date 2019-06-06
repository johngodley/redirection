<?php

include_once dirname( REDIRECTION_FILE ) . '/database/database.php';

class Red_Fixer {
	public function get_json() {
		return [
			'status' => $this->get_status(),
			'debug' => $this->get_debug(),
		];
	}

	public function get_debug() {
		$status = new Red_Database_Status();

		return [
			'database' => [
				'current' => $status->get_current_version(),
				'latest' => REDIRECTION_DB_VERSION,
			],
			'ip_header' => [
				'HTTP_CF_CONNECTING_IP' => isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : false,
				'HTTP_X_FORWARDED_FOR' => isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : false,
				'REMOTE_ADDR' => isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : false,
			],
		];
	}

	public function save_debug( $name, $value ) {
		if ( $name === 'database' ) {
			$database = new Red_Database();
			$status = new Red_Database_Status();

			foreach ( $database->get_upgrades() as $upgrade ) {
				if ( $value === $upgrade['version'] ) {
					$status->finish();
					$status->save_db_version( $value );
					break;
				}
			}
		}
	}

	public function get_status() {
		global $wpdb;

		$options = red_get_options();

		$groups = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups" ), 10 );
		$bad_group = $this->get_missing();
		$monitor_group = $options['monitor_post'];
		$valid_monitor = Red_Group::get( $monitor_group ) || $monitor_group === 0;

		return [
			array_merge( [
				'id' => 'db',
				'name' => __( 'Database tables', 'redirection' ),
			], $this->get_database_status( Red_Database::get_latest_database() ) ),
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
	}

	private function get_database_status( $database ) {
		$missing = $database->get_missing_tables();

		return array(
			'status' => count( $missing ) === 0 ? 'good' : 'error',
			'message' => count( $missing ) === 0 ? __( 'All tables present', 'redirection' ) : __( 'The following tables are missing:', 'redirection' ) . ' ' . join( ',', $missing ),
		);
	}

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

	public function fix( $status ) {
		foreach ( $status as $item ) {
			if ( $item['status'] !== 'good' ) {
				$fixer = 'fix_' . $item['id'];

				if ( method_exists( $this, $fixer ) ) {
					$result = $this->$fixer();
				}

				if ( is_wp_error( $result ) ) {
					return $result;
				}
			}
		}

		return $this->get_status();
	}

	private function get_missing() {
		global $wpdb;

		return $wpdb->get_results( "SELECT {$wpdb->prefix}redirection_items.id FROM {$wpdb->prefix}redirection_items LEFT JOIN {$wpdb->prefix}redirection_groups ON {$wpdb->prefix}redirection_items.group_id = {$wpdb->prefix}redirection_groups.id WHERE {$wpdb->prefix}redirection_groups.id IS NULL" );
	}

	private function fix_db() {
		$database = Red_Database::get_latest_database();
		return $database->install();
	}

	private function fix_groups() {
		if ( Red_Group::create( 'new group', 1 ) === false ) {
			return new WP_Error( __( 'Unable to create group', 'redirection' ) );
		}

		return true;
	}

	private function fix_redirect_groups() {
		global $wpdb;

		$missing = $this->get_missing();

		foreach ( $missing as $row ) {
			$wpdb->update( $wpdb->prefix . 'redirection_items', array( 'group_id' => $this->get_valid_group() ), array( 'id' => $row->id ) );
		}
	}

	private function fix_monitor() {
		red_set_options( array( 'monitor_post' => $this->get_valid_group() ) );
	}

	private function get_valid_group() {
		$groups = Red_Group::get_all();

		return $groups[0]['id'];
	}
}
