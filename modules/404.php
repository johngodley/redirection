<?php

class Error404_Module extends Red_Module
{
	var $log_404      = true;

	function start ()
	{
		add_action ('template_redirect', array (&$this, 'template_redirect'));
	}

	function load ($data)
	{
		$mine = array ('log_404');
		foreach ($mine AS $key)
		{
			if (isset ($data[$key]))
				$this->$key = $data[$key];
		}
	}

	function save ($data)
	{
		$save = array
		(
			'log_404' => isset ($data['log_404']) ? true : false
		);

		$this->load ($save);
		return $save;
	}

	function config ()
	{
		?>
		<tr>
			<th><label for="log_<?php echo $this->log_404 ?>"><?php _e ('Log 404s', 'redirection'); ?>:</label></th>
			<td><input id="log_<?php echo $this->log_404 ?>" type="checkbox" name="log_404" <?php if ($this->log_404) echo ' checked="checked"' ?>/></td>
		</tr>
		<?php
	}

	function options ()
	{
		if (!$this->is_valid ())
			echo __ ('<strong>Disabled: You must enable <a href="options-permalink.php">permalinks</a> before using this</strong>', 'redirection');
		else
		{
			$options = array ();

			if (!empty ($this->log_404))
				$options[] = '404s are logged';

			if (count ($options) > 0)
				echo '<small>'.ucfirst (implode (', ', $options)).'</small>';
			else
				echo __ ('<small>No options have been set</small>', 'redirection');
		}
	}

	function template_redirect ()
	{
		global $redirection;

		if (is_404 () && !$redirection->hasMatched ())
		{
			$url = $_SERVER['REQUEST_URI'];
			$redirects = Red_Item::get_for_url ($url, '404');
			if (!empty ($redirects))
			{
				foreach ($redirects AS $key => $item)
				{
					if ($item->matches ($url))
					{
						$redirection->setMatched (true);
						$this->matched = $item;
						break;
					}
				}
			}

			if (empty ($this->matched))
			{
				$modules = Red_Module::get_by_type ('404');

				if (count ($modules) > 0)
				{
					foreach ($modules AS $module)
					{
						// Log 404 errors
						if ($module->log_404)
						{
							if (isset ($_SERVER['REMOTE_ADDR']))
							  $myip = $_SERVER['REMOTE_ADDR'];
							else if (isset ($_SERVER['HTTP_X_FORWARDED_FOR']))
							  $myip = $_SERVER['HTTP_X_FORWARDED_FOR'];

							$options = $redirection->get_options ();
							if ($options['log_404s'])
								$log = RE_Log::create ($_SERVER['REQUEST_URI'], '', $_SERVER['HTTP_USER_AGENT'], $myip, isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '', 'NULL', $module->id);
						}
					}
				}
			}
		}
	}
}
?>
