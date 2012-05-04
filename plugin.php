<?php

// ======================================================================================
// This library is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public
// License as published by the Free Software Foundation; either
// version 2.1 of the License, or(at your option) any later version.
//
// This library is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// Lesser General Public License for more details.
// ======================================================================================
// @author     John Godley(http://urbangiraffe.com)
// @version    0.2.7
// @copyright  Copyright &copy; 2009 John Godley, All Rights Reserved
// ======================================================================================
// 0.1.6  - Corrected WP locale functions
// 0.1.7  - Add phpdoc comments
// 0.1.8  - Support for Admin SSL
// 0.1.9  - URL encoding, defer localization until init
// 0.1.10 - Better URL encoding
// 0.1.11 - Make work in WP 2.0, fix HTTPS issue on IIS
// 0.1.12 - Activation/deactivation actions that take into account the directory
// 0.1.13 - Add realpath function
// 0.1.14 - Add select/checked functions, fix locale loader
// 0.1.15 - Remove dependency on prototype
// 0.1.16 - Add support for homedir in realpath
// 0.1.17 - Added widget class
// 0.1.18 - Expand checked function
// 0.1.19 - Make url() cope with sites with no trailing slash
// 0.1.20 - Change init function to prevent overloading
// 0.1.21 - Make widget work for WP 2.1
// 0.1.22 - Make select work with option groups, RSS compatability fix
// 0.1.23 - Make widget count work better, fix widgets in K2
// 0.1.24 - Make realpath better
// 0.1.25 - Support for new WP2.6 config location
// 0.1.26 - Add description to widget class
// 0.1.27 - Realpath on windows again
// 0.1.28 - Plugin version information
// 0.1.29 - Plugin version for older WP
// 0.1.30 - Add htmlspecialchars for non-support charsets
// 0.2    - WP Coding style
// 0.2.1  - Better HTTPS detection
// 0.2.2  - Plugin settings, base function
// 0.2.3  - More HTTPS
// 0.2.4  - Ajax helper, more compatability functions
// 0.2.5  - _n helper
// 0.2.6  - Compatability functions js_esc
// 0.2.7  - Allow multiple hooks in add_action/add_filter
// ======================================================================================


/**
 * Wraps up several useful functions for WordPress plugins and provides a method to separate
 * display HTML from PHP code.
 *
 * <h4>Display Rendering</h4>
 * The class uses a similar technique to Ruby On Rails views, whereby the display HTML is kept
 * in a separate directory and file from the main code.  A display is 'rendered'(sent to the browser)
 * or 'captured'(returned to the calling function).
 *
 * Template files are separated into two areas: admin and user.  Admin templates are only for display in
 * the WordPress admin interface, while user templates are typically for display on the site(although neither
 * of these are enforced).  All templates are PHP code, but are referred to without .php extension.
 *
 * The reason for this separation is that one golden rule of plugin creation is that someone will always want to change
 * the formatting and style of your output.  Rather than forcing them to modify the plugin(bad), or modify files within
 * the plugin(equally bad), the class allows user templates to be overridden with files contained within the theme.
 *
 * An additional benefit is that it leads to code re-use, especially with regards to Ajax(i.e. your display code can be called from
 * many locations)
 *
 * Template files are located within the 'view' subdirectory of the plugins base(specified when registering the plugin):
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
 * array( 'message' => 'Your data was processed', 'items' => 103);
 *
 * <h4>How it works in practice</h4>
 * You create a template file to display how many items have been processed.  You store this in 'view/admin/processed.php':
 *
 * <pre>&lt;p&gt;You processed &lt;?php echo $items ?&gt; items&lt;/p&gt;</pre>
 *
 * When you want to display this in your plugin you use:
 *
 * <pre> $this->render_admin( 'processed', array( 'items' => 100));</pre>
 *
 * @package WordPress base library
 * @author John Godley
 * @copyright Copyright(C) John Godley
 **/

class Redirection_Plugin {
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
	 * Version URL(if enabled)
	 * @var string URL
	 **/
	var $version_url;

	/**
	 * Register your plugin with a name and base directory.  This <strong>must</strong> be called once.
	 *
	 * @param string $name Name of your plugin.  Is used to determine the plugin locale domain
	 * @param string $base Directory containing the plugin's 'view' files.
	 * @return void
	 **/
	function register_plugin( $name, $base ) {
		$this->plugin_base = rtrim( dirname( $base ), '/' );
		$this->plugin_name = $name;

		$this->add_action( 'init', 'load_locale' );
	}

