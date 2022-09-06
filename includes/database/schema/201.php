<?php

namespace Redirection\Database\Schema;

use Redirection\Database;

// Note: not localised as the messages aren't important enough
class Database_201 extends Database\Upgrader {
	public function get_stages() {
		return [
			'add_title_201' => 'Add titles to redirects',
		];
	}

	protected function add_title_201( $wpdb ) {
		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD `title` varchar(50) NULL" );
	}
}
