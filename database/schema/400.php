<?php

class Red_Database_400 extends Red_Database_Upgrader {
	public function get_stages() {
		return [
			'add_match_url_400' => 'Add a matched URL column',
			'add_redirect_data_400' => 'Add column to store new flags',
			'convert_existing_urls_400' => 'Convert existing URLs to new format',
		];
	}

	protected function add_match_url_400( $wpdb ) {
		$this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD `match_url` VARCHAR(2000) NULL DEFAULT NULL AFTER `url`" );
		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX `match_url` (`match_url`)" );
	}

	protected function add_redirect_data_400( $wpdb ) {
		return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD `match_data` TEXT NULL DEFAULT NULL AFTER `match_url`" );
	}

	protected function convert_existing_urls_400( $wpdb ) {
		// All regex get match_url=regex
		$this->do_query( $wpdb, "UPDATE `{$wpdb->prefix}redirection_items` SET match_url='regex' WHERE regex=1" );

		// Lowercase, trim trailing, and remove query params
		$this->do_query( $wpdb, "UPDATE `{$wpdb->prefix}redirection_items` SET match_url=TRIM(TRAILING '/' FROM SUBSTRING_INDEX(LOWER(url), '?', 1)) WHERE regex=0" );

		// Any URL that is now empty becomes /
		return $this->do_query( $wpdb, "UPDATE `{$wpdb->prefix}redirection_items` SET match_url='/' WHERE match_url=''" );
	}
}
