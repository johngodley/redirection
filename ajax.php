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

include ('../../../wp-config.php');

class Redirection_AJAX extends Redirection_Plugin
{
	function Redirection_AJAX ($id, $command)
	{
		if (!current_user_can ('edit_plugins'))
			die ('<p style="color: red">You are not allowed access to this resource</p>');
		
		$_POST = stripslashes_deep ($_POST);
		
		$this->register_plugin ('redirection', __FILE__);
		if (method_exists ($this, $command))
			$this->$command ($id);
		else
			die ('<p style="color: red">That function is not defined</p>');
	}
	
	function add_redirect ($id)
	{
		if (in_array ($_POST['type'], array ('301', '302', '307', '404', '410', 'pass')))
		{
			$item = Redirection_Item::create ($_POST);
			
			if ($item !== false)
			{
				echo '<li class="type_'.$item->type.'" id="r_'.$item->id.'">';
				$this->render_admin ('item', array ('redirect' => $item, 'date_format' => get_option ('date_format')));
				echo '</li>';
				
				if (count ($items) > 1)
					$this->render_admin ('sortables');
			}
		}
	}
	
	function show_redirect ($id)
	{
		$redirect = Redirection_Item::get_by_id ($id);
		$log      = RE_Log::get_last ($id);
		if ($redirect)
		{
			$this->render_admin ('item_details', array ('redirect' => $redirect, 'log' => $log));
			$this->render_admin ('sortables');
		}
	}
	
	function save_redirect ($id)
	{
		$redirect = Redirection_Item::get_by_id ($id);
		$redirect->update ($_POST);
		
		$this->render_admin ('item', array ('redirect' => $redirect, 'date_format' => get_option ('date_format')));
	}
	
	function cancel_redirect ($id)
	{
		$redirect = Redirection_Item::get_by_id ($id);
		if ($redirect)
		{
			$this->render_admin ('item', array ('redirect' => $redirect, 'date_format' => get_option ('date_format')));
			$this->render_admin ('sortables');
		}
	}
	
	function delete_redirect ($id)
	{
		Redirection_Item::delete ($id);
	}
	
	function save_order ($id)
	{
		// First save the pager order
		$parts = explode ('&', $_GET['url']);
		unset ($parts[0]);
		
		$urls = array ();
		foreach ($parts AS $part)
		{
			$tmp = explode ('=', $part);
			$urls[$tmp[0]] = $tmp[1];
		}
		
		$pager = new RE_Pager ($urls, $_GET['url'], 'position', 'ASC');
		if ($pager->order_by != 'position' && $pager->order_direction != 'ASC')
		{
			$items = Redirection_Item::get ($pager);
		
			// Convert to a sort order
			$sort = array ();
			foreach ($items AS $item)
				$sort[] = $item->id;
			
			Redirection_Item::sort ($sort);
		}

		// Then save the current screen order
		$order = $_POST['redirections'];
		$fullorder = array ();
		
		foreach ($order AS $pos => $id)
			$fullorder[$pager->offset () + $pos] = $id;

		Redirection_Item::sort ($fullorder);
	}
	
	function reset_redirect ($id)
	{
		$redirect = Redirection_Item::get_by_id ($id);
		if ($redirect)
		{
			$redirect->reset ();
			$this->render_admin ('item', array ('redirect' => $redirect, 'date_format' => get_option ('date_format')));
		}
	}
	
	function show_log ($id)
	{
		$log      = RE_Log::get_by_id ($id);
		$redirect = Redirection_Item::get_by_id ($log->redirection_id);
		
		$this->render_admin ('log_item_details', array ('log' => $log, 'redirect' => $redirect, 'lookup' => get_option ('redirection_lookup')));
	}
	
	function hide_log ($id)
	{
		$log = RE_Log::get_by_id ($id);
		$this->render_admin ('log_item', array ('log' => $log, 'lookup' => get_option ('redirection_lookup')));
	}
	
	function show_404 ($id)
	{
		$log      = RE_Log::get_by_id ($id);
		$redirect = Redirection_Item::get_by_id ($log->redirection_id);
		
		$this->render_admin ('404_item_details', array ('log' => $log, 'redirect' => $redirect, 'lookup' => get_option ('redirection_lookup')));
	}
	
	function hide_404 ($id)
	{
		$log = RE_Log::get_by_id ($id);
		$this->render_admin ('404_item', array ('log' => $log, 'lookup' => get_option ('redirection_lookup')));
	}
	
	function delete_log ($id)
	{
		RE_Log::delete ($id);
	}
}

$id  = $_GET['id'];
$cmd = $_GET['cmd'];

$obj = new Redirection_AJAX ($id, $cmd);

?>