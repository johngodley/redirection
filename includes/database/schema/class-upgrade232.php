<?php

namespace Redirection\Database\Schema;

use Redirection\Database\Upgrader;

// Note: not localised as the messages aren't important enough
class Upgrade232 extends Upgrader {
	/**
	 * @return array<string, string>
	 */
	public function get_stages() {
		return [
			'remove_modules_232' => 'Remove module table',
		];
	}

	/**
	 * @param \wpdb $wpdb
	 * @return bool
	 */
	protected function remove_modules_232( $wpdb ) {
		$this->do_query( $wpdb, "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_modules" );
		return true;
	}
}
