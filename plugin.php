<?php

// ======================================================================================
// This library is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public
// License as published by the Free Software Foundation; either
// version 2.1 of the License, or (at your option) any later version.
// 
// This library is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// Lesser General Public License for more details.
// ======================================================================================
// @author     John Godley (http://urbangiraffe.com)
// @version    0.1.13
// @copyright  Copyright &copy; 2007 John Godley, All Rights Reserved
// ======================================================================================
// 0.1.6 - Corrected WP locale functions
// 0.1.7 - Add phpdoc comments
// 0.1.8 - Support for Admin SSL
// 0.1.9 - URL encoding, defer localization until init
// 0.1.10 - Better URL encoding
// 0.1.11 - Make work in WP 2.0, fix HTTPS issue on IIS
// 0.1.12 - Activation/deactivation actions that take into account the directory
// 0.1.13 - Add realpath function
// ======================================================================================


/**
 * Wraps up several useful functions for WordPress plugins and provides a method to separate
 * display HTML from PHP code.
 *
 * <h4>Display Rendering</h4>
 * The class uses a similar technique to Ruby On Rails views, whereby the display HTML is kept
 * in a separate directory and file from the main code.  A display is 'rendered' (sent to the browser)
 * or 'captured' (returned to the calling function).
 * 
 * Template files are separated into two areas: admin and user.  Admin templates are only for display in
 * the WordPress admin interface, while user templates are typically for display on the site (although neither
 * of these are enforced).  All templates are PHP code, but are referred to without .php extension.
 * 
 * The reason for this separation is that one golden rule of plugin creation is that someone will always want to change
 * the formatting and style of your output.  Rather than forcing them to modify the plugin (bad), or modify files within
 * the plugin (equally bad), the class allows user templates to be overridden with files contained within the theme.
 *
 * An additional benefit is that it leads to code re-use, especially with regards to Ajax (i.e. your display code can be called from
 * many locations)
 * 
 * Template files are located within the 'view' subdirectory of the plugins base (specified when registering the plugin):
 * 
 * <pre>myplugin/view/admin
 * myplugin/view/myplugin</pre>
 * 
 * Admin templates are contained within 'admin', and user templates are contained within a directory of the same name as the plugin.
 * 
 * User files can be overridden within the theme by creating a similar directory structure:
 * 
 * <pre>/themes/mytheme/view/myplugin</pre>
 * 
 * The class will first look in the theme and then defaults to the plugin.  A plugin should always provide default templates.
 * 
 * <h4>Display Parameters</h4>
 * Also similar to Ruby On Rails, when you display a template you must supply the parameters that the template has access to.  This tries
 * to ensure a very clean separation between code and display.  Parameters are supplied as an associative array mapping variable name to variable value.
 * 
 * For example,
 * 
 * array ('message' => 'Your data was processed', 'items' => 103);
 *
 * <h4>How it works in practice</h4>
 * You create a template file to display how many items have been processed.  You store this in 'view/admin/processed.php':
 *
 * <pre>&lt;p&gt;You processed &lt;?php echo $items ?&gt; items&lt;/p&gt;</pre>
 * 
 * When you want to display this in your plugin you use:
 *
 * <pre> $this->render_admin ('processed', array ('items' => 100));
 *
 * @package WordPress base library
 * @author John Godley
 * @copyright Copyright (C) John Godley
 **/

class Redirection_Plugin
{
	/**
	 * Plugin name
	 * @var string
	 **/
	var $plugin_name;
	
	/**
	 * Plugin 'view' directory
	 * @var string Directory
	 **/
	var $plugin_base;
	
	
	/**
	 * Register your plugin with a name and base directory.  This <strong>must</strong> be called once.
	 *
	 * @param string $name Name of your plugin.  Is used to determine the plugin locale domain
	 * @param string $base Directory containing the plugin's 'view' files.
	 * @return void
	 **/
	
	function register_plugin ($name, $base)
	{
		$this->plugin_base = rtrim (dirname ($base), '/');
		$this->plugin_name = $name;

		$this->add_action ('init');
	}
	
	function init ()
	{
		// Here we manually fudge the plugin locale as WP doesnt allow many options
		$locale = get_locale ();
		if ( empty($locale) )
			$locale = 'en_US';

		$mofile = dirname (__FILE__)."/locale/$locale.mo";
		load_textdomain ($name, $mofile);
	}
	
	
	/**
	 * Register a WordPress action and map it back to the calling object
	 *
	 * @param string $action Name of the action
	 * @param string $function Function name (optional)
	 * @param int $priority WordPress priority (optional)
	 * @param int $accepted_args Number of arguments the function accepts (optional)
	 * @return void
	 **/
	
