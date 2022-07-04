<?php

// Note: not localised as the messages aren't important enough
class Red_Database_232 extends Red_Database_Upgrader {
	public function get_stages() {
		return [
			'remove_modules_232' => 'Remove module table',
		];
	}

	protected function remove_modules_232( $wpdb ) {
		$this->do_query( $wpdb, "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_modules" );
		return true;
	}
}
