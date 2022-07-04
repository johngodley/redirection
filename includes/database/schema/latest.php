<?php

/**
 * Latest database schema
 */
class Red_Latest_Database extends Red_Database_Upgrader {
	public function get_stages() {
		return [
			/* translators: displayed when installing the plugin */
			'create_tables' => __( 'Install Redirection tables', 'redirection' ),
			/* translators: displayed when installing the plugin */
			'create_groups' => __( 'Create basic data', 'redirection' ),
		];
	}

	/**
	 * Install the latest database
	 *
	 * @return bool|WP_Error true if installed, WP_Error otherwise
	 */
	public function install() {
		global $wpdb;

		foreach ( $this->get_stages() as $stage => $info ) {
			$result = $this->$stage( $wpdb );

			if ( is_wp_error( $result ) ) {
				if ( $wpdb->last_error ) {
					$result->add_data( $wpdb->last_error );
				}

				return $result;
			}
		}

		red_set_options( array( 'database' => REDIRECTION_DB_VERSION ) );
		return true;
	}

	/**
	 * Remove the database and any options (including unused ones)
	 */
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
		delete_option( Red_Database_Status::OLD_DB_VERSION );
	}

	/**
	 * Return any tables that are missing from the database
	 *
	 * @return array Array of missing table names
	 */
	public function get_missing_tables() {
		global $wpdb;

		$tables = array_keys( $this->get_all_tables() );
		$missing = [];

		foreach ( $tables as $table ) {
			$result = $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

			if ( intval( $result, 10 ) !== 1 ) {
				$missing[] = $table;
			}
		}

		return $missing;
	}

	/**
	 * Get table schema for latest database tables
	 *
	 * @return array Database schema array
	 */
	public function get_table_schema() {
		global $wpdb;

		$tables = array_keys( $this->get_all_tables() );
		$show = array();

		foreach ( $tables as $table ) {
			// These are known queries without user input
			// phpcs:ignore
			$row = $wpdb->get_row( 'SHOW CREATE TABLE ' . $table, ARRAY_N );

			if ( $row ) {
				$show = array_merge( $show, explode( "\n", $row[1] ) );
				$show[] = '';
			} else {
				/* translators: 1: table name */
				$show[] = sprintf( __( 'Table "%s" is missing', 'redirection' ), $table );
			}
		}

		return $show;
	}

	/**
	 * Return array of table names and table schema
	 *
	 * @return array
	 */
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

	/**
	 * Creates default group information
	 */
	public function create_groups( $wpdb, $is_live = true ) {
		if ( ! $is_live ) {
			return true;
		}

		$defaults = [
			[
				'name' => __( 'Redirections', 'redirection' ),
				'module_id' => 1,
				'position' => 0,
			],
			[
				'name' => __( 'Modified Posts', 'redirection' ),
				'module_id' => 1,
				'position' => 1,
			],
		];

		$existing_groups = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups" );

		// Default groups
		if ( intval( $existing_groups, 10 ) === 0 ) {
			$wpdb->insert( $wpdb->prefix . 'redirection_groups', $defaults[0] );
			$wpdb->insert( $wpdb->prefix . 'redirection_groups', $defaults[1] );
		}

		$group = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_groups LIMIT 1" );
		if ( $group ) {
			red_set_options( array( 'last_group_id' => $group->id ) );
		}

		return true;
	}

	/**
	 * Creates all the tables
	 */
	public function create_tables( $wpdb ) {
		global $wpdb;

		foreach ( $this->get_all_tables() as $table => $sql ) {
			$sql = preg_replace( '/[ \t]{2,}/', '', $sql );
			$this->do_query( $wpdb, $sql );
		}

		return true;
	}

	private function create_items_sql( $prefix, $charset_collate ) {
		return "CREATE TABLE IF NOT EXISTS `{$prefix}redirection_items` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`url` mediumtext NOT NULL,
			`match_url` VARCHAR(2000) DEFAULT NULL,
  			`match_data` TEXT,
			`regex` INT(11) unsigned NOT NULL DEFAULT '0',
			`position` INT(11) unsigned NOT NULL DEFAULT '0',
			`last_count` INT(10) unsigned NOT NULL DEFAULT '0',
			`last_access` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
			`group_id` INT(11) NOT NULL DEFAULT '0',
			`status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
			`action_type` VARCHAR(20) NOT NULL,
			`action_code` INT(11) unsigned NOT NULL,
			`action_data` MEDIUMTEXT,
			`match_type` VARCHAR(20) NOT NULL,
			`title` TEXT,
			PRIMARY KEY (`id`),
			KEY `url` (`url`(191)),
			KEY `status` (`status`),
			KEY `regex` (`regex`),
			KEY `group_idpos` (`group_id`,`position`),
			KEY `group` (`group_id`),
			KEY `match_url` (`match_url`(191))
	  ) $charset_collate";
	}

	private function create_groups_sql( $prefix, $charset_collate ) {
		return "CREATE TABLE IF NOT EXISTS `{$prefix}redirection_groups` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`name` VARCHAR(50) NOT NULL,
			`tracking` INT(11) NOT NULL DEFAULT '1',
			`module_id` INT(11) unsigned NOT NULL DEFAULT '0',
			`status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
			`position` INT(11) unsigned NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `module_id` (`module_id`),
			KEY `status` (`status`)
		) $charset_collate";
	}

	private function create_log_sql( $prefix, $charset_collate ) {
		return "CREATE TABLE IF NOT EXISTS `{$prefix}redirection_logs` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`created` datetime NOT NULL,
			`url` MEDIUMTEXT NOT NULL,
			`domain` VARCHAR(255) DEFAULT NULL,
			`sent_to` MEDIUMTEXT,
			`agent` MEDIUMTEXT,
			`referrer` MEDIUMTEXT,
			`http_code` INT(11) unsigned NOT NULL DEFAULT '0',
			`request_method` VARCHAR(10) DEFAULT NULL,
			`request_data` MEDIUMTEXT,
			`redirect_by` VARCHAR(50) DEFAULT NULL,
			`redirection_id` INT(11) unsigned DEFAULT NULL,
			`ip` VARCHAR(45) DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `created` (`created`),
			KEY `redirection_id` (`redirection_id`),
			KEY `ip` (`ip`)
	  	) $charset_collate";
	}

	private function create_404_sql( $prefix, $charset_collate ) {
		return "CREATE TABLE IF NOT EXISTS `{$prefix}redirection_404` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`created` datetime NOT NULL,
			`url` MEDIUMTEXT NOT NULL,
			`domain` VARCHAR(255) DEFAULT NULL,
			`agent` VARCHAR(255) DEFAULT NULL,
			`referrer` VARCHAR(255) DEFAULT NULL,
			`http_code` INT(11) unsigned NOT NULL DEFAULT '0',
			`request_method` VARCHAR(10) DEFAULT NULL,
			`request_data` MEDIUMTEXT,
			`ip` VARCHAR(45) DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `created` (`created`),
			KEY `referrer` (`referrer`(191)),
			KEY `ip` (`ip`)
	  	) $charset_collate";
	}
}
