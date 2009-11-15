<?php

if (!function_exists ('get_home_path'))
{
	function get_home_path() {
		$home = get_option( 'home' );
		if ( $home != '' && $home != get_option( 'siteurl' ) ) {
			$home_path = parse_url( $home );
			$home_path = $home_path['path'];
			$root = str_replace( $_SERVER["PHP_SELF"], '', $_SERVER["SCRIPT_FILENAME"] );
			$home_path = trailingslashit( $root.$home_path );
		} else {
			$home_path = ABSPATH;
		}

		return $home_path;
	}
}
	
class A_Redirector_URL
{
}

class Redirector_Login
{
}

class Redirector_LuckyDip
{
}

class Redirector_Random
{
}

class Redirector_Referrer
{
}

class RE_Database
{
	function install ()
	{
		global $wpdb;
		
		// Create database
		$wpdb->query ("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}redirection_items` (
			`id` int(11) unsigned NOT NULL auto_increment,
		  `url` mediumtext NOT NULL,
		  `regex` int(11) unsigned NOT NULL default '0',
		  `position` int(11) unsigned NOT NULL default '0',
		  `last_count` int(10) unsigned NOT NULL default '0',
		  `last_access` datetime NOT NULL,
		  `group_id` int(11) NOT NULL default '0',
		  `status` enum('enabled','disabled') NOT NULL default 'enabled',
		  `action_type` varchar(20) NOT NULL,
		  `action_code` int(11) unsigned NOT NULL,
		  `action_data` mediumtext,
		  `match_type` varchar(20) NOT NULL,
		  `title` varchar(50) NULL,
		  PRIMARY KEY  (`id`)
		)");

		$wpdb->query ("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}redirection_groups` (
		  `id` int(11) NOT NULL auto_increment,
		  `name` varchar(50) NOT NULL,
		  `tracking` int(11) NOT NULL default '1',
		  `module_id` int(11) unsigned NOT NULL default '0',
	  	`status` enum('enabled','disabled') NOT NULL default 'enabled',
	  	`position` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`)
		)");

		$wpdb->query ("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}redirection_logs` (
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
		  PRIMARY KEY  (`id`)
		)");
		
		$wpdb->query ("CREATE TABLE `{$wpdb->prefix}redirection_modules` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `type` varchar(20) NOT NULL default '',
		  `name` varchar(50) NOT NULL default '',
		  `options` mediumtext,
		  PRIMARY KEY  (`id`)
		)");
		
		$this->defaults ();
	}
	
	function defaults ()
	{
		global $wpdb;
		
		$optionswp     = '';
		$options404    = '';
		$optionsaoache = '';
//		$optionsapache = $wpdb->escape (serialize (array ('location' => get_home_path ().'.htaccess')));

		// Modules
		if ($wpdb->get_var ("SELECT COUNT(*) FROM {$wpdb->prefix}redirection_modules") == 0)
		{
			$wpdb->query ("INSERT INTO {$wpdb->prefix}redirection_modules (id,type,name,options) VALUES (1,'wp','".__ ('WordPress', 'redirection')."','$optionswp')");
			$wpdb->query ("INSERT INTO {$wpdb->prefix}redirection_modules (id,type,name,options) VALUES (2,'apache','".__ ('Apache', 'redirection')."','$optionsapache')");
			$wpdb->query ("INSERT INTO {$wpdb->prefix}redirection_modules (id,type,name,options) VALUES (3,'404','".__ ('404 Errors', 'redirection')."','$options404')");
		}

		// Groups
		if ($wpdb->get_var ("SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups") == 0)
		{
			$wpdb->query ("INSERT INTO {$wpdb->prefix}redirection_groups (id,name,module_id,position) VALUES (1,'".__ ('Redirections', 'redirection')."',1,0)");
			$wpdb->query ("INSERT INTO {$wpdb->prefix}redirection_groups (id,name,module_id,position) VALUES (2,'".__ ('Modified posts', 'redirection')."',1,1)");
			$wpdb->query ("UPDATE {$wpdb->prefix}redirection_items SET group_id='1', status='enabled'");

			$options = get_option ('redirection_options');
			$options['monitor_post']     = 2;
			$options['monitor_category'] = 2;
			
			update_option ('redirection_options', $options);
		}
	}
	
	function upgrade ($current, $target)
	{
		global $wpdb;

		if ($current === false)
			$this->install ();
		else if ($current == '1.2')
			$this->upgrade_from_0 ();
		else if ($current == '1.7')
			$this->upgrade_from_1 ();
		else if ($current == '1.9' || $current == 'DRAINHOLE_VERSION')
			$this->upgrade_from_2 ();
		else if ($current == '2.0')
			$this->upgrade_from_20 ();
		else if ($current == '2.0.1')
			$this->upgrade_from_21 ();
		else if ($current == '2.0.2')
			$this->upgrade_from_22 ();

		if ( version_compare( $current, '2.1.16' ) == -1 )
			$this->upgrade_to_216();
		
		// Check that the IP field exists - some users don't have this
		$test = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_logs LIMIT 0,1");
		if ( !empty( $test ) && !isset( $test[0]->ip ) ) {
			@$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD `ip` varchar(17) NOT NULL");
		}

		update_option ('redirection_version', $target);
	}
	
	function upgrade_from_0 ()
	{
		// Convert to new format
		$items = get_option ('redirection_list');
		if (is_array ($items) && count ($items) > 0)
		{
			foreach ($items AS $item)
			{
				$data = array ('old' => $item->url_old, 'type' => $item->type, 'red_action' => 'A_Redirector_URL', 'new' => $item->url_new);
				if ($item->regex)
					$data['regex'] = 'on';
					
				Red_Item::create ($data);
			}
		
			delete_option ('redirection_list');
		}
		
		$this->upgrade_from_1 ();
		$this->upgrade_from_2 ();
		$this->upgrade_from_21 ();
		$this->upgrade_from_22 ();
	}
	
	function upgrade_from_1 ()
	{
		global $wpdb;
		
		$wpdb->query ("ALTER TABLE {$wpdb->prefix}redirection CHANGE `type` `type` enum('301','302','307','404','410','pass') NOT NULL DEFAULT '301' ;");
		
		$this->upgrade_from_2 ();
		$this->upgrade_from_21 ();
		$this->upgrade_from_22 ();
	}
	
	function upgrade_from_2 ()
	{
		global $wpdb;

		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection` ADD `group_id` int NOT NULL DEFAULT 0;");
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection` ADD `status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled'");
		$wpdb->query ("RENAME TABLE `{$wpdb->prefix}redirection` TO `{$wpdb->prefix}redirection_items`;");
		$wpdb->query ("RENAME TABLE `{$wpdb->prefix}redirection_log` TO `{$wpdb->prefix}redirection_logs`;");
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_logs` CHANGE `redirection_id` `redirection_id` int(11) UNSIGNED DEFAULT NULL ;");
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD `module_id` int(11) UNSIGNED DEFAULT NULL ;");
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD `group_id` int(11) UNSIGNED DEFAULT NULL ;");
	
		$this->install ();   // Ensure new tables are created
		
		// Merge options together
		$options = get_option ('redirection_options');
		if ($options === false)
			$options = array ();

		$options['auto_target']      = get_option ('redirection_auto_target');
		$options['lookup']           = get_option ('redirection_lookup');
		
		delete_option ('redirection_root');
		delete_option ('redirection_index');
		delete_option ('redirection_post');
		delete_option ('redirection_404_log');
		delete_option ('redirection_global_404');
		delete_option ('redirection_auto_target');
		delete_option ('redirection_lookup');
		delete_option ('redirection_updates');

		// Update all the redirectors
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_items` ADD `action_type` varchar(20) NOT NULL");
	  $wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_items` ADD `action_data` mediumtext");
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_items` ADD `match_type` varchar(20) NOT NULL");
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_items` ADD `action_code` int(11) UNSIGNED DEFAULT NULL");
		
		$rows = $wpdb->get_results ("SELECT id,redirector,type FROM {$wpdb->prefix}redirection_items");
		if ($rows)
		{
			foreach ($rows AS $row)
			{
				$obj  = unserialize ($row->redirector);
				$data = array ();
				
				foreach ($obj AS $key => $value)
					$data[$key] = $value;
				
				if (count ($data) == 1 && !is_array (current ($data)))
					$data = current ($data);
				
				$type = strtolower (get_class ($obj));
				if ($type == 'a_redirector_url')
				{
					$match  = 'url';
					
					if ($row->type == 'pass')
						$action = 'pass';
					else if ($row->type == '404' || $row->type == '410')
						$action = 'error';
					else
						$action = 'url';
				}
				else if ($type == 'redirector_login')
				{
					$match  = 'login';
					$data   = array ('url_loggedin' => $data['url'][0], 'url_loggedout' => $data['url'][1]);
					$data   = $wpdb->escape (serialize ($data));
					
					if ($row->type == 'pass')
						$action = 'pass';
					else if ($row->type == '404' || $row->type == '410')
						$action = 'error';
					else
						$action = 'url';
				}
				else if ($type == 'redirector_luckydip')
				{
					$match  = 'url';
					$action = 'random';
					$data   = '';
				}
				else if ($type == 'redirector_random')
				{
					$match  = 'url';
					$action = 'random';
					$data   = '';
				}
				else if ($type == 'redirector_referrer')
				{
					$match  = 'referrer';
					$action = 'url';
					$data   = array ('referrer' => $data['referrer'], 'regex' => $data['regex'], 'url_from' => $data['url'][0], 'url_notfrom' => $data['url'][1]);
					$data   = $wpdb->escape (serialize ($data));
					
					if ($row->type == 'pass')
						$action = 'pass';
					else if ($row->type == '404' || $row->type == '410')
						$action = 'error';
					else
						$action = 'url';
				}

				$wpdb->query ("UPDATE {$wpdb->prefix}redirection_items SET match_type='$match', action_type='$action', action_data='$data' WHERE id='{$row->id}'");
			}
		}
	
		// Action code
		$wpdb->query ("UPDATE {$wpdb->prefix}redirection_items SET action_code=type");
		$wpdb->query ("UPDATE {$wpdb->prefix}redirection_items SET action_code=301 WHERE type = '301'");
		$wpdb->query ("UPDATE {$wpdb->prefix}redirection_items SET action_code=302 WHERE type = '302'");
		$wpdb->query ("UPDATE {$wpdb->prefix}redirection_items SET action_code=307 WHERE type = '307'");
		$wpdb->query ("UPDATE {$wpdb->prefix}redirection_items SET action_code=404 WHERE type = '404'");
		$wpdb->query ("UPDATE {$wpdb->prefix}redirection_items SET action_code=410 WHERE type = '410'");
		$wpdb->query ("UPDATE {$wpdb->prefix}redirection_items SET action_code=301 WHERE type = 'pass'");
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_items` DROP `type`;");
			
		// Drop the old column
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_items` DROP `redirector`");
		
		// Convert log format
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_logs` ADD `ip2` varchar(17) NOT NULL");
		$wpdb->query ("UPDATE {$wpdb->prefix}redirection_logs SET ip2=INET_NTOA(ip)");
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_logs` DROP `ip`");
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_logs` CHANGE `ip2` `ip` varchar(17) NOT NULL DEFAULT '' ");
		$wpdb->query ("UPDATE {$wpdb->prefix}redirection_logs SET group_id='1', module_id='1'");
		
		update_option ('redirection_options', $options);
		
		$this->upgrade_from_21 ();
		$this->upgrade_from_22 ();
	}
	
	function upgrade_from_20 ()
	{
		global $wpdb;
		
		$this->defaults ();
		$this->upgrade_from_21 ();
		$this->upgrade_from_22 ();
	}
	
	function upgrade_from_21 ()
	{
		global $wpdb;
		
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_items` ADD `title` varchar(50) NULL");
		
		$this->upgrade_from_22 ();
	}
	
	function upgrade_from_22 ()	{
		global $wpdb;
		
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_items` CHANGE `title` `title` varchar(50) NULL");
	}
	
	function upgrade_to_216() {
		global $wpdb;
		
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_groups` ADD INDEX (module_id)");
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_groups` ADD INDEX (status)");
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX (url(200))");
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX (status)");
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}redirection_items` ADD INDEX (regex)");
	}
	
	function remove ($plugin)
	{
		global $wpdb;
		
		$wpdb->query ("DROP TABLE IF EXISTS {$wpdb->prefix}redirection;");
		$wpdb->query ("DROP TABLE IF EXISTS {$wpdb->prefix}redirection_items;");
		$wpdb->query ("DROP TABLE IF EXISTS {$wpdb->prefix}redirection_logs;");
		$wpdb->query ("DROP TABLE IF EXISTS {$wpdb->prefix}redirection_groups;");
		$wpdb->query ("DROP TABLE IF EXISTS {$wpdb->prefix}redirection_modules;");
		
		delete_option ('redirection_lookup');
		delete_option ('redirection_post');
		delete_option ('redirection_root');
		delete_option ('redirection_index');
		delete_option ('redirection_version');
		
		$current = get_option('active_plugins');
		array_splice ($current, array_search (basename (dirname ($plugin)).'/'.basename ($plugin), $current), 1 );
		update_option('active_plugins', $current);
	}
}
?>