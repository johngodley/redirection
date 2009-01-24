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

if (file_exists ('../../../wp-load.php'))
	include ('../../../wp-load.php');
else
	include ('../../../wp-config.php');

function urldecode_deep ($data)
{
	// Disabled
	return $data;
	
	if (is_array ($data))
	{
		foreach ($data AS $key => $values)
			$data[$key] = urldecode_deep ($values);
			
		return $data;
	}
	
	return rawurldecode ($data);
}

class Redirection_AJAX extends Redirection_Plugin
{
	function Redirection_AJAX ($id, $command)
	{
		include (dirname (__FILE__).'/models/pager.php');
		
		if (!current_user_can ('edit_plugins'))
			die (__ ('<p style="color: red">You are not allowed access to this resource</p>', 'redirection'));
		
		$_POST = stripslashes_deep ($_POST);
		
		$this->register_plugin ('redirection', __FILE__);
		if (method_exists ($this, $command))
			$this->$command ($id);
		else
			die (__ ('<p style="color: red">That function is not defined</p>', 'redirection'));
	}
	
	function add_redirect ($id)
	{
		if (check_ajax_referer ('redirection-add_redirect'))
		{
			$_POST = urldecode_deep ($_POST);
		
			$item = Red_Item::create ($_POST);
			if ($item !== false)
			{
				echo '<li class="type_'.$item->action_type.'" id="item_'.$item->id.'">';
				$this->render_admin ('item', array ('redirect' => $item, 'date_format' => get_option ('date_format')));
				echo '</li>';
			}
			else
				$this->render_error (__ ('Sorry, but your redirection was not created', 'redirection'));
		}
	}
	
	function edit_redirect ($id)
	{
		$redirect = Red_Item::get_by_id ($id);
		if ($redirect)
			$this->render_admin ('item_edit', array ('redirect' => $redirect, 'groups' => Red_Group::get_for_select ()));
	}
	
	function save_redirect ($id)
	{
		if (check_ajax_referer ('redirection-item_save'))
		{
			$_POST = urldecode_deep ($_POST);
		
			$redirect = Red_Item::get_by_id ($id);
			$redirect->update ($_POST);
		
			$this->render_admin ('item', array ('redirect' => $redirect, 'date_format' => get_option ('date_format')));
		}
	}
	
	function show_redirect ($id)
	{
		$redirect = Red_Item::get_by_id ($id);
		if ($redirect)
			$this->render_admin ('item', array ('redirect' => $redirect, 'date_format' => get_option ('date_format')));
	}
	
	function toggle_redirects ($id)
	{
		if (count ($_POST['checkall']) > 0)
		{
			foreach ($_POST['checkall'] AS $item)
			{
				$redirect = Red_Item::get_by_id ($item);
				$redirect->toggle_status ();
			}
			
			$group = Red_Group::get ($redirect->group_id);
			Red_Module::flush ($group->module_id);
		}
	}
	
	function move_redirects ($id)
	{
		if (count ($_POST['checkall']) > 0 && check_ajax_referer ('redirection-move_items'))
		{
			foreach ($_POST['checkall'] AS $item)
			{
				$redirect = Red_Item::get_by_id ($item);
				$redirect->move_to ($id);
			}
		}
	}
	
	function reset_redirects ($id)
	{
		if (count ($_POST['checkall']) > 0 && check_ajax_referer ('redirection-reset_items'))
		{
			foreach ($_POST['checkall'] AS $item)
			{
				$redirect = Red_Item::get_by_id (intval ($item));
				$redirect->reset ();
			}
		}
	}

	function save_redirect_order ($start)
	{
		if (check_ajax_referer ('redirection-save_item_order'))
			Red_Item::save_order ($_POST['item'], $start);
	}
	
	function reset_redirect ($id)
	{
		if (check_ajax_referer ('redirection-reset_items'))
		{
			$redirect = Red_Item::get_by_id ($id);
			if ($redirect)
			{
				$redirect->reset ();
				$this->render_admin ('item', array ('redirect' => $redirect, 'date_format' => get_option ('date_format')));
			}
		}
	}
	
	function show_log ($id)
	{
		$log      = RE_Log::get_by_id ($id);
		$redirect = Red_Item::get_by_id ($log->redirection_id);
		
		$this->render_admin ('log_item_details', array ('log' => $log, 'redirect' => $redirect));
	}
	
	function hide_log ($id)
	{
		$log = RE_Log::get_by_id ($id);
		echo '<a id="href_'.$id.'" href="'.$log->url.'" onclick="return toggle_log('.$id.')">'.$log->show_url ($log->url).'</a>';
	}

	function delete_logs ($id)
	{
		if (count ($_POST['checkall']) > 0  && check_ajax_referer ('redirection-delete_items'))
		{
			foreach ($_POST['checkall'] AS $item)
				RE_Log::delete (intval ($item));
		}
	}
	
	function delete_items ($id)
	{
		if (count ($_POST['checkall']) > 0 && check_ajax_referer ('redirection-delete_items'))
		{
			$item = Red_Item::get_by_id ($_POST['checkall'][0]);

			foreach ($_POST['checkall'] AS $item)
				Red_Item::delete (intval ($item));

			$group = Red_Group::get ($item->group_id);
			Red_Module::flush ($group->module_id);
		}
	}
	
