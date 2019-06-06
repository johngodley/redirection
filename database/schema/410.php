<?php

class Red_Database_410 extends Red_Database_Upgrader {
	public function get_stages() {
		return [
			'handle_double_slash' => 'Support double-slash URLs',
		];
	}

	protected function handle_double_slash( $wpdb ) {
		// Update any URL with a double slash at the end
		$this->do_query( $wpdb, "UPDATE `{$wpdb->prefix}redirection_items` SET match_url=LOWER(LEFT(SUBSTRING_INDEX(url, '?', 1),LENGTH(SUBSTRING_INDEX(url, '?', 1)) - 1)) WHERE RIGHT(SUBSTRING_INDEX(url, '?', 1), 2) = '//' AND regex=0" );

		// Any URL that is now empty becomes /
		return $this->do_query( $wpdb, "UPDATE `{$wpdb->prefix}redirection_items` SET match_url='/' WHERE match_url=''" );
	}
}
