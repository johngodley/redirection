<?php

class RE_Database {
	public function get_charset() {
		global $wpdb;

		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE=$wpdb->collate";
		}

		return $charset_collate;
	}

	private function create_items_sql( $prefix, $charset_collate ) {
		return "CREATE TABLE IF NOT EXISTS `{$prefix}redirection_items` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`url` mediumtext NOT NULL,
			`regex` int(11) unsigned NOT NULL DEFAULT '0',
			`position` int(11) unsigned NOT NULL DEFAULT '0',
			`last_count` int(10) unsigned NOT NULL DEFAULT '0',
			`last_access` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`group_id` int(11) NOT NULL DEFAULT '0',
			`status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
			`action_type` varchar(20) NOT NULL,
			`action_code` int(11) unsigned NOT NULL,
			`action_data` mediumtext,
			`match_type` varchar(20) NOT NULL,
			`title` text,
			PRIMARY KEY (`id`),
			KEY `url` (`url`(191)),
			KEY `status` (`status`),
			KEY `regex` (`regex`),
			KEY `group_idpos` (`group_id`,`position`),
			KEY `group` (`group_id`)
	  ) $charset_collate";
	}

	private function create_groups_sql( $prefix, $charset_collate ) {
		return "CREATE TABLE IF NOT EXISTS `{$prefix}redirection_groups` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(50) NOT NULL,
			`tracking` int(11) NOT NULL DEFAULT '1',
			`module_id` int(11) unsigned NOT NULL DEFAULT '0',
			`status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
			`position` int(11) unsigned NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `module_id` (`module_id`),
			KEY `status` (`status`)
		) $charset_collate";
	}

	private function create_log_sql( $prefix, $charset_collate ) {
		return "CREATE TABLE IF NOT EXISTS `{$prefix}redirection_logs` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `created` datetime NOT NULL,
		  `url` mediumtext NOT NULL,
		  `sent_to` mediumtext,
		  `agent` mediumtext NOT NULL,
		  `referrer` mediumtext,
		  `redirection_id` int(11) unsigned DEFAULT NULL,
  		  `ip` varchar(45) DEFAULT NULL,
		  `module_id` int(11) unsigned NOT NULL,
		  `group_id` int(11) unsigned DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `created` (`created`),
		  KEY `redirection_id` (`redirection_id`),
		  KEY `ip` (`ip`),
		  KEY `group_id` (`group_id`),
		  KEY `module_id` (`module_id`)
	  	) $charset_collate";
	}

	private function create_404_sql( $prefix, $charset_collate ) {
		return "CREATE TABLE IF NOT EXISTS `{$prefix}redirection_404` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `created` datetime NOT NULL,
		  `url` varchar(255) NOT NULL DEFAULT '',
		  `agent` varchar(255) DEFAULT NULL,
		  `referrer` varchar(255) DEFAULT NULL,
		  `ip` varchar(45) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `created` (`created`),
		  KEY `url` (`url`(191)),
		  KEY `referrer` (`referrer`(191)),
		  KEY `ip` (`ip`)
	  	) $charset_collate";
	}

	public function get_all_tables() {
		global $wpdb;

		$charset_collate = $this->get_charset();

		return array(
			"{$wpdb->prefix}redirection_items" => $this->create_items_sql( $wpdb->prefix, $charset_collate ),
			"{$wpdb->prefix}redirection_groups" => $this->create_groups_sql( $wpdb->prefix, $charset_collate ),
			"{$wpdb->prefix}redirection_logs" => $this->create_log_sql( $wpdb->prefix, $charset_collate ),
			"{$wpdb->prefix}redirection_404" => $this->create_404_sql( $wpdb->prefix, $charset_collate ),
		);
	}

	public function create_tables() {
		global $wpdb;

		foreach ( $this->get_all_tables() as $sql ) {
			if ( $wpdb->query( $sql ) === false ) {
				throw new Exception( 'There was a database error installing Redirection - please post these details to https://github.com/johngodley/redirection/issues - '.$sql.' = '.$wpdb->print_error() );
				return false;
			}
		}
	}

	public function create_defaults() {
		global $wpdb;

		$this->create_default_groups();

		update_option( 'redirection_version', REDIRECTION_DB_VERSION );

		$group = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_groups LIMIT 1" );
		if ( $group ) {
			red_set_options( array( 'last_group_id' => $group->id ) );
		}
	}

	private function create_default_groups() {
		global $wpdb;

		$existing_groups = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups" );

		// Default groups
		if ( intval( $existing_groups, 10 ) === 0 ) {
			$wpdb->insert( $wpdb->prefix.'redirection_groups', array( 'name' => __( 'Redirections', 'redirection' ), 'module_id' => 1, 'position' => 0 ) );
			$wpdb->insert( $wpdb->prefix.'redirection_groups', array( 'name' => __( 'Modified Posts', 'redirection' ), 'module_id' => 1, 'position' => 1 ) );
		}
	}

	public function install() {
		global $wpdb;

		$wpdb->show_errors();
		$this->create_tables();
		$this->create_defaults();
		$wpdb->hide_errors();

		return true;
	}

	public function upgrade( $current, $target ) {
		global $wpdb;

		$wpdb->show_errors();

		if ( $current !== false ) {
			$versions = array(
				'2.0.1'  => 'upgrade_to_201',
				'2.1.16' => 'upgrade_to_216',
				'2.2'    => 'upgrade_to_220',
				'2.3.1'  => 'upgrade_to_231',
				'2.3.2'  => 'upgrade_to_232',
				'2.3.3'  => 'upgrade_to_233',
				'2.4'    => 'upgrade_to_24',
			);

			foreach ( $versions AS $vers => $upgrade ) {
				if ( version_compare( $current, $vers ) === -1 ) {
					$this->$upgrade();
				}
			}

			update_option( 'redirection_version', $target );
		}

		$wpdb->hide_errors();
	}

	/**
	 * Convert IP columns to VARCHAR(45)
	 */

	private function upgrade_to_24() {
		global $wpdb;

		// Expand logs to VARCHAR(45)
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_logs` CHANGE `ip` `ip` VARCHAR(45) DEFAULT NULL" );

		// 404 log is an INT - add a new column with VARCHAR(45) and we'll delete the INT later
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_404` ADD `ipaddress` VARCHAR(45) DEFAULT NULL AFTER `ip`" );

		// Convert all INT ips to VARCHAR ips
		$wpdb->query( "UPDATE {$wpdb->prefix}redirection_404 SET ipaddress=INET_NTOA(ip)" );

		// Now remove the old column
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_404` DROP `ip`" );

		// And rename the new column
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_404` CHANGE `ipaddress` `ip` VARCHAR(45) DEFAULT NULL" );

		// This was missed in a previous upgrade script
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_404` ADD INDEX `ip` (`ip`)" );

		// Convert 'title' to a TEXT column
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_items` CHANGE `title` `title` text" );
	}

	/**
	 * 2.32 => 2.3.3
	 * Migrate any groups with incorrect module_ids
	 * Create a group if none exists
	 */
	private function upgrade_to_233() {
		global $wpdb;

		$wpdb->query( "UPDATE {$wpdb->prefix}redirection_groups SET module_id=1 WHERE module_id > 2" );
		$this->create_default_groups();
	}

	/**
	 * 2.3.1 => 2.3.2
	 * Delete the redirection_modules table
	 */
	private function upgrade_to_232() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_modules;" );
	}

	/**
	 * 2.2 => 2.3.1
	 * Update any group referring to 404 module to WordPress module
	 * Create 404 log table
	 */
	private function upgrade_to_231() {
		global $wpdb;

		$wpdb->query( "UPDATE {$wpdb->prefix}redirection_groups SET module_id=1 WHERE module_id=3" );
		$wpdb->query( $this->create_404_sql( $wpdb->prefix, $this->get_charset() ) );
	}

	/**
	 * 2.1.6 => 2.2.0
	 * Add indices to redirection items and logs
	 */
	private function upgrade_to_220() {
		global $wpdb;

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX `group_idpos` (`group_id`,`position`)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX `group` (`group_id`)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD INDEX `created` (`created`)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD INDEX `redirection_id` (`redirection_id`)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD INDEX `ip` (`ip`)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD INDEX `group_id` (`group_id`)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD INDEX `module_id` (`module_id`)" );
	}

	/**
	 * 2.0.1 => 2.1.6
	 * Update any group referring to 404 module to WordPress module
	 * Create 404 log table
	 */
	private function upgrade_to_216() {
		global $wpdb;

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_groups` ADD INDEX(module_id)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_groups` ADD INDEX(status)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX(url(191))" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX(status)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX(regex)" );
	}

	/**
	 * <2.0.1 => 2.0.1
	 */
	private function upgrade_to_201() {
		global $wpdb;

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD `title` varchar(50) NULL" );
	}

	public function remove() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_items" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_logs" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_groups" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_modules" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_404" );

		delete_option( 'redirection_lookup' );
		delete_option( 'redirection_post' );
		delete_option( 'redirection_root' );
		delete_option( 'redirection_index' );
		delete_option( 'redirection_options' );
		delete_option( 'redirection_version' );
	}

	public function get_status() {
		global $wpdb;

		$missing = array();

		foreach ( $this->get_all_tables() as $table => $sql ) {
			$result = $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

			if ( intval( $result, 10 ) !== 1 ) {
				$missing[] = $table;
			}
		}

		return array(
			'status' => count( $missing ) === 0 ? 'good' : 'error',
			'message' => count( $missing ) === 0 ? __( 'All tables present', 'redirection' ) : __( 'The following tables are missing:', 'redirection' ).' '.join( ',', $missing ),
		);
	}
}
