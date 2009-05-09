<?php
/*
Plugin Name: Redirection
Plugin URI: http://urbangiraffe.com/plugins/redirection/
Description: Manage all your 301 redirects and monitor 404 errors
Version: 2.1.14
Author: John Godley
Author URI: http://urbangiraffe.com
============================================================================================================
1.1    - Added .htaccess processing, counter reset, and Search Regex
         Better item deletion, and infinite redirection protection
1.2    - AJAX support(delete/edit), split admin interface, bug fixes, better regex, redirections to external sites
1.3    - Highlight internal redirects.  Fix bug in Google code
1.4    - Change to wpurl
1.5    - Make non-regex pattern not match subpatterns of itself
1.6    - Allow 'custom' scripts
1.7    - Interface redesign and feature upgrade
1.7.9  - Fix bug with PHP4. Add search box and IP lookup service.  Add log delete function & update notification.  Add 410.
         Add optional global 404 redirect
1.7.10 - Fix bug where other plugins that hook redirections wouldn't work when no redirections are defined
1.7.11 - Fix bug where index.html was not being redirected correctly
1.7.12 - Better database performance, clean up log display, make usable in WP 2.0+
1.7.13 - Workaround for FastCGI bug
1.7.14 - Add delete option, stop AJAX from looping on a bad redirection
1.7.15 - Minor bug fix, show redirect names in drop-down list, add auto-generated target URL
1.7.16 - Prevent errors caused by magic _vti_cnf.php files
1.7.17 - Add option to disable 404 logs
1.7.18 - Add auto-generation for source URL
1.7.19 - Better database installation, better auto-generation
1.7.20 - Workaround for the FastCGI workaround.  Hide canonical options for WP2.3
1.7.21 - Fix activation bug
1.7.22 - Allow carats in regex patterns, another FastCGI workaround
1.7.23 - Stop FTP log files being picked up, RSS 404 log
1.7.24 - Stop problems with mod_security
1.7.25 - Fix database problem on some hosts
2.0    - New version
2.0.1  - Install defaults when no existing redirection setup
2.0.2  - Correct DB install, fix IIS problem
2.0.3  - Fix #248.  Update plugin.php to better handle odd directories
2.0.4  - get_home_path seems not be available for some people
2.0.5  - Fix #264
2.0.6  - Support for wp-load.php
2.0.7  - Fix incorrect automatic redirection with static home pages
2.0.8  - Refix log delete
2.0.9  - Fix delete redirects
2.0.10 - Fix small issues in display with WP 2.7
2.0.11 - Hebrew translation
2.0.12 - Disable category monitor in 2.7
2.1    - Change to jQuery.  Nonce protection.  Fix #352, #353, #339, #351.  Add #358, #316.
2.1.1  - Force JS cache.  Fix log deletion
2.1.2  - Minor button changes
2.1.3  - Re-enable import feature
2.1.4  - RSS feed token
2.1.5  - Fix #366, #371, #378, #390, #400.  Add #370, #357
2.1.6  - Redirection loops
2.1.7  - Fix #422, #426
2.1.8  - Fix category change 'quick edit'
2.1.9  - Fix 'you do not permissions' error on some non-English sites
2.1.10 - Missing localisations
2.1.11 - Errors on some sites
2.1.12 - Add icons, disable category monitoring
2.1.13 - Add Spanish and Chinese translation
2.1.14 - Fix #457, add #475, #427, add Catalan translation. WP2.8 compatability
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages(including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort(including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================
*/

include dirname( __FILE__ ).'/plugin.php';
include dirname( __FILE__ ).'/models/redirect.php';
include dirname( __FILE__ ).'/models/match.php';
include dirname( __FILE__ ).'/models/log.php';
include dirname( __FILE__ ).'/models/group.php';
include dirname( __FILE__ ).'/models/module.php';
include dirname( __FILE__ ).'/models/action.php';
include dirname( __FILE__ ).'/models/monitor.php';
include dirname( __FILE__ ).'/modules/wordpress.php';
include dirname( __FILE__ ).'/modules/404.php';

define( 'REDIRECTION_VERSION', '2.1.14' );

class Redirection extends Redirection_Plugin {
	var $hasMatched = false;
	
