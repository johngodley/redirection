<?php

namespace Redirection\Database\Schema;

use Redirection\Database\Database;
use Redirection\Database\Upgrader;

// Note: not localised as the messages aren't important enough
class Upgrade233 extends Upgrader {
	/**
	 * @return array<string, string>
	 */
	public function get_stages() {
		return [
			'fix_invalid_groups_233' => 'Migrate any groups with invalid module ID',
		];
	}

	/**
	 * @param \wpdb $wpdb
	 * @return bool
	 */
	protected function fix_invalid_groups_233( $wpdb ) {
		$this->do_query( $wpdb, "UPDATE {$wpdb->prefix}redirection_groups SET module_id=1 WHERE module_id > 2" );

		$latest = Database::get_latest_database();
		return $latest->create_groups( $wpdb );
	}
}
