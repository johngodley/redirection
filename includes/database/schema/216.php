<?php

// Note: not localised as the messages aren't important enough
class Red_Database_216 extends Red_Database_Upgrader {
	public function get_stages() {
		return [
			'add_group_indices_216' => 'Add indices to groups',
			'add_redirect_indices_216' => 'Add indices to redirects',
		];
	}

	protected function add_group_indices_216( $wpdb ) {
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_groups` ADD INDEX(module_id)" );
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_groups` ADD INDEX(status)" );

		return true;
	}

	protected function add_redirect_indices_216( $wpdb ) {
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX(url(191))" );
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX(status)" );
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX(regex)" );

		return true;
	}
}
