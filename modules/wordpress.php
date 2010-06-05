<?php

class WordPress_Module extends Red_Module
{
	var $canonical    = 'default';
	var $strip_index  = 'default';
	var $error_level  = 'default';
	var $time_limit   = 0;
	var $matched;

	function start ()
	{
		// Setup the various filters and actions that allow Redirection to h appen
		add_action ('template_redirect',       array (&$this, 'template_redirect'));
		add_action ('init',                    array (&$this, 'init'));
		add_action ('send_headers',            array (&$this, 'send_headers'));
		add_filter ('permalink_redirect_skip', array (&$this, 'permalink_redirect_skip'));
		add_filter ('wp_redirect',             array (&$this, 'wp_redirect'), 1, 2);
		
		// Remove WordPress 2.3 redirection
		remove_action ('template_redirect', 'wp_old_slug_redirect');
		remove_action ('edit_form_advanced', 'wp_remember_old_slug');

		// A WP < 2.3 fix
		global $wp_db_version;
		if ($wp_db_version < 6000)
			add_filter ('status_header', array (&$this, 'status_header'));
	}
	
	function init ()
	{
		global $redirection;
		
		$url = $_SERVER['REQUEST_URI'];

		// Make sure we don't try and redirect something essential
		if (!$this->protected_url ($url) && !$redirection->hasMatched ()) {
			do_action ('redirection_first', $url, $this);

			$redirects = Red_Item::get_for_url( $url, 'wp' );

			if ( !empty( $redirects) ) {
				foreach ($redirects AS $key => $item) {
					if ( $item->matches( $url ) ) {
						global $redirection;
						
						$redirection->setMatched( true );
						$this->matched = $item;
						break;
					}
				}
			}

			do_action ('redirection_last', $url, $this);
		}
	}
	
	function protected_url ($url)
	{
		global $redirection;
		$part = explode ('?', $url);
		
		if ($part[0] == str_replace (get_bloginfo ('url'), '', $redirection->url ()).'/ajax.php' || strpos($url, 'wp-cron.php') !== false)
			return true;
		return false;
	}
	
	function template_redirect ()
	{
		// Load data
		$modules = Red_Module::get_by_type ('wp');
	
		if (count ($modules) > 0)
		{
			foreach ($modules AS $wp)
			{
				// Timeout
				if ($wp->time_limit != 'default')
					set_time_limit ($wp->time_limit);
		
				// Error level
				if ($wp->error_level == 'none')
					error_reporting (0);
				else if ($wp->error_level == 'show')
					error_reporting (E_ALL);
			
				// Mangle the URL, if needed
				$url      = (isset ($_SERVER['HTTPS']) && strtolower ($_SERVER['HTTPS']) == 'on') ? 'https://' : 'http://';
				$url     .= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
				$original = $url;
				
				if ($wp->canonical == 'www')
					$url = preg_replace ('@(https?)://(www)?\.?@', '$1://www.', $url);
				else if ($wp->canonical == 'nowww')
					$url = preg_replace ('@(https?)://(www)?\.?@', '$1://', $url);
			
				if ($wp->strip_index == 'yes')
					$url = preg_replace ('@index.(htm|html|php|asp|aspx|jsp)@', '', $url);
		
				if ($url != $original)
					wp_redirect ($url, 301);
			
				if ($this->matched)
					$this->matched->action->process_after ($this->matched->action_code, $original);
			}
		}
	}

	function status_header ($status)
	{
		// Fix for incorrect headers sent when using FastCGI/IIS
		if (substr (php_sapi_name (), 0, 3) == 'cgi')
			return str_replace ('HTTP/1.1', 'Status:', $status);
		return $status;
	}

	function send_headers ($obj)
	{
		if ( !empty($this->matched) && $this->matched->type == '410')
			status_header (410);
	}