	function add_action ($action, $function = '', $priority = 10, $accepted_args = 1)
	{
		add_action ($action, array (&$this, $function == '' ? $action : $function), $priority, $accepted_args);
	}


	/**
	 * Register a WordPress filter and map it back to the calling object
	 *
	 * @param string $action Name of the action
	 * @param string $function Function name (optional)
	 * @param int $priority WordPress priority (optional)
	 * @param int $accepted_args Number of arguments the function accepts (optional)
	 * @return void
	 **/
	
	function add_filter ($filter, $function = '', $priority = 10, $accepted_args = 1)
	{
		add_filter ($filter, array (&$this, $function == '' ? $filter : $function), $priority, $accepted_args);
	}


	/**
	 * Special activation function that takes into account the plugin directory
	 *
	 * @param string $pluginfile The plugin file location (i.e. __FILE__)
	 * @param string $function Optional function name, or default to 'activate'
	 * @return void
	 **/
	
	function register_activation ($pluginfile, $function = '')
	{
		add_action ('activate_'.basename (dirname ($pluginfile)).'/'.basename ($pluginfile), array (&$this, $function == '' ? 'activate' : $function));
	}
	
	
	/**
	 * Special deactivation function that takes into account the plugin directory
	 *
	 * @param string $pluginfile The plugin file location (i.e. __FILE__)
	 * @param string $function Optional function name, or default to 'deactivate'
	 * @return void
	 **/
	
	function register_deactivation ($pluginfile, $function = '')
	{
		add_action ('deactivate_'.basename (dirname ($pluginfile)).'/'.basename ($pluginfile), array (&$this, $function == '' ? 'deactivate' : $function));
	}
	
	
	/**
	 * Renders an admin section of display code
	 *
	 * @param string $ug_name Name of the admin file (without extension)
	 * @param string $array Array of variable name=>value that is available to the display code (optional)
	 * @return void
	 **/
	
	function render_admin ($ug_name, $ug_vars = array ())
	{
		global $plugin_base;
		foreach ($ug_vars AS $key => $val)
			$$key = $val;

		if (file_exists ("{$this->plugin_base}/view/admin/$ug_name.php"))
			include ("{$this->plugin_base}/view/admin/$ug_name.php");
		else
			echo "<p>Rendering of admin template {$this->plugin_base}/view/admin/$ug_name.php failed</p>";
	}


	/**
	 * Renders a section of user display code.  The code is first checked for in the current theme display directory
	 * before defaulting to the plugin
	 *
	 * @param string $ug_name Name of the admin file (without extension)
	 * @param string $array Array of variable name=>value that is available to the display code (optional)
	 * @return void
	 **/
	
	function render ($ug_name, $ug_vars = array ())
	{
		foreach ($ug_vars AS $key => $val)
			$$key = $val;

		if (file_exists (TEMPLATEPATH."/view/{$this->plugin_name}/$ug_name.php"))
			include (TEMPLATEPATH."/view/{$this->plugin_name}/$ug_name.php");
		else if (file_exists ("{$this->plugin_base}/view/{$this->plugin_name}/$ug_name.php"))
			include ("{$this->plugin_base}/view/{$this->plugin_name}/$ug_name.php");
		else
			echo "<p>Rendering of template $ug_name.php failed</p>";
	}
	
	
	/**
	 * Renders a section of user display code.  The code is first checked for in the current theme display directory
	 * before defaulting to the plugin
	 *
	 * @param string $ug_name Name of the admin file (without extension)
	 * @param string $array Array of variable name=>value that is available to the display code (optional)
	 * @return void
	 **/

	function capture ($ug_name, $ug_vars = array ())
	{
		ob_start ();
		$this->render ($ug_name, $ug_vars);
		$output = ob_get_contents ();
		ob_end_clean ();
		return $output;
	}
	

	/**
	 * Captures an admin section of display code
	 *
	 * @param string $ug_name Name of the admin file (without extension)
	 * @param string $array Array of variable name=>value that is available to the display code (optional)
	 * @return string Captured code
	 **/

	function capture_admin ($ug_name, $ug_vars = array ())
	{
		ob_start ();
		$this->render_admin ($ug_name, $ug_vars);
		$output = ob_get_contents ();
		ob_end_clean ();
		return $output;
	}
	
	
	/**
	 * Display a standard error message (using CSS ID 'message' and classes 'fade' and 'error)
	 *
	 * @param string $message Message to display
	 * @return void
	 **/
	