	function delete_groups ($id)
	{
		if (count ($_POST['checkall']) > 0 && check_ajax_referer ('redirection-delete_items'))
		{
			foreach ($_POST['checkall'] AS $group)
				Red_Group::delete (intval ($group));
		
			$group = Red_Group::get ($group);
			Red_Module::flush ($group->module_id);
		}
	}

	function toggle_groups ($id)
	{
		if (count ($_POST['checkall']) > 0)
		{
			foreach ($_POST['checkall'] AS $group)
			{
				$group = Red_Group::get ($group);
				$group->toggle_status ();
			}
			
			Red_Module::flush ($group->module_id);
		}
	}

	function edit_group ($id)
	{
		$group = Red_Group::get ($id);
		if ($group)
			$this->render_admin ('group_edit', array ('group' => $group, 'modules' => Red_Module::get_for_select ()));
		else
			$this->render_admin ('error', array ('message' => __ ('Failed to retrieve group data', 'redirection'), 'file' => __FILE__, 'line' => __LINE__));
	}
	
	function show_group ($id)
	{
		$group = Red_Group::get ($id);
		if ($group)
			$this->render_admin ('group_item', array ('group' => $group));
		else
			$this->render_admin ('error', array ('message' => __ ('Failed to retrieve group data', 'redirection'), 'file' => __FILE__, 'line' => __LINE__));
	}
	
	function save_group_order ($start)
	{
		if (check_ajax_referer ('redirection-save_group_order'))
			Red_Group::save_order ($_POST['item'], $start);
	}
	
	function save_group ($id)
	{
		if (check_ajax_referer ('redirection-save_group'))
		{
			$group = Red_Group::get ($id);
			if ($group)
			{
				$original_module = $group->module_id;
				$group->update ($_POST);
		
				$this->render_admin ('group_item', array ('group' => $group));
			
				if ($group->status == 'enabled')
					echo '<script type="text/javascript" charset="utf-8">jQuery("#item_'.$id.'").removeClass ("disabled");</script>';
				else
					echo '<script type="text/javascript" charset="utf-8">jQuery("#item_'.$id.'").addClass ("disabled");</script>';
			}
			else
				$this->render_admin ('error', array ('message' => __ ('Failed to retrieve group data', 'redirection'), 'file' => __FILE__, 'line' => __LINE__));
		}
	}
	
	function move_groups ($id)
	{
		if (count ($_POST['checkall']) > 0 && check_ajax_referer ('redirection-move_items'))
		{
			foreach ($_POST['checkall'] AS $item)
			{
				$redirect = Red_Group::get ($item);
				$redirect->move_to ($id);
			}
		}
	}
	
	function reset_groups ($id)
	{
		if (count ($_POST['checkall']) > 0 && check_ajax_referer ('redirection-reset_items'))
		{
			foreach ($_POST['checkall'] AS $item)
			{
				$redirect = Red_Group::get (intval ($item));
				$redirect->reset ();
			}
		}
	}
	
	
	function edit_module ($id)
	{
		if (check_ajax_referer ('redirection-module_manage-'.$id))
		{
			$module = Red_Module::get ($id);
			if ($module)
				$this->render_admin ('module_edit', array ('module' => $module));
			else
				$this->render_admin ('error', array ('message' => __ ('Failed to retrieve module data', 'redirection'), 'file' => __FILE__, 'line' => __LINE__));
		}
	}
	
	function show_module ($id)
	{
		$module = Red_Module::get ($id);
		if ($module)
			$this->render_admin ('module_item', array ('module' => $module));
		else
			$this->render_admin ('error', array ('message' => __ ('Failed to retrieve group data', 'redirection'), 'file' => __FILE__, 'line' => __LINE__));
	}
	
	function save_module ($id)
	{
		if (check_ajax_referer ('redirection-edit_module-'.$id))
		{
			$module = Red_Module::get ($id);
			if ($module)
			{
				$module->update ($_POST);
		
				$this->render_admin ('module_item', array ('module' => $module));
			}
			else
				$this->render_admin ('error', array ('message' => __ ('Failed to retrieve group data', 'redirection'), 'file' => __FILE__, 'line' => __LINE__));
		}
	}
	
	function delete_module ($id)
	{
		if (check_ajax_referer ('redirection-module_manage-'.$id))
		{
			$module = Red_Module::get ($id);
			$module->delete ();
		}
	}
	
	function reset_module ($id)
	{
		if (check_ajax_referer ('redirection-module_manage-'.$id))
		{
			$module = Red_Module::get ($id);
			if ($module)
			{
				$module->reset ();
				$this->render_admin ('module_item', array ('module' => $module));
			}
			else
				$this->render_admin ('error', array ('message' => __ ('Failed to retrieve group data', 'redirection'), 'file' => __FILE__, 'line' => __LINE__));
		}
	}
}

$id  = $_GET['id'];
$cmd = $_GET['cmd'];

$obj = new Redirection_AJAX ($id, $cmd);

?>
