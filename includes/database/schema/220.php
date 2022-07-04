<?php

// Note: not localised as the messages aren't important enough
class Red_Database_220 extends Red_Database_Upgrader {
	public function get_stages() {
		return [
			'add_group_indices_220' => 'Add group indices to redirects',
			'add_log_indices_220' => 'Add indices to logs',
		];
	}

	protected function add_group_indices_220( $wpdb ) {
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX `group_idpos` (`group_id`,`position`)" );
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX `group` (`group_id`)" );
		return true;
	}

	protected function add_log_indices_220( $wpdb ) {
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD INDEX `created` (`created`)" );
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD INDEX `redirection_id` (`redirection_id`)" );
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD INDEX `ip` (`ip`)" );
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD INDEX `group_id` (`group_id`)" );
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD INDEX `module_id` (`module_id`)" );
		return true;
	}
}
