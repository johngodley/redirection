<?php

class Red_Group
{
	function Red_Group ($values = '')
	{
		if (is_array ($values))
		{
			foreach ($values AS $key => $value)
			 	$this->$key = $value;
		}
	}
	
	function get ($id)
	{
		global $wpdb;
		
		$id = intval ($id);
		$row = $wpdb->get_row ("SELECT {$wpdb->prefix}redirection_groups.*,COUNT({$wpdb->prefix}redirection_items.id) AS items,SUM({$wpdb->prefix}redirection_items.last_count) AS redirects FROM {$wpdb->prefix}redirection_groups LEFT JOIN {$wpdb->prefix}redirection_items ON {$wpdb->prefix}redirection_items.group_id={$wpdb->prefix}redirection_groups.id WHERE {$wpdb->prefix}redirection_groups.id='$id' GROUP BY {$wpdb->prefix}redirection_groups.id", ARRAY_A);
		if ($row)
			return new Red_Group ($row);
		return false;
	}
	
	function get_for_module ($module)
	{
		global $wpdb;
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS {$wpdb->prefix}redirection_groups.* FROM {$wpdb->prefix}redirection_groups WHERE {$wpdb->prefix}redirection_groups.module_id='$module'";

		$rows = $wpdb->get_results ($sql, ARRAY_A);
		$items = array ();
		if (count ($rows) > 0)
		{
			foreach ($rows AS $row)
				$items[] = new Red_Group ($row);
		}

		return $items;
	}
	
	function get_all ($module, $pager)
	{
		global $wpdb;
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS {$wpdb->prefix}redirection_groups.*,COUNT({$wpdb->prefix}redirection_items.id) AS items,SUM({$wpdb->prefix}redirection_items.last_count) AS redirects FROM {$wpdb->prefix}redirection_groups LEFT JOIN {$wpdb->prefix}redirection_items ON {$wpdb->prefix}redirection_items.group_id={$wpdb->prefix}redirection_groups.id WHERE {$wpdb->prefix}redirection_groups.module_id='$module'";
		
		$pager->set_total ($wpdb->get_var ("SELECT FOUND_ROWS()"));
		$sql .= $pager->to_limits ('', array ('name'), '', "GROUP BY {$wpdb->prefix}redirection_groups.id");

		$rows = $wpdb->get_results ($sql, ARRAY_A);
		$items = array ();
		if (count ($rows) > 0)
		{
			foreach ($rows AS $row)
				$items[] = new Red_Group ($row);
		}

		return $items;
	}
	
	function get_for_select ()
	{
		global $wpdb;
		
		$data = array ();
		$rows = $wpdb->get_results ("SELECT {$wpdb->prefix}redirection_modules.name AS module_name,{$wpdb->prefix}redirection_groups.name AS group_name,{$wpdb->prefix}redirection_groups.id FROM {$wpdb->prefix}redirection_groups INNER JOIN {$wpdb->prefix}redirection_modules ON {$wpdb->prefix}redirection_modules.id={$wpdb->prefix}redirection_groups.module_id ORDER BY {$wpdb->prefix}redirection_modules.name,{$wpdb->prefix}redirection_groups.position");
		if ($rows)
		{
			foreach ($rows AS $row)
				$data[$row->module_name][$row->id] = $row->group_name;
		}
		
		return $data;
	}
	
	function get_first_id ()
	{
		global $wpdb;
		
		return $wpdb->get_var ("SELECT id FROM {$wpdb->prefix}redirection_groups ORDER BY id LIMIT 0,1");
	}

	function create ($data)
	{
		global $wpdb;
		
		$name   = $wpdb->escape (trim ($data['name']));
		$module = intval ($data['module_id']);

		if ($name != '' && $module > 0)
		{
			if (isset ($data['status']) && isset ($data['position']))
			{
				$extra  = ',status';
				$extra2 = ",'".$data['status']."'";
			}
			
			$position = $wpdb->get_var ("SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups WHERE module_id='$module'");
			if (isset ($data['position']))
				$position = $data['position'];
				
			$wpdb->query ("INSERT INTO {$wpdb->prefix}redirection_groups (name,module_id,position$extra) VALUES ('$name','$module','$position'$extra2)");
			
			Red_Module::flush ($module);
			return $wpdb->insert_id;
		}
		
		return false;
	}
	
