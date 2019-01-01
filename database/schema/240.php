<?php

class Red_Database_240 extends Red_Database_Upgrader {
	public function get_stages() {
		return [
			'expand_log_ip_column_240' => 'Expand IP size in logs to support IPv6',
			'convert_int_ip_to_varchar_240' => 'Convert integer IP values to support IPv6',
			'swap_ip_column_240' => 'Swap IPv4 for IPv6',
			'add_missing_index_240' => 'Add missing IP index to 404 logs',
			'convert_title_to_text_240' => 'Expand size of redirect titles',
		];
	}

	protected function expand_log_ip_column_240( $wpdb ) {
		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_logs` CHANGE `ip` `ip` VARCHAR(45) DEFAULT NULL" );
	}

	protected function convert_int_ip_to_varchar_240( $wpdb ) {
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` ADD `ipaddress` VARCHAR(45) DEFAULT NULL AFTER `ip`" );
		return $this->do_query( $wpdb, "UPDATE {$wpdb->prefix}redirection_404 SET ipaddress=INET_NTOA(ip)" );
	}

	protected function swap_ip_column_240( $wpdb ) {
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` DROP `ip`" );
		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` CHANGE `ipaddress` `ip` VARCHAR(45) DEFAULT NULL" );
	}

	protected function add_missing_index_240( $wpdb ) {
		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` ADD INDEX `ip` (`ip`)" );
	}

	protected function convert_title_to_text_240( $wpdb ) {
		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_items` CHANGE `title` `title` text" );
	}
}