	function render_error ($message)
	{
	?>
<div class="fade error" id="message">
 <p><?php echo $message ?></p>
</div>
<?php
	}
	
	
	/**
	 * Display a standard notice (using CSS ID 'message' and class 'updated').
	 * Note that the notice can be made to automatically disappear, and can be removed
	 * by clicking on it.
	 *
	 * @param string $message Message to display
	 * @param int $timeout Number of seconds to automatically remove the message (optional)
	 * @return void
	 **/
	
	function render_message ($message, $timeout = 0)
	{
		?>
<div class="updated" id="message" onclick="Element.remove ('message')">
 <p><?php echo $message ?></p>
<?php if ($timeout > 0) : ?>
<script type="text/javascript" charset="utf-8">
	new PeriodicalExecuter (function (pe)
	{
		pe.stop ();
		Effect.Fade ('message');
	}, <?php echo $timeout ?>); 
</script>
<?php endif; ?>
<script type="text/javascript" charset="utf-8">
	new Effect.Pulsate ('message', { pulses: 4, duration: 5, from: 0.1});
</script>
</div>
	<?php	
	}


	/**
	 * Get the plugin's base directory
	 *
	 * @return string Base directory
	 **/
	
	function dir ()
	{
		return $this->plugin_base;
	}
	
	
	/**
	 * Get a URL to the plugin.  Useful for specifying JS and CSS files
	 *
	 * For example, <img src="<?php echo $this->url () ?>/myimage.png"/>
	 *
	 * @return string URL
	 **/
	
	function url ($url = '')
	{
		if ($url)
			return str_replace ('\\', urlencode ('\\'), str_replace ('&amp;amp', '&amp;', str_replace ('&', '&amp;', $url)));
		else
		{
			$url = substr ($this->plugin_base, strlen (realpath (ABSPATH)));
			if (DIRECTORY_SEPARATOR != '/')
				$url = str_replace (DIRECTORY_SEPARATOR, '/', $url);
			$url = get_bloginfo ('wpurl').$url;
		
			// Do an SSL check - only works on Apache
			global $is_IIS;
			if (isset ($_SERVER['HTTPS']) && !$is_IIS)
				$url = str_replace ('http://', 'https://', $url);
		}
		return $url;
	}

	
	
	/**
	 * Performs a version update check using an RSS feed.  The function ensures that the feed is only
	 * hit once every given number of days, and the data is cached using the WordPress Magpie library
	 *
	 * @param string $url URL of the RSS feed
	 * @param int $days Number of days before next check
	 * @return string Text to display
	 **/
	
	function version_update ($url, $days = 7)
	{
		if (!function_exists ('fetch_rss'))
		{
			if (!file_exists (ABSPATH.'wp-includes/rss.php'))
				return '';
			include (ABSPATH.'wp-includes/rss.php');
		}

		$now = time ();
		
		$checked = get_option ('plugin_urbangiraffe_rss');
	
		// Use built-in Magpie caching
		if (!isset ($checked[$this->plugin_name]) || $now > $checked[$this->plugin_name] + ($days * 24 * 60 * 60))
		{
			$rss = fetch_rss ($url);
			if (count ($rss->items) > 0)
			{
				foreach ($rss->items AS $pos => $item)
				{
					if (isset ($checked[$this->plugin_name]) && strtotime ($item['pubdate']) < $checked[$this->plugin_name])
						unset ($rss->items[$pos]);
				}
			}
		
			$checked[$this->plugin_name] = $now;
			update_option ('plugin_urbangiraffe_rss', $checked);
			return $rss;
		}
	}
	
	
	/**
	 * Version of realpath that will work on systems without realpath
	 *
	 * @param string $path The path to canonicalize
	 * @return string Canonicalized path
	 **/
	
	function realpath ($path)
	{
		if (function_exists ('realpath'))
			return realpath ($path);
		else if (DIRECTORY_SEPARATOR == '/')
		{
	    // canonicalize
	    $path = explode (DIRECTORY_SEPARATOR, $path);
	    $newpath = array ();
	    for ($i = 0; $i < sizeof ($path); $i++)
			{
				if ($path[$i] === '' || $path[$i] === '.')
					continue;
					
				if ($path[$i] === '..')
				{
					array_pop ($newpath);
					continue;
				}
				
				array_push ($newpath, $path[$i]);
	    }
	
	    $finalpath = DIRECTORY_SEPARATOR.implode (DIRECTORY_SEPARATOR, $newpath);
      return $finalpath;
		}
		
		return $path;
	}
}

?>