	/**
	 * Hook called to change the locale directory
	 * @return void
	 **/
	function load_locale() {
		// Here we manually fudge the plugin locale as WP doesnt allow many options
		$locale = get_locale();
		if( empty( $locale ) )
			$locale = 'en_US';

		$mofile = dirname( __FILE__ )."/locale/$locale.mo";
		load_textdomain( $this->plugin_name, $mofile );
	}


	/**
	 * Register a WordPress action and map it back to the calling object
	 *
	 * @param mixed $action Name of the action (single string or array of strings)
	 * @param string $function Function name (optional, if an array is given for $action then first $action is used as function name)
	 * @param int $priority WordPress priority(optional)
	 * @param int $accepted_args Number of arguments the function accepts(optional)
	 * @return void
	 **/
	function add_action( $actions, $function = '', $priority = 10, $accepted_args = 1 ) {
		if ( !is_array( $actions ) )
			$actions = array( $actions );

		foreach ( $actions AS $action ) {
			add_action( $action, array( &$this, $function == '' ? $actions[0] : $function ), $priority, $accepted_args );
		}
	}


	/**
	 * Register a WordPress filter and map it back to the calling object
	 *
	 * @param mixed $action Name of the action (single string or array of strings)
	 * @param string $function Function name (optional, if an array is given for $action then first $action is used as function name)
	 * @param int $priority WordPress priority(optional)
	 * @param int $accepted_args Number of arguments the function accepts(optional)
	 * @return void
	 **/
	function add_filter( $filters, $function = '', $priority = 10, $accepted_args = 1 ) {
		if ( !is_array( $filters ) )
			$filters = array( $filters );

		foreach ( $filters AS $filter ) {
			add_filter( $filter, array( &$this, $function == '' ? $filters[0] : $function ), $priority, $accepted_args );
		}
	}


	/**
	 * Special activation function that takes into account the plugin directory
	 *
	 * @param string $pluginfile The plugin file location(i.e. __FILE__)
	 * @param string $function Optional function name, or default to 'activate'
	 * @return void
	 **/
	function register_activation( $pluginfile, $function = '' ) {
		add_action( 'activate_'.basename( dirname( $pluginfile ) ).'/'.basename( $pluginfile ), array( &$this, $function == '' ? 'activate' : $function ) );
	}

	function register_ajax( $action, $function = '', $priority = 10 ) {
		add_action( 'wp_ajax_'.$action, array( &$this, $function == '' ? $action : $function ), $priority );
	}

	/**
	 * Special deactivation function that takes into account the plugin directory
	 *
	 * @param string $pluginfile The plugin file location(i.e. __FILE__)
	 * @param string $function Optional function name, or default to 'deactivate'
	 * @return void
	 **/
	function register_deactivation( $pluginfile, $function = '' ) {
		add_action( 'deactivate_'.basename( dirname( $pluginfile ) ).'/'.basename( $pluginfile ), array( &$this, $function == '' ? 'deactivate' : $function ) );
	}

	function register_plugin_settings( $pluginfile, $function = '' ) {
		add_action( 'plugin_action_links_'.basename( dirname( $pluginfile ) ).'/'.basename( $pluginfile ), array( &$this, $function == '' ? 'plugin_settings' : $function ), 10, 4 );
	}

	/**
	 * Renders an admin section of display code
	 *
	 * @param string $ug_name Name of the admin file(without extension)
	 * @param string $array Array of variable name=>value that is available to the display code(optional)
	 * @return void
	 **/
	function render_admin( $ug_name, $ug_vars = array() ) {
		global $plugin_base;

		foreach ( $ug_vars AS $key => $val ) {
			$$key = $val;
		}

		if ( file_exists( "{$this->plugin_base}/view/admin/$ug_name.php" ) )
			include "{$this->plugin_base}/view/admin/$ug_name.php";
		else
			echo "<p>Rendering of admin template {$this->plugin_base}/view/admin/$ug_name.php failed</p>";
	}

