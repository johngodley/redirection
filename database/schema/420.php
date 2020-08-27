<?php

class Red_Database_420 extends Red_Database_Upgrader {
	public function get_stages() {
		return [
			'add_extra_logging' => 'Add extra logging support',
			'remove_module_id' => 'Remove module ID from logs',
			'remove_group_id' => 'Remove group ID from logs',
			'add_extra_404' => 'Add extra 404 logging support',
			'allow_null_agent' => 'Allow null log agent',
			'convert_404_url_to_text' => 'Convert 404 url to text',
		];
	}

	protected function remove_module_id( $wpdb ) {
		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_logs` DROP `module_id`" );
	}

	protected function remove_group_id( $wpdb ) {
		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_logs` DROP `group_id`" );
	}

	protected function add_extra_logging( $wpdb ) {
		// Update any URL with a double slash at the end
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD `domain` VARCHAR(255) NULL DEFAULT NULL AFTER `url`" );
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD `http_code` INT(11) unsigned NOT NULL DEFAULT 0 AFTER `referrer`" );
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD `request_method` VARCHAR(10) NULL DEFAULT NULL AFTER `http_code`" );
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD `redirect_by` VARCHAR(50) NULL DEFAULT NULL AFTER `request_method`" );

		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD `request_data` MEDIUMTEXT NULL DEFAULT NULL AFTER `request_method`" );
	}

	protected function allow_null_agent( $wpdb ) {
		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_logs` CHANGE COLUMN `agent` `agent` MEDIUMTEXT NULL" );
	}

	protected function add_extra_404( $wpdb ) {
		// Update any URL with a double slash at the end
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` ADD `domain` VARCHAR(255) NULL DEFAULT NULL AFTER `url`" );
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` ADD `http_code` INT(11) unsigned NOT NULL DEFAULT 0 AFTER `referrer`" );
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` ADD `request_method` VARCHAR(10) NULL DEFAULT NULL AFTER `http_code`" );

		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` ADD `request_data` MEDIUMTEXT NULL DEFAULT NULL AFTER `request_method`" );
	}

	protected function convert_404_url_to_text( $wpdb ) {
		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_404` CHANGE COLUMN `url` `url` MEDIUMTEXT NOT NULL" );
	}
}
