<?php

class A_Redirector_URL {
}

class Redirector_Login {
}

class Redirector_LuckyDip {
}

class Redirector_Random {
}

class Redirector_Referrer {
}

class RE_Database {
	function get_charset() {
		global $wpdb;

		$charset_collate = '';
		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";

		return $charset_collate;
	}

	function install() {
		global $wpdb;

		$charset_collate = $this->get_charset();

		$create = array(
			"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}redirection_items`(
				`id` int(11) unsigned NOT NULL auto_increment,
			  `url` mediumtext NOT NULL,
			  `regex` int(11) unsigned NOT NULL default '0',
			  `position` int(11) unsigned NOT NULL default '0',
			  `last_count` int(10) unsigned NOT NULL default '0',
			  `last_access` datetime NOT NULL,
			  `group_id` int(11) NOT NULL default '0',
			  `status` enum('enabled','disabled' ) NOT NULL default 'enabled',
			  `action_type` varchar(20) NOT NULL,
			  `action_code` int(11) unsigned NOT NULL,
			  `action_data` mediumtext,
			  `match_type` varchar(20) NOT NULL,
			  `title` varchar(50) NULL,
			  PRIMARY KEY ( `id`),
				KEY `url` (`url`(200)),
			  KEY `status` (`status`),
			  KEY `regex` (`regex`),
				KEY `group_idpos` (`group_id`,`position`),
			  KEY `group` (`group_id`)
			) $charset_collate",

			"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}redirection_groups`(
			  `id` int(11) NOT NULL auto_increment,
			  `name` varchar(50) NOT NULL,
			  `tracking` int(11) NOT NULL default '1',
			  `module_id` int(11) unsigned NOT NULL default '0',
		  	`status` enum('enabled','disabled' ) NOT NULL default 'enabled',
		  	`position` int(11) unsigned NOT NULL default '0',
			  PRIMARY KEY ( `id`),
				KEY `module_id` (`module_id`),
		  	KEY `status` (`status`)
			) $charset_collate",

