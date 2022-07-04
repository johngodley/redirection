<?php

// Note: not localised as the messages aren't important enough
class Red_Database_233 extends Red_Database_Upgrader {
	public function get_stages() {
		return [
			'fix_invalid_groups_233' => 'Migrate any groups with invalid module ID',
		];
	}

	protected function fix_invalid_groups_233( $wpdb ) {
		$this->do_query( $wpdb, "UPDATE {$wpdb->prefix}redirection_groups SET module_id=1 WHERE module_id > 2" );

		$latest = Red_Database::get_latest_database();
		return $latest->create_groups( $wpdb );
	}
}
