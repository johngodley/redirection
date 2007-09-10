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
class RE_Log
{
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
		
		$this->ip      = long2ip ($this->ip);
		$this->created = mysql2date ('U', $this->created);
		$this->url     = stripslashes ($this->url);
	}
	
	function get_last ($id)
	{
		global $wpdb;
		
		$row = $wpdb->get_row ("SELECT * FROM {$wpdb->prefix}redirection_log WHERE redirection_id='$id' ORDER BY created DESC LIMIT 1");
		if ($row)
			return new RE_Log ($row);
		return false;
	}
	
	function get_by_id ($id)
	{
		global $wpdb;
		
		$row = $wpdb->get_row ("SELECT * FROM {$wpdb->prefix}redirection_log WHERE id='$id'", ARRAY_A);
		if ($row)
			return new RE_Log ($row);
		return false;
	}
	
	function get (&$pager)
	{
		global $wpdb;
		
		$pager->set_total ($wpdb->get_var ("SELECT COUNT(id) FROM {$wpdb->prefix}redirection_log WHERE redirection_id IS NOT NULL"));
		$rows = $wpdb->get_results ("SELECT * FROM {$wpdb->prefix}redirection_log".$pager->to_limits ('redirection_id IS NOT NULL', array ('url', 'sent_to')), ARRAY_A);
		$items = array ();
		if (count ($rows) > 0)
		{
			foreach ($rows AS $row)
				$items[] = new RE_Log ($row);
		}
		
		return $items;
	}
	
	function get_404 (&$pager)
	{
		global $wpdb;
		
		$pager->set_total ($wpdb->get_var ("SELECT COUNT(id) FROM {$wpdb->prefix}redirection_log WHERE redirection_id IS NULL"));
		$rows = $wpdb->get_results ("SELECT * FROM {$wpdb->prefix}redirection_log".$pager->to_limits ('redirection_id IS NULL', array ('url')), ARRAY_A);
		$items = array ();
		if (count ($rows) > 0)
		{
			foreach ($rows AS $row)
				$items[] = new RE_Log ($row);
		}
		
		return $items;
	}
	
	function create ($url, $target, $agent, $ip, $referrer, $redirection_id = 'NULL')
	{
		global $wpdb;
		
		// Add a log entry
		$url    = wpdb::escape ($url);
		$agent  = wpdb::escape ($agent);
		
		// Grab IP address
		$ip = sprintf ('%u', ip2long ($ip));
		
		// And referring URL
		if (strlen ($referrer) > 0)
			$referrer = "'".wpdb::escape ($referrer)."'";
		else
			$referrer = 'NULL';
			
		if ($target == '')
			$target = 'NULL';
		else
			$target = "'".wpdb::escape ($target)."'";
		
		$wpdb->query ("INSERT INTO {$wpdb->prefix}redirection_log (url,sent_to,created,agent,redirection_id,ip,referrer) VALUES ('$url',$target,NOW(),'$agent',$redirection_id, '$ip', $referrer)");
	}
	
	function show_url ($url)
	{
		return implode ('&#8203;/', explode ('/', substr (htmlspecialchars (urldecode ($url)), 0, 50))).(strlen ($url) > 50 ? '...' : '');
	}
	
	function delete ($id)
	{
		global $wpdb;
		$wpdb->query ("DELETE FROM {$wpdb->prefix}redirection_log WHERE id='$id'");
	}
	
	function delete_404 ()
	{
		global $wpdb;
		$wpdb->query ("DELETE FROM {$wpdb->prefix}redirection_log WHERE redirection_id IS NULL");
	}
	
	function delete_for_id ($id)
	{
		global $wpdb;
		$wpdb->query ("DELETE FROM {$wpdb->prefix}redirection_log WHERE redirection_id='$id'");
	}
	
	function delete_all ()
	{
		global $wpdb;
		$wpdb->query ("DELETE FROM {$wpdb->prefix}redirection_log WHERE redirection_id IS NOT NULL");
	}
	
	function referrer ()
	{
		$home = get_bloginfo ('home');
		if (substr ($this->referrer, 0, strlen ($home)) == $home)
			return substr ($this->referrer, strlen ($home));
		return $this->referrer;
	}
}


?>