			"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}redirection_logs`(
			  `id` int(11) unsigned NOT NULL auto_increment,
			  `created` datetime NOT NULL,
			  `url` mediumtext NOT NULL,
			  `sent_to` mediumtext,
			  `agent` mediumtext NOT NULL,
			  `referrer` mediumtext,
			  `redirection_id` int(11) unsigned default NULL,
			  `ip` varchar(17) NOT NULL default '',
			  `module_id` int(11) unsigned NOT NULL,
				`group_id` int(11) unsigned default NULL,
			  PRIMARY KEY ( `id`),
			  KEY `created` (`created`),
			  KEY `redirection_id` (`redirection_id`),
			  KEY `ip` (`ip`),
			  KEY `group_id` (`group_id`),
			  KEY `module_id` (`module_id`)
			) $charset_collate",

		 	"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}redirection_modules`(
			  `id` int(11) unsigned NOT NULL auto_increment,
			  `type` varchar(20) NOT NULL default '',
			  `name` varchar(50) NOT NULL default '',
			  `options` mediumtext,
		  	PRIMARY KEY ( `id`),
			  KEY `name` (`name`),
			  KEY `type` (`type`)
			) $charset_collate",

			"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}redirection_404` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `created` datetime NOT NULL,
			  `url` varchar(255) NOT NULL DEFAULT '',
			  `agent` varchar(255) DEFAULT NULL,
			  `referrer` varchar(255) DEFAULT NULL,
			  `ip` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `created` (`created`),
			  KEY `url` (`url`),
			  KEY `ip` (`ip`),
			  KEY `referrer` (`referrer`)
			) $charset_collate;"
		);

		foreach ( $create AS $sql ) {
			if ( $wpdb->query( $sql ) === false )
				return false;
		}

		// Modules
		if ( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_modules" ) == 0 ) {
			$wpdb->insert( $wpdb->prefix.'redirection_modules', array( 'type' => 'wp', 'name' => __( 'WordPress', 'redirection' ), 'options' => '' ) );
			$wpdb->insert( $wpdb->prefix.'redirection_modules', array( 'type' => 'apache', 'name' => __( 'Apache', 'redirection' ), 'options' => '' ) );
		}

		// Groups
		if ( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups" ) == 0 ) {
			$wpdb->insert( $wpdb->prefix.'redirection_groups', array( 'name' => __( 'Redirections' ), 'module_id' => 1, 'position' => 0 ) );
			$wpdb->insert( $wpdb->prefix.'redirection_groups', array( 'name' => __( 'Modified Posts' ), 'module_id' => 1, 'position' => 1 ) );

			$options = get_option( 'redirection_options' );
			$options['monitor_post']     = 2;
			$options['monitor_category'] = 2;

			update_option( 'redirection_options', $options );
		}
	}

	function upgrade( $current, $target ) {
		global $wpdb;

		$wpdb->show_errors();

		// No previous version? Install the DB tables
		if ( $current === false )
			$success = $this->install();
		else {
			// Try and upgrade from a previous version
			if ( $current == '2.0' )
				$this->upgrade_from_20();
			elseif ( $current == '2.0.1' )
				$this->upgrade_from_21();
			elseif ( $current == '2.0.2' )
				$this->upgrade_from_22();

			if ( version_compare( $current, '2.1.16' ) == -1 )
				$this->upgrade_to_216();

			if ( version_compare( $current, '2.2' ) == -1 )
				$this->upgrade_to_220();

			if ( version_compare( $current, '2.3.1' ) == -1 )
				$this->upgrade_to_231();

			$success = true;
		}

		// Set our current version
		update_option( 'redirection_version', $target );

		$wpdb->hide_errors();
		return $success;
	}

	function upgrade_to_231() {
		global $wpdb;

		$charset_collate = $this->get_charset();

		$wpdb->query( "DELETE FROM {$wpdb->prefix}redirection_modules WHERE type='404'" );
		$wpdb->query( "UPDATE {$wpdb->prefix}redirection_groups SET module_id=1 WHERE module_id=3" );

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}redirection_404` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `created` datetime NOT NULL,
			  `url` varchar(255) NOT NULL DEFAULT '',
			  `agent` varchar(255) DEFAULT NULL,
			  `referrer` varchar(255) DEFAULT NULL,
			  `ip` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `created` (`created`),
			  KEY `url` (`url`),
  			  KEY `ip` (`ip`,`id`),
			  KEY `referrer` (`referrer`)
			) $charset_collate;" );
	}

	function upgrade_from_20() {
		global $wpdb;

		$this->upgrade_from_21();
		$this->upgrade_from_22();
	}

	function upgrade_from_21() {
		global $wpdb;

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD `title` varchar(50) NULL" );

		$this->upgrade_from_22();
	}

	function upgrade_from_22() {
		global $wpdb;

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_items` CHANGE `title` `title` varchar(50) NULL" );
	}

	function upgrade_to_216() {
		global $wpdb;

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_groups` ADD INDEX(module_id)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_groups` ADD INDEX(status)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX(url(200))" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX(status)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX(regex)" );
	}

	function upgrade_to_220() {
		global $wpdb;

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX `group_idpos` (`group_id`,`position`)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX `group` (`group_id`)" );

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD INDEX `group` (`group_id`)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD INDEX `redirection_id` (`redirection_id`)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD INDEX `created` (`created`)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD INDEX `ip` (`ip`)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD INDEX `group_id` (`group_id`)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD INDEX `module_id` (`module_id`)" );

		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_modules` ADD INDEX `name` (`name`)" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}redirection_modules` ADD INDEX `type` (`type`)" );
	}

	function remove( $plugin ) {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection;" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_items;" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_logs;" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_groups;" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_modules;" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_404;" );

		delete_option( 'redirection_lookup' );
		delete_option( 'redirection_post' );
		delete_option( 'redirection_root' );
		delete_option( 'redirection_index' );
		delete_option( 'redirection_version' );

		$current = get_option( 'active_plugins' );
		array_splice( $current, array_search( basename( dirname( $plugin ) ).'/'.basename( $plugin ), $current ), 1 );
		update_option( 'active_plugins', $current );
	}
}
