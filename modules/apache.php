<?php

class Apache_Module extends Red_Module
{
	var $name;
	var $allow_ip  = false;
	var $raw;
	var $ban_ip = false;

	var $site      = '';
	var $location  = '';
	var $canonical = '';
	var $strip_index = '';
	var $memory_limit = '';
	var $error_level  = '';

	function is_valid ()
	{
		if (!$this->location || !file_exists (dirname ($this->location)) && !is_writable (dirname ($this->location)))
			return false;
		return true;
	}

	function load ($data)
	{
		$mine = array ('location', 'canonical', 'strip_index', 'memory_limit', 'error_level', 'ban_ip', 'allow_ip', 'raw', 'site');
		foreach ($mine AS $key)
		{
			if (isset ($data[$key]))
				$this->$key = $data[$key];
		}
	}

	function module_flush_delete ()
	{
		@unlink ($this->location);
	}

	function module_flush ($items)
	{
		// Produce the .htaccess file
		include_once (dirname (__FILE__).'/../models/htaccess.php');

		$htaccess = new Red_Htaccess ($this);
		if (is_array ($items) && count ($items) > 0)
		{
			foreach ($items AS $item)
				$htaccess->add ($item);
		}

		$htaccess->save ($this->location, $this->name);
	}

	function save ($data)
	{
		$save = array
		(
			'location'     => $data['location'],
			'canonical'    => $data['canonical'],
			'strip_index'  => $data['strip_index'],
			'memory_limit' => $data['memory_limit'],
			'error_level'  => $data['error_level'],
			'ban_ip'       => $data['ban_ip'],
			'allow_ip'     => $data['allow_ip'],
			'raw'          => $data['raw'],
			'site'         => preg_replace ('@https?://@', '', $data['site'])
		);

		$this->load ($save);
		return $save;
	}

	function config ()
	{
		?>
		<tr>
			<th valign="top"><?php _e ('Location', 'redirection'); ?>:</th>
			<td>
				<input type="text" name="location" value="<?php echo htmlspecialchars ($this->location) ?>" style="width: 95%"/>
				<?php if ($this->location == '') : ?>
					<br/>
					<span class="sub"><?php printf (__ ('WordPress is installed in: <code>%s</code>', 'redirection'), ABSPATH); ?></span>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th><?php _e ('Canonical', 'redirection'); ?>:</th>
			<td>
				<select name="canonical">
					<?php echo $this->select (array ('default' => __ ('Leave as is', 'redirection'), 'nowww' => sprintf (__ ('Strip WWW (%s)', 'redirection'), preg_replace ('@https?://(www)?@', '', get_bloginfo ('url'))), 'www' => sprintf (__ ('Force WWW (www.%s)', 'redirection'), preg_replace ('@https?://(www)?@', '', get_bloginfo ('url')))), $this->canonical); ?>
				</select>

				<br/>
				<strong><?php _e ('Strip Index', 'redirection'); ?>:</strong>
				<select name="strip_index">
					<?php echo $this->select (array ('default' => __ ('Leave as is', 'redirection'), 'yes' => __ ('Strip index files (html,php)', 'redirection')), $this->strip_index); ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><?php _e ('Memory Limit', 'redirection'); ?>:</th>
			<td>
				<select name="memory_limit">
					<?php echo $this->select (array ('0' => __ ('Server default', 'redirection'), '8' => '8MB', '16' => '16MB', '32' => '32MB', '64' => '64MB', '128' => '128MB'), $this->memory_limit); ?>
				</select>

				<strong><?php _e ('Error Level', 'redirection'); ?>:</strong>
				<select name="error_level">
					<?php echo $this->select (array ('default' => __ ('Server default', 'redirection'), 'none' => __ ('No errors', 'redirection'), 'error' => __ ('Show errors', 'redirection')), $this->error_level); ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><?php _e ('Ban IPs', 'redirection'); ?>:</th>
			<td>
				<input type="text" name="ban_ip" value="<?php echo htmlspecialchars ($this->ban_ip) ?>" style="width: 95%"/>
			</td>
		</tr>
		<tr>
			<th><?php _e ('Allow IPs', 'redirection'); ?>:</th>
			<td>
				<input type="text" name="allow_ip" value="<?php echo htmlspecialchars ($this->allow_ip) ?>" style="width: 95%"/>
			</td>
		</tr>
		<tr>
			<th valign="top"><?php _e ('Raw .htaccess', 'redirection'); ?>:</th>
			<td>
				<textarea style="width: 95%" name="raw"><?php echo htmlspecialchars ($this->raw)?></textarea>
			</td>
		</tr>
		<tr>
			<th><?php _e ('Site URL', 'redirection'); ?>:</th>
			<td>
				<input type="text" size="40" name="site" value="<?php echo htmlspecialchars ($this->site) ?>"/>
				<span class="sub"><?php _e ('Advanced: For management of external sites', 'redirection'); ?></span>
			</td>
		</tr>

		<?php
	}

	function options ()
	{
		echo '<p>';
		if ($this->location)
		{
			if (!file_exists (dirname ($this->location)))
			{
				echo '<code>'.$this->location.'</code></p>';
				echo __ ('<strong>Location is invalid - check that path exists</strong>', 'redirection');
				return;
			}
			else if ((file_exists ($this->location) && !is_writable ($this->location)) || (!file_exists ($this->location) && !is_writable (dirname ($this->location))))
			{
				echo '<code>'.$this->location.'</code></p>';
				echo __ ('<strong>Could not write to configured <code>.htaccess</code> file - check file permissions</strong>', 'redirection');
				return;
			}
			else
				echo '<code>'.$this->location.'</code>';
		}
		else
			echo __ ('<strong>Disabled: enter the location of an <code>.htaccess</code> file for this to be valid</strong>', 'redirection');
		echo '</p>';

		$options = array ();
		if ($this->canonical != 'default' && !empty ($this->canonical))
			$options[] = ($this->canonical == 'nowww') ? __ ('strip WWW', 'redirection') : __ ('force WWW', 'redirection');

		if ($this->strip_index != 'default' && !empty ($this->strip_index))
			$options[] = __ ('strip index', 'redirection');

		if ($this->memory_limit > 0 && !empty ($this->memory_limit))
			$options[] = sprintf (__ ('memory limit at %dMB', 'redirection'), $this->memory_limit);

		if ($this->error_level != 'default' && !empty ($this->error_level))
			$options[] = ($this->error_level == 'none') ? __ ('no errors', 'redirection') : __ ('show errors', 'redirection');

		if (!empty ($this->ban_ip))
			$options[] = __ ('IPs are banned', 'redirection');

		if (!empty ($this->allow_ip))
			$options[] = __ ('IPs are allowed', 'redirection');

		echo '<small>'.ucfirst (implode (', ', $options)).'</small>';
	}
}
