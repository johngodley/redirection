<?php

include dirname( __FILE__ ).'/models/group.php';
include dirname( __FILE__ ).'/models/monitor.php';
include dirname( __FILE__ ).'/models/pager.php';
include dirname( __FILE__ ).'/models/file-io.php';

class Redirection_Admin {
	private static $instance = null;
	private $monitor;

	static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Redirection_Admin();

			load_plugin_textdomain( 'redirection', false, dirname( plugin_basename( REDIRECTION_FILE ) ).'/locale/' );
		}

		return self::$instance;
	}

	function __construct() {
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'load-tools_page_redirection', array( &$this, 'redirection_head' ) );
		add_action( 'plugin_action_links_'.basename( dirname( REDIRECTION_FILE ) ).'/'.basename( REDIRECTION_FILE ), array( &$this, 'plugin_settings' ), 10, 4 );

		add_filter( 'set-screen-option', array( $this, 'set_per_page' ), 10, 3 );

		register_deactivation_hook( REDIRECTION_FILE, array( 'Redirection_Admin', 'plugin_deactivated' ) );
		register_uninstall_hook( REDIRECTION_FILE, array( 'Redirection_Admin', 'plugin_uninstall' ) );

		add_action( 'wp_ajax_red_log_delete', array( &$this, 'ajax_log_delete' ) );
		add_action( 'wp_ajax_red_module_edit', array( &$this, 'ajax_module_edit' ) );
		add_action( 'wp_ajax_red_module_save', array( &$this, 'ajax_module_save' ) );
		add_action( 'wp_ajax_red_group_edit', array( &$this, 'ajax_group_edit' ) );
		add_action( 'wp_ajax_red_group_save', array( &$this, 'ajax_group_save' ) );
		add_action( 'wp_ajax_red_redirect_add', array( &$this, 'ajax_redirect_add' ) );
		add_action( 'wp_ajax_red_redirect_edit', array( &$this, 'ajax_redirect_edit' ) );
		add_action( 'wp_ajax_red_redirect_save', array( &$this, 'ajax_redirect_save' ) );
		add_action( 'wp_ajax_red_get_htaccess', array( &$this, 'ajax_get_htaccess' ) );
		add_action( 'wp_ajax_red_get_nginx', array( &$this, 'ajax_get_nginx' ) );

		$this->monitor = new Red_Monitor( red_get_options() );
	}

	public static function plugin_activated() {
		Redirection_Admin::update();
		Red_Flusher::schedule();
	}

	public static function plugin_deactivated() {
		Red_Flusher::clear();
	}

	public static function plugin_uninstall() {
		include dirname( REDIRECTION_FILE ).'/models/database.php';

		$db = new RE_Database();
		$db->remove( REDIRECTION_FILE );
	}

	private function render( $template, $template_vars = array() ) {
		foreach ( $template_vars as $key => $val ) {
			$$key = $val;
		}

		if ( file_exists( dirname( REDIRECTION_FILE )."/view/$template.php" ) )
			include dirname( REDIRECTION_FILE )."/view/$template.php";
	}

	private function capture( $ug_name, $ug_vars = array() ) {
		ob_start();

		$this->render( $ug_name, $ug_vars );
		$output = ob_get_contents();

		ob_end_clean();
		return $output;
	}

	private function render_error( $message ) {
	?>
<div class="fade error" id="message">
	<p><?php echo $message ?></p>
</div>
<?php
	}

	private function render_message( $message, $timeout = 0 ) {
		?>
<div class="updated" id="message" onclick="this.parentNode.removeChild(this)">
	<p><?php echo $message ?></p>
</div>
	<?php
	}

	private static function update() {
		$version = get_option( 'redirection_version' );

		Red_Flusher::schedule();

		if ( $version !== REDIRECTION_VERSION ) {
			include_once dirname( REDIRECTION_FILE ).'/models/database.php';

			$database = new RE_Database();
			return $database->upgrade( $version, REDIRECTION_VERSION );
		}

		return true;
	}

	private function select( $items, $default = '' ) {
		foreach ( $items as $key => $value ) {
			if ( is_array( $value ) )	{
				echo '<optgroup label="'.esc_attr( $key ).'">';

				foreach ( $value as $sub => $subvalue ) {
					echo '<option value="'.esc_attr( $sub ).'"'.( $sub === $default ? ' selected="selected"' : '' ).'>'.esc_html( $subvalue ).'</option>';
				}

				echo '</optgroup>';
			}
			else
				echo '<option value="'.esc_attr( $key ).'"'.( $key === $default ? ' selected="selected"' : '' ).'>'.esc_html( $value ).'</option>';
		}
	}

	function set_per_page( $status, $option, $value ) {
		if ( $option === 'redirection_log_per_page' )
			return $value;
		return $status;
	}

	function plugin_settings( $links ) {
		$settings_link = '<a href="tools.php?page='.basename( REDIRECTION_FILE ).'&amp;sub=options">'.__( 'Settings', 'redirection' ).'</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	function redirection_head() {
		$version = get_plugin_data( REDIRECTION_FILE );
		$version = $version['Version'];

		$this->inject();

		if ( ! isset( $_GET['sub'] ) || ( isset( $_GET['sub'] ) && ( in_array( $_GET['sub'], array( 'log', '404s', 'groups' ) ) ) ) )
			add_screen_option( 'per_page', array( 'label' => __( 'Log entries', 'redirection' ), 'default' => 25, 'option' => 'redirection_log_per_page' ) );

		wp_enqueue_script( 'redirection', plugin_dir_url( REDIRECTION_FILE ).'redirection.js', array( 'jquery-form', 'jquery-ui-sortable' ), $version );
		wp_enqueue_style( 'redirection', plugin_dir_url( REDIRECTION_FILE ).'admin.css', $version );

		wp_localize_script( 'redirection', 'Redirectioni10n', array(
			'error_msg' => __( 'Sorry, unable to do that. Please try refreshing the page.' ),
		) );
	}

	function admin_menu() {
		add_management_page( __( 'Redirection', 'redirection' ), __( 'Redirection', 'redirection' ), apply_filters( 'redirection_role', 'administrator' ), basename( REDIRECTION_FILE ), array( &$this, 'admin_screen' ) );
	}

	function admin_screen() {
	  	Redirection_Admin::update();

		if ( isset( $_GET['sub'] ) ) {
			if ( $_GET['sub'] === 'log' )
				return $this->admin_screen_log();
			elseif ( $_GET['sub'] === '404s' )
				return $this->admin_screen_404();
			elseif ( $_GET['sub'] === 'options' )
				return $this->admin_screen_options();
			elseif ( $_GET['sub'] === 'process' )
				return $this->admin_screen_process();
			elseif ( $_GET['sub'] === 'groups' )
				return $this->admin_groups( isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0 );
			elseif ( $_GET['sub'] === 'modules' )
				return $this->admin_screen_modules();
			elseif ( $_GET['sub'] === 'support' )
				return $this->render( 'support', array( 'options' => red_get_options() ) );
		}

		return $this->admin_redirects( isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0 );
	}

	function admin_screen_modules() {
		$options = red_get_options();
		$pager = new Redirection_Module_Table( $options['token'] );
		$pager->prepare_items();

		$this->render( 'module-list', array( 'options' => $options, 'table' => $pager ) );
	}

	function inject() {
		$options = red_get_options();

		if ( isset( $_POST['id'] ) && ! isset( $_POST['action'] ) ) {
			wp_safe_redirect( add_query_arg( 'id', intval( $_POST['id'] ), $_SERVER['REQUEST_URI'] ) );
			die();
		}

		if ( isset( $_GET['token'] ) && isset( $_GET['page'] ) && isset( $_GET['sub'] ) && $_GET['token'] === $options['token'] && $_GET['page'] === 'redirection.php' ) {
			$exporter = Red_FileIO::create( $_GET['sub'] );
			if ( $exporter ) {
				$items = Red_Item::get_all_for_module( intval( $_GET['module'] ) );

				$exporter->export( $items );
				die();
			}
		}
		elseif ( isset( $_POST['export-csv'] ) && check_admin_referer( 'redirection-log_management' ) ) {
			if ( isset( $_GET['sub'] ) && $_GET['sub'] === 'log' )
				RE_Log::export_to_csv();
			else
				RE_404::export_to_csv();
			die();
		}
	}

	function admin_screen_options() {
		if ( isset( $_POST['regenerate'] ) && check_admin_referer( 'redirection-update_options' ) ) {
			$options = red_get_options();
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

			if ( trim( $options['token'] ) === '' )
				$options['token'] = md5( uniqid() );

			update_option( 'redirection_options', $options );

			Red_Flusher::schedule();
			$this->render_message( __( 'Your options were updated', 'redirection' ) );
		}
		elseif ( isset( $_POST['delete'] ) && check_admin_referer( 'redirection-delete_plugin' ) ) {
			$this->plugin_uninstall();

			$current = get_option( 'active_plugins' );
			array_splice( $current, array_search( basename( dirname( REDIRECTION_FILE ) ).'/'.basename( REDIRECTION_FILE ), $current ), 1 );
			update_option( 'active_plugins', $current );

			$this->render_message( __( 'Redirection data has been deleted and the plugin disabled', 'redirection' ) );
			return;
		}
		elseif ( isset( $_POST['import'] ) && check_admin_referer( 'redirection-import' ) ) {
			$count = Red_FileIO::import( $_POST['group'], $_FILES['upload'] );

			if ( $count > 0 )
				$this->render_message( sprintf( _n( '%d redirection was successfully imported','%d redirections were successfully imported', $count, 'redirection' ), $count ) );
			else
				$this->render_message( __( 'No items were imported', 'redirection' ) );
		}

		$groups = Red_Group::get_for_select();
		$this->render( 'options', array( 'options' => red_get_options(), 'groups' => $groups ) );
	}

	function admin_screen_log() {
		$options = red_get_options();

		if ( isset( $_POST['delete-all'] ) && check_admin_referer( 'redirection-log_management' ) ) {
			RE_Log::delete_all();
			$this->render_message( __( 'Your logs have been deleted', 'redirection' ) );
		}

		$table = new Redirection_Log_Table( $options );

		if ( isset( $_GET['module'] ) )
			$table->prepare_items( 'module', intval( $_GET['module'] ) );
		else if ( isset( $_GET['group'] ) )
			$table->prepare_items( 'group', intval( $_GET['group'] ) );
		else if ( isset( $_GET['redirect'] ) )
			$table->prepare_items( 'redirect', intval( $_GET['redirect'] ) );
		else
			$table->prepare_items();

		$this->render( 'log', array( 'options' => $options, 'table' => $table, 'lookup' => $options['lookup'], 'type' => 'log' ) );
	}

	function admin_screen_404() {
		if ( isset( $_POST['delete-all'] ) && check_admin_referer( 'redirection-log_management' ) ) {
			RE_404::delete_all();
			$this->render_message( __( 'Your logs have been deleted', 'redirection' ) );
		}

		$options = red_get_options();

		$table = new Redirection_404_Table( $options );
		$table->prepare_items( isset( $_GET['ip'] ) ? $_GET['ip'] : false );

		$this->render( 'log', array( 'options' => $options, 'table' => $table, 'lookup' => $options['lookup'], 'type' => '404s' ) );
	}

	function admin_groups( $module ) {
		if ( isset( $_POST['add'] ) && check_admin_referer( 'redirection-add_group' ) ) {
			if ( Red_Group::create( stripslashes( $_POST['name'] ), intval( $_POST['module_id'] ) ) ) {
				$this->render_message( __( 'Your group was added successfully', 'redirection' ) );
			}
			else
				$this->render_error( __( 'Please specify a group name', 'redirection' ) );
		}

		$table = new Redirection_Group_Table( Red_Module::get_for_select() );
		$table->prepare_items();

		$this->render( 'group-list', array( 'options' => red_get_options(), 'table' => $table, 'modules' => Red_Module::get_for_select(), 'module' => $module ) );
	}

	function admin_redirects( $group_id ) {
		$table = new Redirection_Table( Red_Group::get_for_select(), $group_id );
		$table->prepare_items();

		$this->render( 'item-list', array( 'options' => red_get_options(), 'group' => $group_id, 'table' => $table, 'date_format' => get_option( 'date_format' ) ) );
	}

	function locales() {
		$locales = array();
		if ( file_exists( dirname( REDIRECTION_FILE ).'/readme.txt' ) ) {
			$readme = file_get_contents( dirname( REDIRECTION_FILE ).'/readme.txt' );

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

	public function ajax_log_delete() {
		if ( check_ajax_referer( 'redirection-items' ) ) {
			if ( preg_match_all( '/=(\d*)/', $_POST['checked'], $items ) > 0 ) {
				foreach ( $items[1] as $item ) {
					RE_Log::delete( intval( $item ) );
				}
			}
		}
	}

	private function check_ajax_referer( $nonce ) {
		if ( check_ajax_referer( $nonce, false, false ) === false )
			$this->output_ajax_response( array( 'error' => __( 'Unable to perform action' ).' - bad nonce' ) );
	}

	public function ajax_module_edit() {
		$module_id = intval( $_POST['id'] );

		$this->check_ajax_referer( 'red_edit-'.$module_id );

		$module = Red_Module::get( $module_id );
		if ( $module )
			$json['html'] = $this->capture( 'module-edit', array( 'module' => $module ) );
		else
			$json['error'] = __( 'Unable to perform action' ).' - could not find module';

		$this->output_ajax_response( $json );
	}

	public function ajax_module_save() {
		$module_id = intval( $_POST['id'] );

		$this->check_ajax_referer( 'red_module_save_'.$module_id );

		$module = Red_Module::get( $module_id );
		if ( $module ) {
			$result = $module->update( $_POST );

			if ( $result === true ) {
				global $hook_suffix;

				$hook_suffix = '';
				$options = red_get_options();
				$pager = new Redirection_Module_Table( $options['token'] );

				$json = array( 'html' => $pager->column_name( $module ) );
			}
			else
				$json['error'] = $result;
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
			$json['html'] = $this->capture( 'group-edit', array( 'group' => $group, 'modules' => Red_Module::get_for_select() ) );
		else
			$json['error'] = __( 'Unable to perform action' ).' - could not find group';

		$this->output_ajax_response( $json );
	}

	public function ajax_group_save() {
		global $hook_suffix;

		$hook_suffix = '';
		$group_id = intval( $_POST['id'] );

		$this->check_ajax_referer( 'redirection-group_save_'.$group_id );

		$group = Red_Group::get( $group_id );
		if ( $group ) {
			$group->update( $_POST );
			$module = Red_Module::get( $group->get_module_id() );

			$pager = new Redirection_Group_Table( array(), false );
			$json = array( 'html' => $pager->column_name( $group ), 'module' => $module->get_name() );
		}
		else
			$json['error'] = __( 'Unable to perform action' ).' - could not find redirect';

		$this->output_ajax_response( $json );
	}

	public function ajax_redirect_edit() {
		$this->check_ajax_referer( 'red-edit_'.intval( $_POST['id'] ) );
		$redirect = Red_Item::get_by_id( intval( $_POST['id'] ) );

		if ( $redirect )
			$json['html'] = $this->capture( 'item-edit', array( 'redirect' => $redirect, 'groups' => Red_Group::get_for_select() ) );
		else
			$json['error'] = __( 'Unable to perform action' ).' - could not find redirect';

		$this->output_ajax_response( $json );
	}

	public function ajax_redirect_save() {
		global $hook_suffix;

		$hook_suffix = '';

		$red_id = intval( $_POST['id'] );

		$this->check_ajax_referer( 'redirection-redirect_save_'.$red_id );

		$redirect = Red_Item::get_by_id( $red_id );
		if ( $redirect ) {
			$redirect->update( $_POST );

			$pager = new Redirection_Table( array(), 0 );
			$json = array( 'html' => $pager->column_url( $redirect ), 'code' => $redirect->get_action_code() );
		}
		else
			$json['error'] = __( 'Unable to perform action' ).' - could not find redirect';

		$this->output_ajax_response( $json );
	}

	public function ajax_redirect_add() {
		global $hook_suffix;

		$hook_suffix = '';

		$this->check_ajax_referer( 'redirection-redirect_add' );

		$item = Red_Item::create( $_POST );
		if ( is_wp_error( $item ) )
			$json['error'] = $item->get_error_message();
		elseif ( $item !== false ) {
			$pager = new Redirection_Table( array(), 0 );
			$json = array( 'html' => $pager->get_row( $item ) );
		}
		else
			$json['error'] = __( 'Sorry, but your redirection was not created', 'redirection' );

		$this->output_ajax_response( $json );
	}

	private function get_module_column( $module_id, $export_type ) {
		$json['error'] = __( 'Invalid module', 'redirection' );

		$module = Red_Module::get( $module_id );
		$exporter = Red_FileIO::create( $export_type );

		if ( $module && $exporter ) {
			global $hook_suffix;

			$hook_suffix = '';
			$options  = red_get_options();
			$pager    = new Redirection_Module_Table( $options['token'] );
			$items    = Red_Item::get_all_for_module( $module_id );

			$json = array( 'html' => $pager->column_name( $module ) );

			$json['html'] .= '<textarea readonly="readonly" class="module-export" rows="10">'.esc_textarea( $exporter->get( $items ) ).'</textarea>';
			$json['html'] .= '<div class="table-actions"><a href="?page=redirection.php&amp;token='.$options['token'].'&amp;sub='.$export_type.'&amp;module='.$module_id.'"><input class="button-primary" type="button" value="'.__( 'Download', 'redirection' ).'"/></a> ';
			$json['html'] .= '<input class="button-secondary" type="submit" name="cancel" value="'.__( 'Cancel', 'redirection' ).'"/>';
		}

		$this->output_ajax_response( $json );
	}

	public function ajax_get_nginx() {
		$this->get_module_column( intval( $_POST['id'] ), 'nginx' );
	}

	public function ajax_get_htaccess() {
		$this->get_module_column( intval( $_POST['id'] ), 'apache' );
	}

	private function output_ajax_response( array $data ) {
		header( 'Content-Type: application/json' );
		echo wp_json_encode( $data );
		die();
	}
}

register_activation_hook( REDIRECTION_FILE, array( 'Redirection_Admin', 'plugin_activated' ) );

add_action( 'init', array( 'Redirection_Admin', 'init' ) );
