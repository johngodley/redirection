<?php

/**
 * There are several problems with 2.3.3 => 2.4 that this attempts to cope with:
 * - some sites have a misconfigured IP column
 * - some sites don't have any IP column
 */
class Red_Database_240 extends Red_Database_Upgrader {
	public function get_stages() {
		return [
			'convert_int_ip_to_varchar_240' => 'Convert integer IP values to support IPv6',
			'expand_log_ip_column_240' => 'Expand IP size in logs to support IPv6',
			'convert_title_to_text_240' => 'Expand size of redirect titles',
			'add_missing_index_240' => 'Add missing IP index to 404 logs',
		];
	}

	private function has_ip_index( $wpdb ) {
		$wpdb->hide_errors();
		$existing = $wpdb->get_row( "SHOW CREATE TABLE `{$wpdb->prefix}redirection_404`", ARRAY_N );
		$wpdb->show_errors();

		if ( isset( $existing[1] ) && strpos( strtolower( $existing[1] ), 'key `ip` (' ) !== false ) {
			return true;
		}

		return false;
	}

	protected function has_varchar_ip( $wpdb ) {
		$wpdb->hide_errors();
		$existing = $wpdb->get_row( "SHOW CREATE TABLE `{$wpdb->prefix}redirection_404`", ARRAY_N );
		$wpdb->show_errors();

		if ( isset( $existing[1] ) && strpos( strtolower( $existing[1] ), '`ip` varchar(45)' ) !== false ) {
			return true;
		}

		return false;
	}

	protected function has_int_ip( $wpdb ) {
		$wpdb->hide_errors();
		$existing = $wpdb->get_row( "SHOW CREATE TABLE `{$wpdb->prefix}redirection_404`", ARRAY_N );
		$wpdb->show_errors();

		if ( isset( $existing[1] ) && strpos( strtolower( $existing[1] ), '`ip` int' ) !== false ) {
			return true;
		}

		return false;
	}

	protected function convert_int_ip_to_varchar_240( $wpdb ) {
		if ( $this->has_int_ip( $wpdb ) ) {
			$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` ADD `ipaddress` VARCHAR(45) DEFAULT NULL AFTER `ip`" );
			$this->do_query( $wpdb, "UPDATE {$wpdb->prefix}redirection_404 SET ipaddress=INET_NTOA(ip)" );
			$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` DROP `ip`" );
			return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` CHANGE `ipaddress` `ip` VARCHAR(45) DEFAULT NULL" );
		}

		return true;
	}

	protected function expand_log_ip_column_240( $wpdb ) {
		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_logs` CHANGE `ip` `ip` VARCHAR(45) DEFAULT NULL" );
	}

	protected function add_missing_index_240( $wpdb ) {
		if ( $this->has_ip_index( $wpdb ) ) {
			// Remove index
			$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` DROP INDEX ip" );
		}

		// Ensure we have an IP column
		$this->convert_int_ip_to_varchar_240( $wpdb );
		if ( ! $this->has_varchar_ip( $wpdb ) ) {
			$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` ADD `ip` VARCHAR(45) DEFAULT NULL" );
		}

		// Finally add the index
		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` ADD INDEX `ip` (`ip`)" );
	}

	protected function convert_title_to_text_240( $wpdb ) {
		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_items` CHANGE `title` `title` text" );
	}
}
