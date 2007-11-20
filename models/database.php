<?php
	
class RE_Database
{
	function install ()
	{
		global $wpdb;
		
		// Create database
		$wpdb->query ("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}redirection` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `url` mediumtext NOT NULL,
		  `type` enum('301','302','307','404','410','pass') NOT NULL default '301',
		  `regex` int(11) unsigned NOT NULL default '0',
		  `position` int(11) unsigned NOT NULL default '0',
		  `redirector` text NOT NULL,
		  `last_count` int(10) unsigned NOT NULL default '0',
		  `last_access` datetime,
		  PRIMARY KEY  (`id`)
		)");

		$wpdb->query ("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}redirection_log` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `created` datetime NOT NULL,
		  `url` mediumtext NOT NULL,
		  `sent_to` mediumtext,
		  `agent` mediumtext NOT NULL,
		  `referrer` mediumtext,
		  `ip` int(11) unsigned NOT NULL default '0',
		  `redirection_id` int(11) default NULL,
		  PRIMARY KEY  (`id`)
		)");
	}
	
	function upgrade ($current, $target)
	{
		global $wpdb;
		
		if (get_option ('redirection_lookup') === false)
			update_option ('redirection_lookup', 'http://geomaplookup.cinnamonthoughts.org/?ip=');
		
		$this->install ();
		if ($current === false || $current == '1.2')
		{
			// Convert to new format
			$items = get_option ('redirection_list');
			if (is_array ($items) && count ($items) > 0)
			{
				foreach ($items AS $item)
				{
					$data = array ('old' => $item->url_old, 'type' => $item->type, 'redirector' => 'A_Redirector_URL', 'new' => $item->url_new);
					if ($item->regex)
						$data['regex'] = 'on';
					Redirection_Item::create ($data);
				}
			}
			
			delete_option ('redirection_list');
		}
		else if ($current == '1.7')
			$wpdb->query ("ALTER TABLE {$wpdb->prefix}redirection CHANGE `type` `type` enum('301','302','307','404','410','pass') NOT NULL DEFAULT '301' ;");

		update_option ('redirection_version', $target);
	}
	
	function remove ($plugin)
	{
		global $wpdb;
		
		$wpdb->query ("DROP TABLE {$wpdb->prefix}redirection;");
		$wpdb->query ("DROP TABLE {$wpdb->prefix}redirection_log;");
		
		delete_option ('redirection_option');
		delete_option ('redirection_lookup');
		delete_option ('redirection_updates');
		delete_option ('redirection_index');
		delete_option ('redirection_post');
		delete_option ('redirection_version');
		delete_option ('redirection_global_404');
		
		$current = get_option('active_plugins');
		array_splice ($current, array_search (basename (dirname ($plugin)).'/'.basename ($plugin), $current), 1 );
		update_option('active_plugins', $current);
	}
}
?>