	/**
	 * Renders a section of user display code.  The code is first checked for in the current theme display directory
	 * before defaulting to the plugin
	 *
	 * @param string $ug_name Name of the admin file(without extension)
	 * @param string $array Array of variable name=>value that is available to the display code(optional)
	 * @return void
	 **/
	function render( $ug_name, $ug_vars = array() ) {
		foreach ( $ug_vars AS $key => $val ) {
			$$key = $val;
		}

		if ( file_exists( TEMPLATEPATH."/view/{$this->plugin_name}/$ug_name.php" ) )
			include TEMPLATEPATH."/view/{$this->plugin_name}/$ug_name.php";
		elseif ( file_exists( "{$this->plugin_base}/view/{$this->plugin_name}/$ug_name.php" ) )
			include "{$this->plugin_base}/view/{$this->plugin_name}/$ug_name.php";
		else
			echo "<p>Rendering of template $ug_name.php failed</p>";
	}

	/**
	 * Renders a section of user display code.  The code is first checked for in the current theme display directory
	 * before defaulting to the plugin
	 *
	 * @param string $ug_name Name of the admin file(without extension)
	 * @param string $array Array of variable name=>value that is available to the display code(optional)
	 * @return void
	 **/
	function capture( $ug_name, $ug_vars = array() ) {
		ob_start();

		$this->render( $ug_name, $ug_vars );
		$output = ob_get_contents();

		ob_end_clean();
		return $output;
	}

	/**
	 * Captures an admin section of display code
	 *
	 * @param string $ug_name Name of the admin file(without extension)
	 * @param string $array Array of variable name=>value that is available to the display code(optional)
	 * @return string Captured code
	 **/
	function capture_admin( $ug_name, $ug_vars = array() ) {
		ob_start();

		$this->render_admin( $ug_name, $ug_vars );
		$output = ob_get_contents();

		ob_end_clean();
		return $output;
	}

	/**
	 * Display a standard error message(using CSS ID 'message' and classes 'fade' and 'error)
	 *
	 * @param string $message Message to display
	 * @return void
	 **/
	function render_error( $message ) {
	?>
<div class="fade error" id="message">
 <p><?php echo $message ?></p>
</div>
<?php
	}

	/**
	 * Display a standard notice(using CSS ID 'message' and class 'updated' ).
	 * Note that the notice can be made to automatically disappear, and can be removed
	 * by clicking on it.
	 *
	 * @param string $message Message to display
	 * @param int $timeout Number of seconds to automatically remove the message(optional)
	 * @return void
	 **/
	function render_message( $message, $timeout = 0 ) {
		?>
<div class="updated" id="message" onclick="this.parentNode.removeChild(this)">
 <p><?php echo $message ?></p>
</div>
	<?php
	}

	/**
	 * Get the plugin's base directory
	 *
	 * @return string Base directory
	 **/
	function dir() {
		return $this->plugin_base;
	}

	function base () {
		return admin_url( $this->plugin_name );
	}

