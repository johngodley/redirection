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
class Redirection_Item
{
  var $id            = null;
	var $url           = null;
	var $type          = null;
	var $regex         = false;
	var $redirector    = null;
	
	var $last_access   = null;
	var $last_count    = 0;
	
	function Redirection_Item ($values, $type = '', $redirector = '')
	{
		if (is_array ($values))
		{
			foreach ($values AS $key => $value)
			 	$this->$key = $value;
		
			$this->redirector  = unserialize ($this->redirector);
			if ($this->last_access == '0000-00-00 00:00:00')
				$this->last_access = 0;
			else
				$this->last_access = mysql2date ('U', $this->last_access);
		}
		else
		{
			$this->url        = $values;
			$this->type       = $type;
			$this->redirector = $redirector;
		}
	}
	
	function get_by_position ($url)
	{
		global $wpdb;
		
		$sql = "SELECT * FROM {$wpdb->prefix}redirection WHERE regex=1 OR url='".wpdb::escape ($url)."' ORDER BY position";

		$rows = $wpdb->get_results ($sql, ARRAY_A);
		$items = array ();
		if (count ($rows) > 0)
		{
			foreach ($rows AS $row)
				$items[] = new Redirection_Item ($row);
		}
		
		return $items;
	}
	
	function get (&$pager)
	{
		global $wpdb;
		
		$sql = "SELECT * FROM {$wpdb->prefix}redirection ";
		$pager->set_total ($wpdb->get_var ("SELECT COUNT(id) FROM {$wpdb->prefix}redirection"));
		$sql .= $pager->to_limits ('', array ('url', 'redirector'));
		
		$rows = $wpdb->get_results ($sql, ARRAY_A);
		$items = array ();
		if (count ($rows) > 0)
		{
			foreach ($rows AS $row)
				$items[] = new Redirection_Item ($row);
		}
		
		return $items;
	}
	
	function get_by_id ($id)
	{
		global $wpdb;
		
		$id = intval ($id);
		$row = $wpdb->get_row ("SELECT * FROM {$wpdb->prefix}redirection WHERE id='$id'", ARRAY_A);
		if ($row)
			return new Redirection_Item ($row);
		return false;
	}
	
	function create ($details)
	{
		global $wpdb;

		$red = new Redirection_Item (array ());
		
		$red->url  = $details['old'];
		$red->type = $details['type'];
		
		if ($red->is_valid ())
		{
			$red->regex = isset ($details['regex']) ? true : false;
		
			$red->redirector = new $details['redirector'];
			
			if (($details['new'] == '' || $details['old'] == '') && get_option ('redirection_auto_target') != '')
			{
				// Auto-generate a target URL
				$id = time ();
				
				if ($details['new'])
				{
					$details['new'] = get_option ('redirection_auto_target');
					$details['new'] = str_replace ('$dec$', $id, $details['new']);
					$details['new'] = str_replace ('$hex$', sprintf ('%x', $id), $details['new']);
				}
				
				if ($details['old'])
				{
					$details['old'] = get_option ('redirection_auto_target');
					$details['old'] = str_replace ('$dec$', $id, $details['old']);
					$details['old'] = str_replace ('$hex$', sprintf ('%x', $id), $details['old']);
				}
			}
			
			$red->redirector->initialize ($details['new']);
		
			$url           = wpdb::escape ($red->url);
			$redirector    = wpdb::escape (serialize ($red->redirector));
			$red->position = $wpdb->get_var ("SELECT COUNT(id) FROM {$wpdb->prefix}redirection");
		
			$wpdb->query ("INSERT INTO {$wpdb->prefix}redirection (url,type,regex,position,redirector) VALUES ('$url','{$red->type}','{$red->regex}','{$red->position}','$redirector')");
			$red->id = $wpdb->insert_id;
			return $red;
		}
		return false;
	}
	
	function delete ($id)
	{
		global $wpdb;
		$id = intval ($id);
		$wpdb->query ("DELETE FROM {$wpdb->prefix}redirection WHERE id='$id'");
		
		RE_Log::delete_for_id ($id);
		
		// Reorder all elements
		$rows = $wpdb->get_results ("SELECT id FROM {$wpdb->prefix}redirection ORDER BY position");
		if (count ($rows) > 0)
		{
			foreach ($rows AS $pos => $row)
				$wpdb->query ("UPDATE {$wpdb->prefix}redirection SET position='$pos' WHERE id='{$row->id}'");
		}
	}
	
