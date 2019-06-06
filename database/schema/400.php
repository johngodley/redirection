<?php

class Red_Database_400 extends Red_Database_Upgrader {
	public function get_stages() {
		return [
			'add_match_url_400' => 'Add a matched URL column',
			'add_match_url_index' => 'Add match URL index',
			'add_redirect_data_400' => 'Add column to store new flags',
			'convert_existing_urls_400' => 'Convert existing URLs to new format',
		];
	}

	private function has_column( $wpdb, $column ) {
		$existing = $wpdb->get_row( "SHOW CREATE TABLE `{$wpdb->prefix}redirection_items`", ARRAY_N );

		if ( isset( $existing[1] ) && strpos( strtolower( $existing[1] ), strtolower( $column ) ) !== false ) {
			return true;
		}

		return false;
	}

	private function has_match_index( $wpdb ) {
		$existing = $wpdb->get_row( "SHOW CREATE TABLE `{$wpdb->prefix}redirection_items`", ARRAY_N );

		if ( isset( $existing[1] ) && strpos( strtolower( $existing[1] ), 'key `match_url' ) !== false ) {
			return true;
		}

		return false;
	}

	protected function add_match_url_400( $wpdb ) {
		if ( ! $this->has_column( $wpdb, '`match_url` varchar(2000)' ) ) {
			return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD `match_url` VARCHAR(2000) NULL DEFAULT NULL AFTER `url`" );
		}

		return true;
	}

	protected function add_match_url_index( $wpdb ) {
		if ( ! $this->has_match_index( $wpdb ) ) {
			return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX `match_url` (`match_url`(191))" );
		}
	}

	protected function add_redirect_data_400( $wpdb ) {
		if ( ! $this->has_column( $wpdb, '`match_data` TEXT' ) ) {
			return $this->do_query( $wpdb, "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD `match_data` TEXT NULL DEFAULT NULL AFTER `match_url`" );
		}

		return true;
	}

	protected function convert_existing_urls_400( $wpdb ) {
		// All regex get match_url=regex
		$this->do_query( $wpdb, "UPDATE `{$wpdb->prefix}redirection_items` SET match_url='regex' WHERE regex=1" );

		// Remove query part from all URLs and lowercase
		$this->do_query( $wpdb, "UPDATE `{$wpdb->prefix}redirection_items` SET match_url=SUBSTRING_INDEX(LOWER(url), '?', 1) WHERE regex=0" );

		// Trim the last / from a URL
		$this->do_query( $wpdb, "UPDATE `{$wpdb->prefix}redirection_items` SET match_url=LEFT(match_url,LENGTH(match_url)-1) WHERE regex=0 AND match_url != '/' AND RIGHT(match_url, 1) = '/'" );

		// Any URL that is now empty becomes /
		return $this->do_query( $wpdb, "UPDATE `{$wpdb->prefix}redirection_items` SET match_url='/' WHERE match_url=''" );
	}
}
