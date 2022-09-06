<?php

namespace Redirection\Database\Schema;

use Redirection\Database;

// Note: not localised as the messages aren't important enough
class Database_232 extends Database\Upgrader {
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
