<?php

// Note: not localised as the messages aren't important enough
class Red_Database_201 extends Red_Database_Upgrader {
	public function get_stages() {
		return [
			'add_title_201' => 'Add titles to redirects',
		];
	}

	protected function add_title_201( $wpdb ) {
		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD `title` varchar(50) NULL" );
	}
}