	function update ($data)
	{
		global $wpdb;
		
		$this->tracking = isset ($data['tracking']) ? true : false;
		$this->name     = $data['name'];
		$this->status   = isset ($data['status']) ? 'enabled' : 'disabled';
		
		$name = $wpdb->escape ($this->name);
		
		$wpdb->query ("UPDATE {$wpdb->prefix}redirection_groups SET name='$name', status='{$this->status}', tracking='{$this->tracking}' WHERE id='{$this->id}'");
		
		Red_Module::flush ($this->module_id);
	}
	
	function delete ($group)
	{
		global $wpdb;
		
		$obj = Red_Group::get ($group);
		
		// Delete all items in this group
		$wpdb->query ("DELETE FROM {$wpdb->prefix}redirection_items WHERE group_id='$group'");

		// Delete the group
		$wpdb->query ("DELETE FROM {$wpdb->prefix}redirection_groups WHERE id='$group'");
		
		// Update positions
		$rows = $wpdb->get_results ("SELECT id FROM {$wpdb->prefix}redirection_groups ORDER BY position");
		if (count ($rows) > 0)
		{
			foreach ($rows AS $pos => $row)
				$wpdb->query ("UPDATE {$wpdb->prefix}redirection_groups SET position='$pos' WHERE id='{$row->id}'");
		}
	}
	
	function save_order ($items, $start)
	{
		global $wpdb;
		foreach ($items AS $pos => $id)
			$wpdb->query ("UPDATE {$wpdb->prefix}redirection_groups SET position='".($pos + $start)."' WHERE id='{$id}'");
		
		$group = Red_Group::get ($items[0]);
		Red_Module::flush ($group->module_id);
	}
	
	function move_to ($module)
	{
		global $wpdb;

		$wpdb->query ("UPDATE {$wpdb->prefix}redirection_groups SET module_id='$module' WHERE id='{$this->id}'");
		
		Red_Module::flush ($module);
		Red_Module::flush ($this->id);
	}
	
	function reset ()
	{
		global $wpdb;
		
		$this->last_count  = 0;
		$this->last_access = '0000-00-00 00:00:00';
		
		$wpdb->query ("UPDATE {$wpdb->prefix}redirection_items SET last_count=0, last_access='{$this->last_access}' WHERE group_id='{$this->id}'");
		
		RE_Log::delete_for_group ($this->id);
	}
	
	function items ()
	{
		if ($this->items > 0)
			return sprintf (' (%d)', $this->items);
		return '';
	}
	
	function type ()
	{
		if ($this->apache)
			return '.ht';
		return 'WP';
	}
	
	function tracked ()
	{
		if ($this->tracking == 1)
			return 'Yes';
		return 'No';
	}
	
	function toggle_status ()
	{
		global $wpdb;

		$this->status = ($this->status == 'enabled') ? 'disabled' : 'enabled';
		
		$wpdb->query ("UPDATE {$wpdb->prefix}redirection_groups SET status='{$this->status}' WHERE id='{$this->id}'");
	}
	
	function hits ()
	{
		global $wpdb;
//		$count = $wpdb->get_var ("SELECT SUM(last_count) FROM {$wpdb->prefix}redirection_groups INNER JOIN {$wpdb->prefix}redirection_items ON {$wpdb->prefix}redirection_items.group_id=wp_redirection_groups.id WHERE wp_redirection_groups.module_id='{$this->id}'");
		$count = $wpdb->get_var ("SELECT COUNT(id) FROM {$wpdb->prefix}redirection_logs WHERE group_id='{$this->id}'");
		if ($count > 0)
			return $count;
		return 0;
	}
}
?>