<?php

/**
 * Note that some installations have the 2.4 tables setup, but in a slightly different way, and with the DB version set to 2.3.3
 * Unsure why this would happen, but it means we get an error when upgrading
 * Try and detect this and skip the upgrade
 */
class Red_Database_240 extends Red_Database_Upgrader {
	private $existing_index = false;
	private $existing_column = false;

	public function get_stages() {
		return [
			'detect_existing_240' => 'Detect existing 2.4 upgrade',
			'expand_log_ip_column_240' => 'Expand IP size in logs to support IPv6',
			'convert_int_ip_to_varchar_240' => 'Convert integer IP values to support IPv6',
			'swap_ip_column_240' => 'Swap IPv4 for IPv6',
			'add_missing_index_240' => 'Add missing IP index to 404 logs',
			'convert_title_to_text_240' => 'Expand size of redirect titles',
		];
	}

	protected function detect_existing_240( $wpdb ) {
		$existing = $wpdb->get_row( "SHOW CREATE TABLE `{$wpdb->prefix}redirection_404`", ARRAY_N );

		if ( isset( $existing[1] ) ) {
			if ( strpos( strtolower( $existing[1] ), 'key `ip` (`id`)' ) !== false ) {
				// Incorrect index exists for 'id', but called 'ip' - remove so add_missing_index_240 will work
				$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` drop index ip" );
			}

			if ( strpos( strtolower( $existing[1] ), '`ip` varchar(45)' ) !== false ) {
				// IP as varchar exists - don't recreate
				$this->existing_column = true;
			}
		}

		return true;
	}

	protected function expand_log_ip_column_240( $wpdb ) {
		if ( ! $this->existing_column ) {
			return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_logs` CHANGE `ip` `ip` VARCHAR(45) DEFAULT NULL" );
		}

		return true;
	}

	protected function convert_int_ip_to_varchar_240( $wpdb ) {
		if ( ! $this->existing_column ) {
			$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` ADD `ipaddress` VARCHAR(45) DEFAULT NULL AFTER `ip`" );
			return $this->do_query( $wpdb, "UPDATE {$wpdb->prefix}redirection_404 SET ipaddress=INET_NTOA(ip)" );
		}

		return true;
	}

	protected function swap_ip_column_240( $wpdb ) {
		if ( ! $this->existing_column ) {
			$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` DROP `ip`" );
			return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` CHANGE `ipaddress` `ip` VARCHAR(45) DEFAULT NULL" );
		}

		return true;
	}

	protected function add_missing_index_240( $wpdb ) {
		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` ADD INDEX `ip` (`ip`)" );
	}

	protected function convert_title_to_text_240( $wpdb ) {
		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_items` CHANGE `title` `title` text" );
	}
}
