<?php
/**
 * Redirection
 *
 * @package Redirection
 * @author John Godley
 * @copyright Copyright (C) John Godley
 **/

/*
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages (including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================ */

class RE_Log {
	var $id;
	var $created;
	var $url;
	var $agent;
	var $referrer;
	var $ip;
	var $redirection_id;
	
	function RE_Log ($values)
	{
		foreach ($values AS $key => $value)
		 	$this->$key = $value;
		
		$this->created = mysql2date ('U', $this->created);
		$this->url     = stripslashes ($this->url);
	}
	
	function get_by_id ($id)
	{
		global $wpdb;
		
		$row = $wpdb->get_row ("SELECT * FROM {$wpdb->prefix}redirection_logs WHERE id='$id'", ARRAY_A);
		if ($row)
			return new RE_Log ($row);
		return false;
	}
	
	function get( &$pager ) {
		global $wpdb;
		
		$rows = $wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->prefix}redirection_logs FORCE INDEX(created) ".$pager->to_limits ('redirection_id IS NOT NULL', array ('url', 'sent_to', 'ip')), ARRAY_A );
		$pager->set_total ($wpdb->get_var ("SELECT FOUND_ROWS()"));
		
		$items = array ();
		if (count ($rows) > 0)
		{
			foreach ($rows AS $row)
				$items[] = new RE_Log ($row);
		}
		
		return $items;
	}
	
	function get_by_group (&$pager, $group)
	{
		global $wpdb;
		
		$rows = $wpdb->get_results ("SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->prefix}redirection_logs".$pager->to_limits ("redirection_id IS NOT NULL AND group_id='".$group."'", array ('url', 'sent_to', 'ip')), ARRAY_A);
		$pager->set_total ($wpdb->get_var ("SELECT FOUND_ROWS()"));
		
		$items = array ();
		if (count ($rows) > 0)
		{
			foreach ($rows AS $row)
				$items[] = new RE_Log ($row);
		}
		
		return $items;
	}
	
	function get_by_module (&$pager, $module)
	{
		global $wpdb;

		$rows = $wpdb->get_results ("SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->prefix}redirection_logs".$pager->to_limits ("module_id='".$module."'", array ('url', 'sent_to', 'ip')), ARRAY_A);
		$pager->set_total ($wpdb->get_var ("SELECT FOUND_ROWS()"));
		
		$items = array ();
		if (count ($rows) > 0)
		{
			foreach ($rows AS $row)
				$items[] = new RE_Log ($row);
		}
		
		return $items;
	}
	
	function get_by_redirect (&$pager, $redirect)
	{
		global $wpdb;
		
		$rows = $wpdb->get_results ("SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->prefix}redirection_logs".$pager->to_limits ("redirection_id=$redirect", array ('url', 'sent_to', 'ip')), ARRAY_A);
		$pager->set_total ($wpdb->get_var ("SELECT FOUND_ROWS()"));
		
		$items = array ();
		if (count ($rows) > 0)
		{
			foreach ($rows AS $row)
				$items[] = new RE_Log ($row);
		}
		
		return $items;
	}
	
	function create ($url, $target, $agent, $ip, $referrer, $redirection_id = 'NULL', $module_id = 'NULL', $group_id = 'NULL')
	{
		global $wpdb, $redirection;
		
		// Add a log entry
		$url    = $wpdb->escape ($url);
		$agent  = $wpdb->escape ($agent);
		$ip     = $wpdb->escape ($ip);
		
		// And referring URL
		if (strlen ($referrer) > 0)
			$referrer = "'".$wpdb->escape ($referrer)."'";
		else
			$referrer = 'NULL';
			
		if ($target == '')
			$target = 'NULL';
		else
			$target = "'".$wpdb->escape ($target)."'";
		
		$wpdb->query ("INSERT INTO {$wpdb->prefix}redirection_logs (url,sent_to,created,agent,redirection_id,ip,referrer,module_id,group_id) VALUES ('$url',$target,NOW(),'$agent',$redirection_id, '$ip', $referrer, $module_id, $group_id)");
		
		// Expire old entries
		$options = $redirection->get_options ();
		if ($options['expire'] != 0)
			$wpdb->query ("DELETE FROM {$wpdb->prefix}redirection_logs WHERE created < DATE_SUB(NOW(), INTERVAL ".$options['expire']." DAY)");
	}
	
	function show_url ($url)
	{
		return implode ('&#8203;/', explode ('/', substr (htmlspecialchars ($url), 0, 80))).(strlen ($url) > 80 ? '...' : '');
	}

	function delete ($id)
	{
		global $wpdb;
		$wpdb->query ("DELETE FROM {$wpdb->prefix}redirection_logs WHERE id='$id'");
	}
	
	function delete_404 ($pager)
	{
		global $wpdb;
		$wpdb->query ("DELETE FROM {$wpdb->prefix}redirection_logs ".$pager->to_conditions ('redirection_id IS NULL', array ('url', 'sent_to', 'ip')));
	}
	
	function delete_for_id ($id)
	{
		global $wpdb;
		$wpdb->query ("DELETE FROM {$wpdb->prefix}redirection_logs WHERE redirection_id='$id'");
	}
	
	function delete_for_group ($id)
	{
		global $wpdb;
		$wpdb->query ("DELETE FROM {$wpdb->prefix}redirection_logs WHERE group_id=$id");
	}

	function delete_for_module ($id)
	{
		global $wpdb;
		$wpdb->query ("DELETE FROM {$wpdb->prefix}redirection_logs WHERE module_id=$id");
	}
	
	function delete_all ($cond, $pager)
	{
		global $wpdb;
		
		$sql = 'redirection_id IS NOT NULL';
		if (!empty ($cond))
		{
			$sql = '';
			foreach ($cond AS $key => $value)
				$sql .= "$key=$value";
		}

		$wpdb->query ("DELETE FROM {$wpdb->prefix}redirection_logs ".$pager->to_conditions ($sql, array ('url', 'sent_to', 'ip')));
	}
	
	function referrer ()
	{
		return preg_replace ('@https?://(.*?)/.*@', '$1', $this->referrer);
		$home = get_bloginfo ('url');
		if (substr ($this->referrer, 0, strlen ($home)) == $home)
			return substr ($this->referrer, strlen ($home));
		return $this->referrer;
	}
}


?>