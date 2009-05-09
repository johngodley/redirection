<?php

class Red_Xml_File extends Red_FileIO
{
	function collect ($module)
	{
		$this->name    = $module->name;
		$this->id      = $module->id;
		$this->type    = $module->type;
		$this->options = unserialize ($module->options);
		
		if (!is_array ($this->options))
			$this->options = array ();

		$this->groups = Red_Group::get_for_module ($module->id);
		if (is_array ($this->groups) && count ($this->groups) > 0)
		{
			$pager = new RE_Pager ($_GET, $_SERVER['REQUEST_URI'], 'position', 'ASC', 'log');
			$pager->per_page = 0;

			foreach ($this->groups AS $pos => $group)
				$this->groups[$pos]->items = Red_Item::get_by_group ($group->id, $pager);
		}
		else
			$this->groups = array ();
			
		return true;
	}

	function feed ()
	{
		$filename = sprintf (__ ('module_%d.xml', 'redirection'), $this->id);

		header ("Content-Type: text/xml");
		header ("Cache-Control: no-cache, must-revalidate");
		header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
//	 	header ('Content-Disposition: attachment; filename="'.$filename.'"');

		echo '<?xml version="1.0" encoding="utf-8"?>';
		?>
<redirection>
	<module name="<?php echo htmlspecialchars ($this->name) ?>" id="<?php echo $this->id ?>" type="<?php echo $this->type ?>">
		<?php if (count ($this->options) > 0) : ?>
		<options>
			<?php foreach ($this->options AS $name => $value) : ?>
			<option name="<?php echo $name ?>"><?php echo htmlspecialchars ($value) ?></option>
			<?php endforeach; ?>
		</options>
		<?php endif; ?>

		<?php if (count ($this->groups) > 0) :?>
			<?php foreach ($this->groups AS $group) : ?>
				<group id="<?php echo $group->id ?>" name="<?php echo htmlspecialchars ($group->name) ?>" status="<?php echo $group->status ?>" position="<?php echo $group->position ?>" tracking="<?php echo $group->tracking ?>">
					<?php if (count ($group->items) > 0) : ?>
						<?php foreach ($group->items AS $item) $this->output_item ($item); ?>
					<?php endif; ?>
				</group>
			<?php endforeach; ?>
		<?php endif; ?>
	</module>
</redirection>	
		<?php
	}
	
	function output_item ($item)
	{
		$data = unserialize ($item->action_data);
		?>
<item id="<?php echo $item->id ?>" position="<?php echo $item->position ?>" status="<?php echo $item->status ?>">
	<source><?php echo htmlspecialchars ($item->url) ?></source>
	<match type="<?php echo $item->match_type ?>" regex="<?php echo $item->regex ?>"></match>
	<action type="<?php echo $item->action_type ?>" code="<?php echo $item->action_code ?>">
		<?php if (is_array ($data) && count ($data) > 0) : ?>xxx
			<?php foreach ($data AS $key => $value) : ?>
				<option name="'.$key.'">
					<?php if (is_array ($value)) : ?>
						<?php echo htmlspecialchars (serialize ($value)); ?>
					<?php else : ?>
						<?php echo htmlspecialchars ($value); ?>
					<?php endif; ?>
				</option>
			<?php endforeach; ?>
		<?php else: ?>
			<?php echo htmlspecialchars ($item->action_data); ?>
		<?php endif; ?>
	</action>
	<statistic count="<?php echo $item->last_count ?>" access="<?php echo $item->last_access ?>"/>
</item>
<?php
	}

	function load ($group, $data)
	{
		$count = 0;
		if (function_exists ('simplexml_load_string'))
		{
			global $wpdb;
			
			$xml = simplexml_load_string ($data);

			// Extract module
			$moduledata = array
			(
				'type' => (string)$xml->module['type'],
				'name' => sprintf (__ ('%s imported on %s at %s', 'redirection'), (string)$xml->module['name'], date ('M d Y'), date ('H:i'))
			);
			
			if (isset ($xml->module->options))
			{
				foreach ($xml->module->options->option AS $option)
					$options[(string)$option['name']] = trim ((string)$option);
					
				$moduledata['options'] = $options;
			}

			$module = Red_Module::create ($moduledata);
			
			// Look at groups
			if (count ($xml->module->group) > 0)
			{
				foreach ($xml->module->group AS $group)
				{
					$id = Red_Group::create (array ('module_id' => $module, 'name' => (string)$group['name'], 'status' => (string)$group['status'], 'position' => (string)$group['position']));
					
					// Look at items
					if (count ($group->item) > 0)
					{
						foreach ($group->item AS $item)
						{
							$actiondata = array ();
							if (isset ($item->action->option) && count ($item->action->option) > 0)
							{
								foreach ($item->action->option AS $option)
									$actiondata[(string)$option['key']] = trim ((string)$option);
									
								$actiondata = serialize ($actiondata);
							}
							else
								$actiondata = trim ((string)$item->action);
							
							$data = array
							(
								'group_id'    => $id,
								'url'         => trim ((string)$item->source),
								'position'    => intval ((string)$item['position']),
								'status'      => (string)$item['status'],
								'regex'       => (string)$item->match['regex'],
								'match_type'  => (string)$item->match['type'],
								'action_type' => (string)$item->action['type'],
								'action_code' => (string)$item->action['code'],
								'action_data' => $actiondata
							);
							
							foreach ($data AS $key => $value)
								$data[$key] = "'".$wpdb->escape ($value)."'";
							
							// Easier to insert it directly here
							$wpdb->query ("INSERT INTO {$wpdb->prefix}redirection_items (".implode (',', array_keys ($data)).") VALUES (".implode (',', $data).")");
							$count++;
						}
					}
				}
			}
		}
		else
		{
			global $redirection;
			$redirection->render_error (__ ('XML importing is only available with PHP5 - you have PHP4.', 'redirection'));
		}

		return $count;
	}
}
?>