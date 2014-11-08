<?php
/*
Plugin Name: Redirection
Plugin URI: http://urbangiraffe.com/plugins/redirection/
Description: Manage all your 301 redirects and monitor 404 errors
Version: 2.3.10
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

define( 'REDIRECTION_VERSION', '2.3.1' );     // DB schema version. Only change if DB needs changing

if ( class_exists( 'Redirection' ) )
	return;

class Redirection extends Redirection_Plugin {
	var $hasMatched = false;

	function __construct() {
		$this->register_plugin( 'redirection', __FILE__ );

		if ( is_admin() ) {
			$this->add_action( 'admin_menu' );
			$this->add_action( 'load-tools_page_redirection', 'redirection_head' );

			add_filter( 'set-screen-option', array( $this, 'set_per_page' ), 10, 3 );
			add_action( 'redirection_log_delete', array( $this, 'expire_logs' ) );

			$this->register_plugin_settings( __FILE__ );

			add_action( 'wp_ajax_red_log_delete', array( &$this, 'ajax_log_delete' ) );
			add_action( 'wp_ajax_red_module_edit', array( &$this, 'ajax_module_edit' ) );
			add_action( 'wp_ajax_red_module_save', array( &$this, 'ajax_module_save' ) );
			add_action( 'wp_ajax_red_group_edit', array( &$this, 'ajax_group_edit' ) );
			add_action( 'wp_ajax_red_group_save', array( &$this, 'ajax_group_save' ) );
			add_action( 'wp_ajax_red_redirect_add', array( &$this, 'ajax_redirect_add' ) );
			add_action( 'wp_ajax_red_redirect_edit', array( &$this, 'ajax_redirect_edit' ) );
			add_action( 'wp_ajax_red_redirect_save', array( &$this, 'ajax_redirect_save' ) );

			$this->update();
		}
		else {
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

			$database = new RE_Database();
			return $database->upgrade( $version, REDIRECTION_VERSION );
		}

		return true;
	}

	function set_per_page( $status, $option, $value ) {
		if ( $option == 'redirection_log_per_page' )
			return $value;
		return $status;
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
		include dirname( __FILE__ ).'/models/pager.php';

		$this->inject();

		if ( !isset( $_GET['sub'] ) || ( isset( $_GET['sub'] ) && ( in_array( $_GET['sub'], array( 'log', '404s', 'groups' ) ) ) ) )
			add_screen_option( 'per_page', array( 'label' => __( 'Log entries', 'redirection' ), 'default' => 25, 'option' => 'redirection_log_per_page' ) );

		wp_enqueue_script( 'redirection', plugin_dir_url( __FILE__ ).'js/redirection.js', array( 'jquery-form', 'jquery-ui-sortable' ), $this->version() );
		wp_enqueue_style( 'redirection', plugin_dir_url( __FILE__ ).'admin.css', $this->version() );

		wp_localize_script( 'redirection', 'Redirectioni10n', array(
			'error_msg' => __( 'Sorry, unable to do that. Please try refreshing the page.' ),
		) );
	}

	function admin_menu() {
  		add_management_page( __( "Redirection", 'redirection' ), __( "Redirection", 'redirection' ), apply_filters( 'redirection_role', 'administrator' ), basename( __FILE__ ), array( &$this, "admin_screen" ) );
	}

	function expire_logs() {
		global $wpdb;

		$options = $this->get_options();
		$cleanup = false;

		if ( $options['expire_redirect'] > 0 ) {
			$cleanup = true;
			$logs = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_logs WHERE created < DATE_SUB(NOW(), INTERVAL %d DAY)", $options['expire'] ) );

			if ( $logs > 0 )
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_logs WHERE created < DATE_SUB(NOW(), INTERVAL %d DAY) LIMIT 1000", $options['expire'] ) );
		}

		if ( $options['expire_404'] > 0 ) {
			$cleanup = true;
			$l404 = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_404 WHERE created < DATE_SUB(NOW(), INTERVAL %d DAY)", $options['expire'] ) );

			if ( $l404 > 0 )
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_404 WHERE created < DATE_SUB(NOW(), INTERVAL %d DAY) LIMIT 1000", $options['expire'] ) );
		}

		if ( $cleanup ) {
			$rand = mt_rand( 1, 5000 );

			if ( $rand == 11 )
				$wpdb->query( "OPTIMIZE TABLE {$wpdb->prefix}redirection_logs" );
			elseif ( $rand == 12 )
				$wpdb->query( "OPTIMIZE TABLE {$wpdb->prefix}redirection_404" );
		}
	}

	function admin_screen() {
	  	$this->update();

		$options = $this->get_options();
		if ( ( $options['expire_404'] > 0 || $options['expire_redirect'] > 0 ) && !wp_next_scheduled( 'redirection_log_delete' ) )
			wp_schedule_event( time(), 'daily', 'redirection_log_delete' );

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
		$pager = new Redirection_Module_Table();
		$pager->prepare_items();

		$this->render_admin( 'module_list', array( 'options' => $this->get_options(), 'table' => $pager ) );
	}

	function get_options() {
		$options = get_option( 'redirection_options' );
		if ( $options === false )
			$options = array();

		$defaults = array(
			'support'         => false,
			'token'           => '',
			'monitor_post'    => 0,
			'auto_target'     => '',
			'expire_redirect' => 7,
			'expire_404'      => 7,
		);

		foreach ( $defaults AS $key => $value ) {
			if ( !isset( $options[$key] ) )
				$options[$key] = $value;
		}

		if ( isset( $options['expire'] ) ) {
			if ( isset( $options['log_redirection'] ) )
				$options['expire_redirect'] = $options['expire'];

			if ( isset( $options['log_404s'] ) )
				$options['expire_404'] = $options['expire'];

			unset( $options['expire'] );
			unset( $options['log_redirection'] );
			unset( $options['log_404s'] );

			update_option( 'redirection_options', $options );
		}

		$options['lookup'] = 'http://urbangiraffe.com/map/?ip=';

		return $options;
	}

	function inject() {
		$options = $this->get_options();

		if ( isset( $_POST['id'] ) && !isset( $_POST['action'] ) ) {
			wp_safe_redirect( add_query_arg( 'id', intval( $_POST['id'] ), $_SERVER['REQUEST_URI'] ) );
			die();
		}

		if ( isset( $_GET['token'] ) && isset( $_GET['page'] ) && isset( $_GET['sub'] ) && $_GET['token'] == $options['token'] && $_GET['page'] == 'redirection.php' ) {
			include dirname( __FILE__ ).'/models/file_io.php';

			$exporter = Red_FileIO::create( $_GET['sub'] );
			if ( $exporter ) {
				$items = Red_Item::get_all_for_module( intval( $_GET['module'] ) );

				$exporter->export( $items );
				die();
			}
		}
		elseif ( isset( $_POST['export-csv'] ) && check_admin_referer( 'redirection-log_management' ) ) {
			if ( isset( $_GET['sub'] ) && $_GET['sub'] == 'log' )
				RE_Log::export_to_csv();
			else
				RE_404::export_to_csv();
			die();
		}
	}

	function admin_screen_options() {
		if ( isset( $_POST['regenerate'] ) && check_admin_referer( 'redirection-update_options' ) ) {
			$options = $this->get_options();
			$options['token'] = md5( uniqid() );

			update_option( 'redirection_options', $options );

			$this->render_message( __( 'Your options were updated', 'redirection' ) );
		}
		elseif ( isset( $_POST['update'] ) && check_admin_referer( 'redirection-update_options' ) ) {
			$options['monitor_post']    = stripslashes( $_POST['monitor_post'] );
			$options['auto_target']     = stripslashes( $_POST['auto_target'] );
			$options['support']         = isset( $_POST['support'] ) ? true : false;
			$options['token']           = stripslashes( $_POST['token'] );
			$options['expire_redirect'] = min( intval( $_POST['expire_redirect'] ), 60 );
			$options['expire_404']      = min( intval( $_POST['expire_404'] ), 60 );

			if ( trim( $options['token'] ) == '' )
				$options['token'] = md5( uniqid() );

			update_option( 'redirection_options', $options );

			$this->render_message( __( 'Your options were updated', 'redirection' ) );
		}
		elseif ( isset( $_POST['delete'] ) && check_admin_referer( 'redirection-delete_plugin' ) ) {
			include dirname( __FILE__ ).'/models/database.php';

			$db = new RE_Database();
			$db->remove( __FILE__ );

			$this->render_message( __( 'Redirection data has been deleted and the plugin disabled', 'redirection' ) );
			return;
		}
		elseif ( isset( $_POST['import'] ) && check_admin_referer( 'redirection-import' ) ) {
			include dirname( __FILE__ ).'/models/file_io.php';

			$count = Red_FileIO::import( $_POST['group'], $_FILES['upload'] );

			if ( $count > 0 )
				$this->render_message( sprintf( _n( '%d redirection was successfully imported','%d redirections were successfully imported', $count, 'redirection' ), $count ) );
			else
				$this->render_message( __( 'No items were imported', 'redirection' ) );
		}

		$groups = Red_Group::get_for_select();
		$this->render_admin( 'options', array( 'options' => $this->get_options(), 'groups' => $groups ) );
	}

	function admin_screen_log() {
		$options = $this->get_options();

		if ( isset( $_POST['delete-all'] ) && check_admin_referer( 'redirection-log_management' ) ) {
			RE_Log::delete_all();
			$this->render_message( __( 'Your logs have been deleted', 'redirection' ) );
		}

		$table = new Redirection_Log_Table( $options );

		if ( isset( $_GET['module'] ) )
			$table->prepare_items( 'module', intval( $_GET['module'] ) );
		else if (isset($_GET['group']))
			$table->prepare_items( 'group', intval( $_GET['group'] ) );
		else if (isset($_GET['redirect']))
			$table->prepare_items( 'redirect', intval( $_GET['redirect'] ) );
		else
			$table->prepare_items();

		$this->render_admin( 'log', array( 'options' => $options, 'table' => $table, 'lookup' => $options['lookup'], 'type' => 'log' ) );
	}

	function admin_screen_404() {
		if ( isset( $_POST['delete-all'] ) && check_admin_referer( 'redirection-log_management' ) ) {
			RE_404::delete_all();
			$this->render_message( __( 'Your logs have been deleted', 'redirection' ) );
		}

		$options = $this->get_options();

		$table = new Redirection_404_Table( $options );
		$table->prepare_items( isset( $_GET['ip'] ) ? $_GET['ip'] : false );

		$this->render_admin( 'log', array( 'options' => $options, 'table' => $table, 'lookup' => $options['lookup'], 'type' => '404s' ) );
	}

	function admin_groups( $module ) {
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

		$table = new Redirection_Group_Table( Red_Module::get_for_select(), $module );
		$table->prepare_items();

		$module = Red_Module::get( $module );
		if ( $module )
  			$this->render_admin( 'group_list', array( 'options' => $this->get_options(), 'table' => $table, 'modules' => Red_Module::get_for_select(), 'module' => $module ) );
  		else
  			$this->render_message( __( 'Unknown module', 'redirection' ) );
	}

	function admin_redirects( $group_id ) {
		if ( $group_id == 0 )
			$group_id = Red_Group::get_first_id();

		$group = Red_Group::get( $group_id );

		$table = new Redirection_Table( Red_Group::get_for_select(), $group );
		$table->prepare_items();

  		$this->render_admin( 'item_list', array( 'options' => $this->get_options(), 'group' => $group, 'table' => $table, 'date_format' => get_option( 'date_format' ) ) );
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
	 */
	function template_redirect() {
		if ( is_404() )	{
			$options = $this->get_options();

			if ( isset( $options['expire_404'] ) && $options['expire_404'] >= 0 ) {
				RE_404::create( red_get_url(), red_user_agent(), red_ip(), red_http_referrer() );
			}
		}
	}

	public function ajax_log_delete()	{
		if ( check_ajax_referer( 'redirection-items' ) ) {
			if ( preg_match_all( '/=(\d*)/', $_POST['checked'], $items ) > 0) {
				foreach ( $items[1] AS $item ) {
					RE_Log::delete( intval( $item ) );
				}
			}
		}
	}

	private function check_ajax_referer( $nonce ) {
		if ( check_ajax_referer( $nonce, 'nonce', false ) === false )
			$this->output_ajax_response( array( 'error' => __( 'Unable to perform action' ).' - bad nonce' ) );
	}

	public function ajax_module_edit() {
		$module_id = intval( $_POST['id'] );

		$this->check_ajax_referer( 'red_edit-'.$module_id );

		$module = Red_Module::get( $module_id );
		if ( $module )
			$json['html'] = $this->capture_admin( 'module_edit', array( 'module' => $module ) );
		else
			$json['error'] = __( 'Unable to perform action' ).' - could not find module';

		$this->output_ajax_response( $json );
	}

	public function ajax_module_save() {
		global $hook_suffix;

		include dirname( __FILE__ ).'/models/pager.php';

		$hook_suffix = '';
		$module_id = intval( $_POST['id'] );

		$this->check_ajax_referer( 'red_module_save_'.$module_id );

		$module = Red_Module::get( $module_id );

		if ( $module ) {
			$module->update( $_POST );

			$pager = new Redirection_Module_Table( array(), false );
			$json = array( 'html' => $pager->column_name( $module ) );
		}
		else
			$json['error'] = __( 'Unable to perform action' ).' - could not find module';

		$this->output_ajax_response( $json );
	}

	public function ajax_group_edit() {
		$group_id = intval( $_POST['id'] );

		$this->check_ajax_referer( 'red-edit_'.$group_id );

		$group = Red_Group::get( $group_id );
		if ( $group )
			$json['html'] = $this->capture_admin( 'group_edit', array( 'group' => $group, 'modules' => Red_Module::get_for_select() ) );
		else
			$json['error'] = __( 'Unable to perform action' ).' - could not find group';

		$this->output_ajax_response( $json );
	}

	public function ajax_group_save() {
		global $hook_suffix;

		include dirname( __FILE__ ).'/models/pager.php';

		$hook_suffix = '';
		$group_id = intval( $_POST['id'] );

		$this->check_ajax_referer( 'redirection-group_save_'.$group_id );

		$group = Red_Group::get( $group_id );
		if ( $group ) {
			$group->update( $_POST );

			$pager = new Redirection_Group_Table( array(), false );
			$json = array( 'html' => $pager->column_name( $group ) );
		}
		else
			$json['error'] = __( 'Unable to perform action' ).' - could not find redirect';

		$this->output_ajax_response( $json );
	}

	public function ajax_redirect_edit() {
		$this->check_ajax_referer( 'red-edit_'.intval( $_POST['id'] ) );
		$redirect = Red_Item::get_by_id( intval( $_POST['id'] ) );

		if ( $redirect )
			$json['html'] = $this->capture_admin( 'item_edit', array( 'redirect' => $redirect, 'groups' => Red_Group::get_for_select() ) );
		else
			$json['error'] = __( 'Unable to perform action' ).' - could not find redirect';

		$this->output_ajax_response( $json );
	}

	public function ajax_redirect_save() {
		global $hook_suffix;

		include dirname( __FILE__ ).'/models/pager.php';

		$hook_suffix = '';

		$red_id = intval( $_POST['id'] );

		$this->check_ajax_referer( 'redirection-redirect_save_'.$red_id );

		$redirect = Red_Item::get_by_id( $red_id );
		if ( $redirect ) {
			$redirect->update( $_POST );

			$pager = new Redirection_Table( array() );
			$json = array( 'html' => $pager->column_url( $redirect ), 'code' => $redirect->get_action_code() );
		}
		else
			$json['error'] = __( 'Unable to perform action' ).' - could not find redirect';

		$this->output_ajax_response( $json );
	}

	public function ajax_redirect_add()	{
		global $hook_suffix;

		include dirname( __FILE__ ).'/models/pager.php';

		$hook_suffix = '';

		$this->check_ajax_referer( 'redirection-redirect_add' );

		$item = Red_Item::create( $_POST );
		if ( is_wp_error( $item ) )
			$json['error'] = $item->get_error_message();
		elseif ( $item !== false ) {
			$pager = new Redirection_Table( array() );
			$json = array( 'html' => $pager->get_row( $item ) );
		}
		else
			$json['error'] = __( 'Sorry, but your redirection was not created', 'redirection' );

		$this->output_ajax_response( $json );
	}

	private function output_ajax_response( array $data ) {
		header( 'Content-Type: application/json' );
		echo json_encode( $data );
		die();
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
