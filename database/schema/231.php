<?php

// Note: not localised as the messages aren't important enough
class Red_Database_231 extends Red_Database_Upgrader {
	public function get_stages() {
		return [
			'remove_404_module_231' => 'Remove 404 module',
			'create_404_table_231' => 'Create 404 table',
		];
	}

	protected function remove_404_module_231( $wpdb ) {
		return $this->do_query( $wpdb, "UPDATE {$wpdb->prefix}redirection_groups SET module_id=1 WHERE module_id=3" );
	}

	protected function create_404_table_231( $wpdb ) {
		$this->do_query( $wpdb, $this->get_404_table( $wpdb ) );
	}

	private function get_404_table( $wpdb ) {
		$charset_collate = $this->get_charset();

		return "CREATE TABLE `{$wpdb->prefix}redirection_404` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`created` datetime NOT NULL,
			`url` varchar(255) NOT NULL DEFAULT '',
			`agent` varchar(255) DEFAULT NULL,
			`referrer` varchar(255) DEFAULT NULL,
			`ip` int(10) unsigned NOT NULL,
			PRIMARY KEY (`id`),
			KEY `created` (`created`),
			KEY `url` (`url`(191)),
			KEY `ip` (`ip`),
			KEY `referrer` (`referrer`(191))
		  ) $charset_collate";
	}
}