	function wp_redirect ($url, $status)
	{
		global $wp_version, $is_IIS;
    if ( $wp_version < '2.1' ) {
    	status_header( $status );
			return $url;
    } elseif ( $is_IIS ) {
			header( "Refresh: 0;url=$url" );
			return $url;
		} else {
        if ( $status == 301 && php_sapi_name() == 'cgi-fcgi' ) {
            $servers_to_check = array( 'lighttpd', 'nginx' );
            foreach ( $servers_to_check as $name ) {
                if ( stripos( $_SERVER['SERVER_SOFTWARE'], $name ) !== false ) {
                    status_header( $status );
                    header( "Location: $url" );
                    exit( 0 );
                }
            }
        }

        status_header( $status );
				return $url;
    }
	}
	
	// XXX
	function permalink_redirect_skip ($skip)
	{
		// only want this if we:ve matched using redirection
		if ($this->matched)
			$skip[] = $_SERVER['REQUEST_URI'];
		return $skip;
	}
	
	
	function load ($data)
	{
		$mine = array ('canonical', 'strip_index', 'error_level', 'time_limit');
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
			'canonical'    => $data['canonical'],
			'strip_index'  => $data['strip_index'],
			'error_level'  => $data['error_level'],
			'time_limit'   => $data['time_limit'],
		);
		
		$this->load ($save);
		return $save;
	}
	
	function config ()
	{
		?>
		<tr>
			<th><?php _e ('Canonical', 'redirection'); ?>:</th>
			<td>
				<select name="canonical">
					<?php echo $this->select (array ('default' => __ ('Leave as is', 'redirection'), 'nowww' => sprintf (__ ('Strip WWW (%s)', 'redirection'), preg_replace ('@https?://(www)?@', '', get_bloginfo ('home'))), 'www' => sprintf (__ ('Force WWW (www.%s)', 'redirection'), preg_replace ('@https?://(www)?@', '', get_bloginfo ('home')))), $this->canonical); ?>
				</select>

				<strong><?php _e ('Strip Index', 'redirection'); ?>:</strong>
				<select name="strip_index">
					<?php echo $this->select (array ('default' => __ ('Leave as is', 'redirection'), 'yes' => __ ('Strip index files (html,php,asp)', 'redirection')), $this->strip_index); ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><?php _e ('Time Limit', 'redirection') ?>:</th>
			<td>
				<select name="time_limit">
					<?php echo $this->select (array ('default' => __ ('Server default', 'redirection'), '30' => __ ('30 seconds', 'redirection'), '60' => __ ('1 minute', 'redirection'), '120' => __ ('2 minutes', 'redirection'), (5 * 60) => __ ('5 minutes', 'redirection'), '0' => __ ('As long as possible', 'redirection')), $this->time_limit); ?>
				</select>
				
				<strong><?php _e ('Error Level', 'redirection'); ?>:</strong>
				<select name="error_level">
					<?php echo $this->select (array ('default' => __ ('Server default', 'redirection'), 'none' => 'No errors', 'error' => 'Show errors'), $this->error_level); ?>
				</select>
			</td>
		</tr>
		<?php
	}
	
	function is_valid ()
	{
		$perm = get_option ('permalink_structure');
		if ($perm === false || $perm == '')
			return false;
		return true;
	}
	
	function options ()
	{
		if (!$this->is_valid ())
			echo __ ('<strong>Disabled: You must enable <a href="options-permalink.php">permalinks</a> before using this</strong>', 'redirection');
		else
		{
			$options = array ();
			if ($this->canonical != 'default' && !empty ($this->canonical))
				$options[] = ($this->canonical == 'nowww') ? 'strip WWW' : 'force WWW';
			
			if ($this->strip_index != 'default' && !empty ($this->strip_index))
				$options[] = __ ('strip index', 'redirection');
			
			if ($this->time_limit != 'default')
			{
				if ($this->time_limit == 0)
					$options[] = __ ('time limit set as long as possible', 'redirection');
				else
					$options[] = sprintf (__ ('time limit at %ss', 'redirection'), $this->time_limit);
			}
			
			if ($this->error_level != 'default' && !empty ($this->error_level))
				$options[] = ($this->error_level == 'none') ? __ ('no errors', 'redirection') : __ ('show errors', 'redirection');
			
			if (count ($options) > 0)
				echo '<small>'.ucfirst (implode (', ', $options)).'</small>';
			else
				echo __ ('<small>No options have been set</small>', 'redirection');
		}
	}
}
?>