	/**
	 * Get a URL to the plugin.  Useful for specifying JS and CSS files
	 *
	 * For example, <img src="<?php echo $this->url() ?>/myimage.png"/>
	 *
	 * @return string URL
	 **/
	function url( $url = '' ) {
		if ( $url )
			return str_replace( '\\', urlencode( '\\' ), str_replace( '&amp;amp', '&amp;', str_replace( '&', '&amp;', $url ) ) );

		$root = ABSPATH;
		if ( defined( 'WP_PLUGIN_DIR' ) )
			$root = WP_PLUGIN_DIR;

		$url = substr( $this->plugin_base, strlen( $this->realpath( $root ) ) );
		if ( DIRECTORY_SEPARATOR != '/' )
			$url = str_replace( DIRECTORY_SEPARATOR, '/', $url );

		if ( defined( 'WP_PLUGIN_URL' ) )
			$url = WP_PLUGIN_URL.'/'.ltrim( $url, '/' );
		else
			$url = get_bloginfo( 'wpurl' ).'/'.ltrim( $url, '/' );

		// Do an SSL check - only works on Apache
		global $is_IIS;
		if ( isset( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS'] ) == 'on' && $is_IIS === false )
			$url = str_replace( 'http://', 'https://', $url );

		return $url;
	}

	/**
	 * Version of realpath that will work on systems without realpath
	 *
	 * @param string $path The path to canonicalize
	 * @return string Canonicalized path
	 **/
	function realpath( $path ) {
		if ( function_exists( 'realpath' ) && DIRECTORY_SEPARATOR == '/' )
			return realpath( $path );
		elseif ( DIRECTORY_SEPARATOR == '/' )
		{
			$path = preg_replace( '/^~/', $_SERVER['DOCUMENT_ROOT'], $path );

	    // canonicalize
	    $path    = explode( DIRECTORY_SEPARATOR, $path );
	    $newpath = array();

	    for ( $i = 0; $i < count( $path ); $i++ ) {
				if ( $path[$i] === '' || $path[$i] === '.' )
					continue;

				if ( $path[$i] === '..' ) {
					array_pop( $newpath );
					continue;
				}

				array_push( $newpath, $path[$i] );
	    }

	   	return DIRECTORY_SEPARATOR.implode( DIRECTORY_SEPARATOR, $newpath );
		}

		return $path;
	}

	/**
	 * Helper function to check a checkbox if the item has been checked
	 *
	 * @param mixed $item Checkbox value, or array of checkbox values: field => value
	 * @param string $field Fieldname, if array is given for $item
	 * @return void
	 **/
	function checked( $item, $field = '' ) {
		if ( $field && is_array( $item ) )	{
			if ( isset( $item[$field] ) && $item[$field] )
				echo ' checked="checked"';
		}
		elseif ( !empty( $item ) )
			echo ' checked="checked"';
	}

	/**
	 * Helper function to display a dropdown select box
	 *
	 * @param array $items Associative array of: fieldname => label
	 * @param string $default Default fieldname to select
	 * @return void
	 **/
	function select( $items, $default = '' ) {
		if ( count( $items ) > 0 ) {
			foreach ( $items AS $key => $value ) {
				if ( is_array( $value ) )	{
					echo '<optgroup label="'.esc_attr( $key ).'">';

					foreach ( $value AS $sub => $subvalue ) {
						echo '<option value="'.esc_attr( $sub ).'"'.( $sub == $default ? ' selected="selected"' : '' ).'>'.esc_html( $subvalue ).'</option>';
					}

					echo '</optgroup>';
				}
				else
					echo '<option value="'.esc_attr( $key ).'"'.( $key == $default ? ' selected="selected"' : '' ).'>'.esc_html( $value ).'</option>';
			}
		}
	}

	/**
	 * Expanded version of htmlspecialchars which detects the blog encoding and runs iconv on any encoding that is not supported by htmlspecialchars
	 *
	 * @param string $text Text to run htmlspecialchars on
	 * @return void
	 **/
	function specialchars( $text ) {
		$charset = get_option( 'blog_charset' );

		if ( $charset != 'UTF-8' && function_exists( 'iconv' ) && !in_array( $charset, array( 'ISO-8859-1', 'ISO-8859-15', 'cp1251', 'cp1252', 'KOI8-R', 'BIG5', 'GB2312', 'Shift_JIS', 'EUC-JP' ) ) )
			return iconv( 'UTF-8//IGNORE', $charset, htmlspecialchars( iconv( $charset, 'UTF-8//IGNORE', $text ) ) );
		return htmlspecialchars( $text, ENT_COMPAT, $charset );
	}

	/**
	 * Special version of strlen that runs mb_strlen if blog encoding is not UTF-8
	 *
	 * @param string $name Name of your plugin.  Is used to determine the plugin locale domain
	 * @param string $base Directory containing the plugin's 'view' files.
	 * @return void
	 **/
	function strlen( $text ) {
		$charset = get_option( 'blog_charset' );

		if ( $charset != 'UTF-8' && function_exists( 'mb_strlen' ) )
			return mb_strlen( $text );
		return strlen( $text );
	}

	/**
	 * Returns version of plugin
	 *
	 * @return string Version
	 **/
	function version() {
		$plugin_data = implode( '', file( $this->plugin_base ) );

		if ( preg_match( '|Version:(.*)|i', $plugin_data, $version ) )
			return trim( $version[1] );
		return '';
	}
}

/**
 * Debug helper, borrowed from CakePHP, that displays a print_r inside <pre></pre> tags
 *
 * @param string $name Name of your plugin.  Is used to determine the plugin locale domain
 * @param string $base Directory containing the plugin's 'view' files.
 * @return void
 **/
if ( !function_exists( 'pr' ) ) {
	function pr( $thing ) {
		echo '<pre>';
		print_r( $thing );
		echo '</pre>';
	}
}

if ( !function_exists( '_n' ) ) {
	function _n($single, $plural, $number, $domain = 'default') {
		return __ngettext($single, $plural, $number, $domain = 'default');
	}
}