	function Redirection() {
		$this->register_plugin('redirection', __FILE__);
		
		if ( is_admin() ) {
			$this->add_action( 'admin_menu' );
			$this->add_action( 'admin_head' );
			$this->add_action( 'wp_print_scripts' );
			$this->add_action( 'wp_print_styles' );
			$this->add_action( 'admin_head', 'wp_print_styles' );
			$this->add_action( 'init', 'inject' );
			$this->add_filter( 'contextual_help', 'contextual_help', 10, 2 );
			
			$this->register_plugin_settings( __FILE__ );
		}
		else {
			$this->update();
			
			// Create a WordPress exporter and let it handle the load
			$this->wp = new WordPress_Module();
			$this->wp->start();
			
			$this->error = new Error404_Module();
			$this->error->start();
		}
		
		$this->monitor = new Red_Monitor($this->get_options());
	}
	
	function plugin_settings( $links ) {
		$settings_link = '<a href="tools.php?page='.basename( __FILE__ ).'">'.__('Settings', 'redirection').'</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
	
	function contextual_help( $help, $screen ) {
		if ( $screen == 'tools_page_redirection' ) {
			$help .= '<h5>' . __( 'Redirection Help' ) . '</h5><div class="metabox-prefs">';
			$help .= '<a href="http://urbangiraffe.com/plugins/redirection/">'.__( 'Redirection Documentation', 'redirection' ).'</a><br/>';
			$help .= '<a href="http://urbangiraffe.com/support/forum/redirection">'.__( 'Redirection Support Forum', 'redirection' ).'</a><br/>';
			$help .= '<a href="http://urbangiraffe.com/tracker/projects/redirection/issues?set_filter=1&amp;tracker_id=1">'.__( 'Redirection Bug Tracker', 'redirection' ).'</a><br/>';
			$help .= '<a href="http://urbangiraffe.com/plugins/redirection/faq/">'.__( 'Redirection FAQ', 'redirection' ).'</a><br/>';
			$help .= __( 'Please read the documentation and FAQ, and check the bug tracker, before asking a question.' );
			$help .= '</div>';
		}
		
		return $help;
	}

	function is_25() {
		global $wp_version;
		if ( version_compare( '2.5', $wp_version ) <= 0 )
			return true;
		return false;
	}
	
	function submenu( $inwrap = false ) {
		// Decide what to do
		$sub = isset( $_GET['sub'] ) ? $_GET['sub'] : '';
	  $url = explode( '&', $_SERVER['REQUEST_URI'] );
	  $url = $url[0];

		if ( !$this->is_25() && $inwrap == false )
			$this->render_admin( 'submenu', array( 'url' => $url, 'sub' => $sub, 'class' => 'id="subsubmenu"' ) );
		elseif ( $this->is_25() && $inwrap == true )
			$this->render_admin( 'submenu', array( 'url' => $url, 'sub' => $sub, 'class' => 'class="subsubsub"', 'trail' => ' | ' ) );
			
		return $sub;
	}
	
	function version() {
		$plugin_data = implode( '', file( __FILE__ ) );
		
		if ( preg_match( '|Version:(.*)|i', $plugin_data, $version ) )
			return trim( $version[1] );
		return '';
	}

	function wp_print_scripts() {
		if ( strpos( $_SERVER['REQUEST_URI'], 'redirection.php' ) ) {
			if (!function_exists ('wp_print_styles')) {
				wp_deregister_script ('jquery');
				wp_enqueue_script( 'jquery', $this->url ().'/2.3/jquery.js', array(), $this->version () );
				wp_enqueue_script( 'jquery-ui-core', $this->url ().'/2.3/ui.core.js', array('jquery'), $this->version () );
				wp_enqueue_script( 'jquery-ui-sortable', $this->url ().'/2.3/ui.sortable.js', array('jquery-ui-core'), $this->version () );
			}
			
			wp_enqueue_script( 'redirection', $this->url().'/js/redirection.js', array('jquery-form', 'jquery-ui-sortable' ), $this->version() );
		}
	}
	
	function wp_print_styles() {
		if ( strpos( $_SERVER['REQUEST_URI'], 'redirection.php' ) )
			echo '<link rel="stylesheet" href="'.$this->url().'/admin.css" type="text/css" media="screen" title="no title" charset="utf-8"/>';
	}
	
	function admin_head() {
		if ( strpos( $_SERVER['REQUEST_URI'], 'redirection.php' ) )
			$this->render_admin( 'head', array( 'type' => $_GET['sub'] == '' ? '301' : $_GET['sub'] ) );
	}
	
	function admin_menu() {
  	add_management_page( __( "Redirection", 'redirection' ), __( "Redirection", 'redirection' ), "administrator", basename( __FILE__ ), array( &$this, "admin_screen" ) );
	}

	function update() {
		$version = get_option( 'redirection_version' );

		if ( $version != REDIRECTION_VERSION ) {
			include_once dirname( __FILE__ ).'/models/database.php';

			$db = new RE_Database();
			$db->upgrade( $version, REDIRECTION_VERSION );
		}
	}

	function admin_screen() {
	  $this->update();
	  
		$sub     = $this->submenu();	
		$options = $this->get_options();
		
		if ( $_GET['sub'] == 'log' )
			return $this->admin_screen_log();
	  elseif ( $_GET['sub'] == 'options' )
	    return $this->admin_screen_options();
	  elseif ( $_GET['sub'] == 'process' )
	    return $this->admin_screen_process();
	  elseif ( $_GET['sub'] == 'groups' )
			return $this->admin_groups( isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0);
		elseif ( $_GET['sub'] == 'modules' )
			return $this->admin_screen_modules();
		elseif ( $_GET['sub'] == 'support' )
			return $this->render_admin('support');
		else
			return $this->admin_redirects(isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0);
	}
	
	function admin_screen_modules() {
		if ( isset( $_POST['create'] ) && check_admin_referer( 'redirection-module_add' ) ) {
			$_POST = stripslashes_deep( $_POST );
			
			if ( ( $module = Red_Module::create( $_POST ) ) ) {
				$this->render_message( __( 'Your module was successfully created', 'redirection' ) );
				Red_Module::flush( intval( $_POST['module'] ) );
			}
			else
				$this->render_error( __( 'Your module was not created - did you provide a name?', 'redirection' ) );
		}
		
		$options = $this->get_options();
		$this->render_admin( 'module_list', array( 'modules' => Red_Module::get_all(), 'module_types' => Red_Module::get_types(), 'token' => $options['token'] ) );
	}
	
	function get_options() {
		$options = get_option( 'redirection_options' );
		if ( $options === false )
			$options = array();
			
		$defaults = array	(
			'lookup'            => 'http://geomaplookup.cinnamonthoughts.org/?ip=',
			'support'           => false,
			'expire'            => 0,
			'token'             => '',
			'monitor_new_posts' => false,
			'monitor_post'      => 0
		);
		
		foreach ( $defaults AS $key => $value ){
			if ( !isset( $options[$key] ) )
				$options[$key] = $value;
		}
		
		return $options;
	}
	
	function inject() {
		$options = $this->get_options();
		
		if ( ( current_user_can( 'administrator' ) || $_GET['token'] == $options['token'] ) && $_GET['page'] == 'redirection.php' && in_array( $_GET['sub'], array( 'rss', 'xml', 'csv', 'apache' ) ) ) {
			include dirname( __FILE__ ).'/models/file_io.php';

			$exporter = new Red_FileIO;
			if ( $exporter->export( $_GET['sub'] ) )
				die();
		}
	}

	function admin_screen_options() {
		if ( isset( $_POST['update'] ) && check_admin_referer( 'redirection-update_options' ) ) {
			$_POST = stripslashes_deep( $_POST );

			$options['lookup']           = $_POST['lookup'];
			$options['monitor_post']     = $_POST['monitor_post'];
			$options['monitor_category'] = $_POST['monitor_category'];
			$options['auto_target']      = $_POST['auto_target'];
			$options['support']          = isset( $_POST['support'] ) ? true : false;
			$options['monitor_new_posts'] = isset( $_POST['monitor_new_posts'] ) ? true : false;
			$options['expire']           = intval( $_POST['expire'] );
			$options['token']            = $_POST['token'];
			
			if ( trim( $options['token'] ) == '' )
				$options['token'] = md5( uniqid() );
				
			update_option( 'redirection_options', $options );

			$this->render_message( __( 'Your options were updated', 'redirection' ) );
		}
		elseif ( isset( $_POST['delete'] ) && check_admin_referer( 'redirection-delete_plugin' ) ) {
			include dirname( __FILE__ ).'/models/database.php';

			$db = new RE_Database;
			$db->remove( __FILE__ );
			
			$this->render_message( __( 'Redirection data has been deleted and the plugin disabled', 'redirection' ) );
			return;
		}
		elseif ( isset( $_POST['import'] ) && check_admin_referer( 'redirection-import' ) ) {
			include dirname( __FILE__ ).'/models/file_io.php';
			
			$importer = new Red_FileIO;
			
			$count = $importer->import( $_POST['group'], $_FILES['upload'] );
			if ( $count > 0 )
				$this->render_message( sprintf( __ngettext( '%d redirection was successfully imported','%d redirections were successfully imported', $count, 'redirection' ), $count ) );
			else
				$this->render_message( __( 'No items were imported', 'redirection' ) );
		}

		$groups = Red_Group::get_for_select();
		$this->render_admin( 'options', array( 'options' => $this->get_options(), 'groups' => $groups ) );
	}

	function admin_screen_log() {
		include dirname( __FILE__ ).'/models/pager.php';
		
		if ( isset( $_POST['deleteall'] ) && check_admin_referer( 'redirection-process_logs' ) ) {
			if ( isset( $_GET['module'] ) )
				RE_Log::delete_all( array( 'module_id' => intval( $_GET['module'] ) ), new RE_Pager( $_GET, $_SERVER['REQUEST_URI'], 'created', 'DESC', 'log' ) );
			else if (isset($_GET['group']))
				RE_Log::delete_all( array( 'group_id' => intval( $_GET['group'] ) ), new RE_Pager( $_GET, $_SERVER['REQUEST_URI'], 'created', 'DESC', 'log' ) );
			else
				RE_Log::delete_all( array(), new RE_Pager( $_GET, $_SERVER['REQUEST_URI'], 'created', 'DESC', 'log' ) );
				
			$this->render_message( __( 'Your logs have been deleted', 'redirection' ) );
		}
			
		$pager = new RE_Pager( $_GET, $_SERVER['REQUEST_URI'], 'created', 'DESC', 'log' );
		
		if ( isset( $_GET['module'] ) )
			$logs = RE_Log::get_by_module( $pager, intval( $_GET['module'] ) );
		else if (isset($_GET['group']))
			$logs = RE_Log::get_by_group( $pager, intval( $_GET['group'] ) );
		else if (isset($_GET['redirect']))
			$logs = RE_Log::get_by_redirect( $pager, intval( $_GET['redirect'] ) );
		else
			$logs = RE_Log::get( $pager );
		
		$options = $this->get_options();
		$this->render_admin( 'log', array( 'logs' => $logs, 'pager' => $pager, 'lookup' => $options['lookup'] ) );
	}
	
	function admin_groups($module) {
		include dirname( __FILE__ ).'/models/pager.php';
		
		if (isset( $_POST['add'] ) && check_admin_referer( 'redirection-add_group' ) ) {
			if ( Red_Group::create(stripslashes_deep( $_POST ) ) ) {
				$this->render_message( __( 'Your group was added successfully', 'redirection' ) );
				Red_Module::flush( $module );
			}
			else
				$this->render_error( __( 'Please specify a group name', 'redirection' ) );
		}
		
		if ( $module == 0 )
			$module = Red_Module::get_first_id();

		$pager = new RE_Pager( $_GET, $_SERVER['REQUEST_URI'], 'position', 'ASC' );
		$items = Red_Group::get_all( $module, $pager );

  	$this->render_admin( 'group_list', array( 'groups' => $items, 'pager' => $pager, 'modules' => Red_Module::get_for_select(), 'module' => Red_Module::get( $module ) ) );
	}
	
	function admin_redirects( $group ) {
		include dirname( __FILE__ ).'/models/pager.php';
		
		if ( $group == 0 )
			$group = Red_Group::get_first_id();
		
		$pager = new RE_Pager( $_GET, $_SERVER['REQUEST_URI'], 'position', 'ASC' );
		$items = Red_Item::get_by_group( $group, $pager );

  	$this->render_admin( 'item_list', array( 'items' => $items, 'pager' => $pager, 'group' => Red_Group::get( $group ), 'groups' => Red_Group::get_for_select() ) );
	}
	
	function setMatched( $match ) {
		$this->hasMatched = $match;
	}
	
	function hasMatched() {
		return $this->hasMatched;
	}
}

// Instantiate the plugin
$redirection = new Redirection;