	function update ($details)
	{
		if (strlen ($details['old']) > 0)
		{
			$this->url   = $details['old'];
			$this->regex = isset ($details['regex']) ? true : false;
		
			// Update the redirector
			if ($this->is_valid ())
			{
				$this->redirector->save ($details);
		
				$url        = wpdb::escape ($this->url);
				$redirector = wpdb::escape (serialize ($this->redirector));
				$regex      = isset ($details['regex']) ? true : false;
				
				// Save this
				global $wpdb;
				$wpdb->query ("UPDATE {$wpdb->prefix}redirection SET url='$url', regex='{$this->regex}', redirector='$redirector' WHERE id='{$this->id}'");
			}
		}
	}
	
	function sort ($order, $start = 'x', $end = 'x')
	{
		global $wpdb;
		
		if ($start === 'x')
		{
			foreach ($order AS $pos => $id)
				$wpdb->query ("UPDATE {$wpdb->prefix}redirection SET position='$pos' WHERE id='$id'");
		}
		else
		{
			// Get all IDs in this range
			$ids = $wpdb->get_results ("SELECT id FROM {$wpdb->prefix}redirection WHERE position >= $start AND position < $end ORDER BY position");
			// XXX why?
		}
	}
	
	function normalize_agent ($agent)
	{
		if (strpos ($agent, 'Yahoo! Slurp') !== false)
			return 'Yahoo! Slurp';
		else if (strpos ($agent, 'MSIE 6.0') !== false)
			return 'Microsoft IE6';
		else if (preg_match ('@Mozilla/(?:.*?) \((\w+);(?:.*?)\) Gecko/\w+ (\w+)/(.*)@', $agent, $matches) > 0)
			return $matches[1].' '.$matches[2].' '.$matches[3];
		else if (strpos ($agent, 'Safari'))
			return 'Apple Safari';
		else if (strpos ($agent, 'ICC-Crawler') !== false)
			return 'ICC-Crawler';
		
		return $agent;
	}
	
	function matches ($url)
	{
		$this->url = str_replace (' ', '%20', $this->url);
		$matches = false;

		// Check if we match the URL
		if (($this->regex == false && ($this->url == $url || $this->url == rtrim ($url, '/'))) || ($this->regex == true && preg_match ('@'.str_replace ('@', '\\@', $this->url).'@', $url, $matches) > 0))
			$matches = true;

		// Check if our redirector wants this URL
		if ($matches == true && $this->redirector->wants_it () != true)
			$matches = false;

		// Do the dirty
		if ($matches == true)
			return $this->redirect ($url);
		return false;
	}

	function redirect ($url)
	{
		$target = $this->redirector->get_target ($url, $this->url, $this->regex);
		if ($target != '')
		{
			$this->visit ($url, $target);
			if ($this->redirector->redirect_to ($this->type, $target))
				exit ();
			return true;
		}
		return false;
	}
	
	function visit ($url, $target)
	{
		if ($this->id)
		{
			global $wpdb;

			// Update the counters
			$count = $this->last_count + 1;
			$wpdb->query ("UPDATE {$wpdb->prefix}redirection SET last_count='$count', last_access=NOW() WHERE id='{$this->id}'");

			if (isset ($_SERVER['REMOTE_ADDR']))
			  $ip = $_SERVER['REMOTE_ADDR'];
			else if (isset ($_SERVER['HTTP_X_FORWARDED_FOR']))
			  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		
			$log = RE_Log::create ($url, $target, $_SERVER['HTTP_USER_AGENT'], $ip, $_SERVER['HTTP_REFERER'], $this->id);
		}
	}
	
	function reset ()
	{
		global $wpdb;
		
		$this->last_count  = 0;
		$this->last_access = '0000-00-00 00:00:00';
		
		$wpdb->query ("UPDATE {$wpdb->prefix}redirection SET last_count=0, last_access='{$this->last_access}' WHERE id='{$this->id}'");
		
		RE_Log::delete_for_id ($this->id);
	}
	
	function is_valid ()
	{
		$this->url = preg_replace ('@http://(.*?)/@', '/', $this->url);
		$this->url = preg_replace ('@/{2,}@', '/', $this->url);

		if (substr ($this->url, 0, 1) != '/' && $this->regex == false)
			$this->url = '/'.$this->url;
		
		return true;
	}
	
	function show_url ($url)
	{
		return implode ('&#8203;/', explode ('/', $url));
	}
}
?>