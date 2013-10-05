<?php
/*
Plugin Name: Redirection
Plugin URI: http://urbangiraffe.com/plugins/redirection/
Description: Manage all your 301 redirects and monitor 404 errors
Version: 2.3.4
Author: John Godley
Author URI: http://urbangiraffe.com
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

define( 'REDIRECTION_VERSION', '2.3.1' );

if ( class_exists( 'Redirection' ) )
	return;

class Redirection extends Redirection_Plugin {
	var $hasMatched = false;

	function Redirection() {
		$this->register_plugin( 'redirection', __FILE__ );

		if ( is_admin() ) {
			$this->add_action( 'admin_menu' );
			$this->add_action( 'load-tools_page_redirection', 'redirection_head' );
			$this->add_action( 'init', 'inject' );

			$this->register_activation( __FILE__ );
			$this->register_plugin_settings( __FILE__ );

			// Ajax functions
			if ( defined( 'DOING_AJAX' ) ) {
				include_once dirname( __FILE__ ).'/ajax.php';
				$this->ajax = new RedirectionAjax();
			}
		}
		else {
			$this->update();

			// Create a WordPress exporter and let it handle the load
			$this->wp = new WordPress_Module();
			$this->wp->start();
		}

		$this->monitor = new Red_Monitor( $this->get_options() );
		$this->add_action ('template_redirect' );
	}

	function update() {
		$version = get_option( 'redirection_version' );

		if ( $version != REDIRECTION_VERSION ) {
			include_once dirname( __FILE__ ).'/models/database.php';

			$db = new RE_Database();
			return $db->upgrade( $version, REDIRECTION_VERSION );
		}

		return true;
	}

	function activate() {
		if ( $this->update() === false ) {
			$db = new RE_Database();
			$db->remove( $version, REDIRECTION_VERSION );
	    exit();
		}
	}

	function plugin_settings( $links ) {
		$settings_link = '<a href="tools.php?page='.basename( __FILE__ ).'">'.__( 'Settings', 'redirection' ).'</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	function version() {
		$plugin_data = implode( '', file( __FILE__ ) );

		if ( preg_match( '|Version:(.*)|i', $plugin_data, $version ) )
			return trim( $version[1] );
		return '';
	}

	function redirection_head() {
		wp_enqueue_script( 'redirection', plugin_dir_url( __FILE__ ).'js/redirection.js', array( 'jquery-form', 'jquery-ui-sortable' ), $this->version() );
		wp_enqueue_style( 'redirection', plugin_dir_url( __FILE__ ).'admin.css', $this->version() );

		wp_localize_script( 'redirection', 'Redirectioni10n', array(
			'please_wait'  => __( 'Please wait...', 'redirection' ),
			'type'         => 1,
			'progress'     => '<img src="'.plugin_dir_url( __FILE__ ).'/images/progress.gif" alt="loading" width="50" height="16"/>',
		  	'are_you_sure' => __( 'Are you sure?', 'redirection' ),
			'none_select'  => __( 'No items have been selected', 'redirection' )
		) );
	}

	function admin_menu() {
  		add_management_page( __( "Redirection", 'redirection' ), __( "Redirection", 'redirection' ), "administrator", basename( __FILE__ ), array( &$this, "admin_screen" ) );
	}

	function expire_logs() {
		global $wpdb;

		// Expire old entries
		$options = $this->get_options();
		if ( $options['expire'] != 0 ) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_logs WHERE created < DATE_SUB(NOW(), INTERVAL %d DAY) LIMIT 1000", $options['expire'] ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_404 WHERE created < DATE_SUB(NOW(), INTERVAL %d DAY) LIMIT 1000", $options['expire'] ) );
		}
	}

	function admin_screen() {
	  	$this->update();
	  	$this->expire_logs();

		// Decide what to do
		$sub = isset( $_GET['sub'] ) ? $_GET['sub'] : '';

		$options = $this->get_options();

		if ( isset($_GET['sub']) ) {
			if ( $_GET['sub'] == 'log' )
				return $this->admin_screen_log();
			elseif ( $_GET['sub'] == '404s' )
				return $this->admin_screen_404();
			elseif ( $_GET['sub'] == 'options' )
				return $this->admin_screen_options();
			elseif ( $_GET['sub'] == 'process' )
				return $this->admin_screen_process();
			elseif ( $_GET['sub'] == 'groups' )
				return $this->admin_groups( isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0 );
			elseif ( $_GET['sub'] == 'modules' )
				return $this->admin_screen_modules();
			elseif ( $_GET['sub'] == 'support' )
				return $this->render_admin('support', array( 'options' => $this->get_options() ) );
		}

		return $this->admin_redirects( isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0 );
	}

	function admin_screen_modules() {
		$options = $this->get_options();
		$this->render_admin( 'module_list', array( 'options' => $this->get_options(), 'modules' => Red_Module::get_all(), 'module_types' => Red_Module::get_types(), 'token' => $options['token'] ) );
	}

	function get_options() {
		$options = get_option( 'redirection_options' );
		if ( $options === false )
			$options = array();

		$defaults = array	(
			'support'           => false,
			'log_redirections'  => true,
			'log_404s'          => true,
			'expire'            => 0,
			'token'             => '',
			'monitor_new_posts' => false,
			'monitor_post'      => 0,
			'auto_target'       => '',
		);

		foreach ( $defaults AS $key => $value ) {
			if ( !isset( $options[$key] ) )
				$options[$key] = $value;
		}

		$options['lookup'] = 'http://geomaplookup.net/?ip=';

		return $options;
	}

	function inject() {
		$options = $this->get_options();

		if ( isset($_GET['token'] ) && isset( $_GET['page'] ) && isset( $_GET['sub'] ) && $_GET['token'] == $options['token'] && $_GET['page'] == 'redirection.php' && in_array( $_GET['sub'], array( 'rss', 'xml', 'csv', 'apache' ) ) ) {
			include dirname( __FILE__ ).'/models/file_io.php';

			$exporter = new Red_FileIO;
			if ( $exporter->export( $_GET['sub'] ) )
				die();
		}
	}

	function admin_screen_options() {
		if ( isset( $_POST['update'] ) && check_admin_referer( 'redirection-update_options' ) ) {
			$options['monitor_post']      = stripslashes( $_POST['monitor_post'] );
//			$options['monitor_category']  = stripslashes( $_POST['monitor_category'] );
			$options['auto_target']       = stripslashes( $_POST['auto_target'] );
			$options['support']           = isset( $_POST['support'] ) ? true : false;
			$options['log_redirections']  = (bool) @ $_POST['log_redirections'];
			$options['log_404s']          = (bool) @ $_POST['log_404s'];
			$options['monitor_new_posts'] = isset( $_POST['monitor_new_posts'] ) ? true : false;
			$options['expire']            = intval( $_POST['expire'] );
			$options['token']             = stripslashes( $_POST['token'] );

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
				$this->render_message( sprintf( _n( '%d redirection was successfully imported','%d redirections were successfully imported', $count, 'redirection' ), $count ) );
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
				RE_Log::delete_all( 'module', intval( $_GET['module'] ) );
			else if (isset($_GET['group']))
				RE_Log::delete_all( 'group', intval( $_GET['group_id'] ) );
			else
				RE_Log::delete_all();

			$this->render_message( __( 'Your logs have been deleted', 'redirection' ) );
		}

		$options = $this->get_options();

		$table = new Redirection_Log_Table( $options );

		if ( isset( $_GET['module'] ) )
			$table->prepare_items( 'module', intval( $_GET['module'] ) );
		else if (isset($_GET['group']))
			$table->prepare_items( 'group', intval( $_GET['group'] ) );
		else if (isset($_GET['redirect']))
			$table->prepare_items( 'redirect', intval( $_GET['redirect'] ) );
		else
			$table->prepare_items();

		$this->render_admin( 'log', array( 'options' => $options, 'table' => $table, 'lookup' => $options['lookup'] ) );
	}

	function admin_screen_404() {
		include dirname( __FILE__ ).'/models/pager.php';

		if ( isset( $_POST['deleteall'] ) && check_admin_referer( 'redirection-process_logs' ) ) {
			RE_404::delete_all();
			$this->render_message( __( 'Your logs have been deleted', 'redirection' ) );
		}

		$options = $this->get_options();

		$table = new Redirection_404_Table( $options );
		$table->prepare_items( isset( $_GET['ip'] ) ? $_GET['ip'] : false );

		$this->render_admin( 'log', array( 'options' => $options, 'table' => $table, 'lookup' => $options['lookup'] ) );
	}

	function admin_groups( $module ) {
		include dirname( __FILE__ ).'/models/pager.php';

		if ( isset( $_POST['add'] ) && check_admin_referer( 'redirection-add_group' ) ) {
			if ( Red_Group::create( stripslashes_deep( $_POST ) ) ) {
				$this->render_message( __( 'Your group was added successfully', 'redirection' ) );
				Red_Module::flush( $module );
			}
			else
				$this->render_error( __( 'Please specify a group name', 'redirection' ) );
		}

		if ( $module == 0 )
			$module = Red_Module::get_first_id();

		$pager = new RE_Pager( $_GET, admin_url( add_query_arg( array( 'sub' => 'groups' ), 'tools.php?page=redirection.php' ) ), 'position', 'ASC' );
		$items = Red_Group::get_all( $module, $pager );

		$module = Red_Module::get( $module );
		if ( $module )
  			$this->render_admin( 'group_list', array( 'options' => $this->get_options(), 'groups' => $items, 'pager' => $pager, 'modules' => Red_Module::get_for_select(), 'module' => $module ) );
  		else
  			$this->render_message( __( 'Unknown module', 'redirection' ) );
	}

	function admin_redirects( $group ) {
		include dirname( __FILE__ ).'/models/pager.php';

		if ( $group == 0 )
			$group = Red_Group::get_first_id();

		$pager = new RE_Pager( $_GET, admin_url( add_query_arg( array(), 'tools.php?page=redirection.php' ) ), 'position', 'ASC' );
		$items = Red_Item::get_by_group( $group, $pager );

  		$this->render_admin( 'item_list', array( 'options' => $this->get_options(), 'items' => $items, 'pager' => $pager, 'group' => Red_Group::get( $group ), 'groups' => Red_Group::get_for_select(), 'date_format' => get_option( 'date_format' ) ) );
	}

	function setMatched( $match ) {
		$this->hasMatched = $match;
	}

	function hasMatched() {
		return $this->hasMatched;
	}

	function locales() {
		$locales = array();
		if ( file_exists( dirname( __FILE__ ).'/readme.txt' ) ) {
			$readme = file_get_contents( dirname( __FILE__ ).'/readme.txt' );

			$start = strpos( $readme, 'Redirection is available in' );
			$end   = strpos( $readme, '==', $start );
			if ( $start !== false && $end !== false ) {
				if ( preg_match_all( '/^\* (.*?) by (.*?)/m', substr( $readme, $start, $end ), $matches ) > 0 ) {
					$locales = $matches[1];
				}
			}

			sort( $locales );
		}

		return $locales;
	}

	/**
	 * Matches 404s
	 * @return [type] [description]
	 */
	function template_redirect() {
		if ( is_404() )	{
			$options = $this->get_options();

			if ( $options['log_404s'] ) {
				$log = RE_404::create( red_get_url(), red_user_agent(), red_ip(), red_http_referrer() );
			}
		}
	}
}

function red_get_url() {
	if ( isset( $_SERVER['REQUEST_URI'] ) )
		return $_SERVER['REQUEST_URI'];
	return '';
}

function red_user_agent() {
	if ( isset( $_SERVER['HTTP_USER_AGENT'] ) )
		return $_SERVER['HTTP_USER_AGENT'];
	return false;
}

function red_http_referrer() {
	if ( isset( $_SERVER['HTTP_REFERER'] ) )
		return $_SERVER['HTTP_REFERER'];
	return false;
}

function red_ip() {
	if ( isset( $_SERVER['REMOTE_ADDR'] ) )
	  return $_SERVER['REMOTE_ADDR'];
	elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
	  return $_SERVER['HTTP_X_FORWARDED_FOR'];
	return '';
}

// Instantiate the plugin
$redirection = new Redirection;
