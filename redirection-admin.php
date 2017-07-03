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
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'load-tools_page_redirection', array( $this, 'redirection_head' ) );
		add_action( 'plugin_action_links_'.basename( dirname( REDIRECTION_FILE ) ).'/'.basename( REDIRECTION_FILE ), array( $this, 'plugin_settings' ), 10, 4 );

		add_filter( 'set-screen-option', array( $this, 'set_per_page' ), 10, 3 );

		register_deactivation_hook( REDIRECTION_FILE, array( 'Redirection_Admin', 'plugin_deactivated' ) );
		register_uninstall_hook( REDIRECTION_FILE, array( 'Redirection_Admin', 'plugin_uninstall' ) );

		add_action( 'wp_ajax_red_group_edit', array( $this, 'ajax_group_edit' ) );
		add_action( 'wp_ajax_red_group_save', array( $this, 'ajax_group_save' ) );
		add_action( 'wp_ajax_red_redirect_add', array( $this, 'ajax_redirect_add' ) );
		add_action( 'wp_ajax_red_redirect_edit', array( $this, 'ajax_redirect_edit' ) );
		add_action( 'wp_ajax_red_redirect_save', array( $this, 'ajax_redirect_save' ) );

		add_action( 'wp_ajax_red_load_settings', array( $this, 'ajax_load_settings' ) );
		add_action( 'wp_ajax_red_save_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_red_get_logs', array( $this, 'ajax_get_logs' ) );
		add_action( 'wp_ajax_red_log_action', array( $this, 'ajax_log_action' ) );
		add_action( 'wp_ajax_red_delete_plugin', array( $this, 'ajax_delete_plugin' ) );
		add_action( 'wp_ajax_red_delete_all', array( $this, 'ajax_delete_all' ) );
		add_action( 'wp_ajax_red_get_module', array( $this, 'ajax_get_module' ) );
		add_action( 'wp_ajax_red_set_module', array( $this, 'ajax_set_module' ) );

		add_action( 'redirection_save_options', array( $this, 'flush_schedule' ) );

		$this->monitor = new Red_Monitor( red_get_options() );

		$this->export_rss();
	}

	public static function plugin_activated() {
		Redirection_Admin::update();
		Red_Flusher::schedule();

		update_option( 'redirection_options', red_get_options() );
	}

	public static function plugin_deactivated() {
		Red_Flusher::clear();
	}

	public static function plugin_uninstall() {
		include_once dirname( REDIRECTION_FILE ).'/models/database.php';

		$db = new RE_Database();
		$db->remove( REDIRECTION_FILE );

		delete_option( 'redirection_options' );
	}

	public function flush_schedule() {
		Red_Flusher::schedule();
	}

	private function render( $template, $template_vars = array() ) {
		foreach ( $template_vars as $key => $val ) {
			$$key = $val;
		}

		if ( file_exists( dirname( REDIRECTION_FILE )."/view/$template.php" ) ) {
			include dirname( REDIRECTION_FILE )."/view/$template.php";
		}
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

			if ( $version === false ) {
				$database->install();
			}

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
		if ( $option === 'redirection_log_per_page' ) {
			return max( 1, min( intval( $value, 10 ), 100 ) );
		}

		return $status;
	}

	function plugin_settings( $links ) {
		$settings_link = '<a href="tools.php?page='.basename( REDIRECTION_FILE ).'&amp;sub=options">'.__( 'Settings', 'redirection' ).'</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	function redirection_head() {
		$options = red_get_options();
		$version = get_plugin_data( REDIRECTION_FILE );
		$version = $version['Version'];

		$this->inject();

		if ( ! isset( $_GET['sub'] ) || ( isset( $_GET['sub'] ) && ( in_array( $_GET['sub'], array( 'log', '404s', 'groups' ) ) ) ) ) {
			add_screen_option( 'per_page', array( 'label' => __( 'Log entries', 'redirection' ), 'default' => 25, 'option' => 'redirection_log_per_page' ) );
		}

		wp_enqueue_script( 'redirection', plugin_dir_url( REDIRECTION_FILE ).'redirection.js', array( 'jquery-form', 'jquery-ui-sortable' ), $version );

		if ( defined( 'REDIRECTION_DEV_MODE' ) && REDIRECTION_DEV_MODE ) {
			wp_enqueue_script( 'redirection-ui', 'http://localhost:3312/redirection-ui.js', array( 'redirection' ), $version );
		} else {
			wp_enqueue_script( 'redirection-ui', plugin_dir_url( REDIRECTION_FILE ).'redirection-ui.js', array( 'redirection' ), $version );
		}

		wp_enqueue_style( 'redirection', plugin_dir_url( REDIRECTION_FILE ).'admin.css', $version );

		wp_localize_script( 'redirection', 'Redirectioni10n', array(
			'WP_API_root' => admin_url( 'admin-ajax.php' ),
			'WP_API_nonce' => wp_create_nonce( 'wp_rest' ),
			'pluginBaseUrl' => plugins_url( '', REDIRECTION_FILE ),
			'pluginRoot' => admin_url( 'tools.php?page=redirection.php' ),
			'per_page' => $this->get_per_page(),
			'locale' => $this->get_i18n_data(),
			'localeSlug' => get_locale(),
			'token' => $options['token'],
		) );
	}

	private function get_per_page() {
		$per_page = intval( get_user_meta( get_current_user_id(), 'redirection_log_per_page', true ), 10 );

		return $per_page > 0 ? $per_page : 25;
	}

	private function get_i18n_data() {
		$i18n_json = REDIRECTION_FILE . 'locale/json/redirection-' . get_locale() . '.json';

		if ( is_file( $i18n_json ) && is_readable( $i18n_json ) ) {
			$locale_data = @file_get_contents( $i18n_json );

			if ( $locale_data ) {
				return $locale_data;
			}
		}

		// Return empty if we have nothing to return so it doesn't fail when parsed in JS
		return '{}';
	}

	function admin_menu() {
		add_management_page( 'Redirection', 'Redirection', apply_filters( 'redirection_role', 'administrator' ), basename( REDIRECTION_FILE ), array( &$this, 'admin_screen' ) );
	}

	function export_rss() {
		if ( isset( $_GET['token'] ) && isset( $_GET['page'] ) && isset( $_GET['sub'] ) && $_GET['page'] === 'redirection.php' && $_GET['sub'] === 'rss' ) {
			$options = red_get_options();

			if ( $_GET['token'] === $options['token'] && !empty( $options['token'] ) ) {
				$items = Red_Item::get_all_for_module( intval( $_GET['module'] ) );

				$exporter = Red_FileIO::create( 'rss' );
				$exporter->export( $items );
				die();
			}
		}
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

		$this->render( 'module-list', array( 'options' => $options ) );
	}

	function inject() {
		if ( isset( $_POST['id'] ) && ! isset( $_POST['action'] ) ) {
			wp_safe_redirect( add_query_arg( 'id', intval( $_POST['id'] ), $_SERVER['REQUEST_URI'] ) );
			die();
		}

		if ( isset( $_GET['page'] ) && isset( $_GET['sub'] ) && $_GET['page'] === 'redirection.php' ) {
			if ( isset( $_POST['export-csv'] ) && check_admin_referer( 'wp_rest' ) ) {
				if ( isset( $_GET['sub'] ) && $_GET['sub'] === 'log' ) {
					RE_Log::export_to_csv();
				} else {
					RE_404::export_to_csv();
				}

				die();
			}

			if ( $this->user_has_access() && $_GET['sub'] === 'modules' && isset( $_GET['exporter'] ) && isset( $_GET['export'] ) ) {
				$exporter = Red_FileIO::create( $_GET['exporter'] );

				if ( $exporter ) {
					$items = Red_Item::get_all_for_module( intval( $_GET['export'] ) );

					$exporter->export( $items );
					die();
				}
			}
		}
	}

	function admin_screen_options() {
		if ( isset( $_POST['import'] ) && check_admin_referer( 'wp_rest' ) ) {
			$count = Red_FileIO::import( $_POST['group'], $_FILES['upload'] );

			if ( $count > 0 ) {
				$this->render_message( sprintf( _n( '%d redirection was successfully imported','%d redirections were successfully imported', $count, 'redirection' ), $count ) );
			} else {
				$this->render_message( __( 'No items were imported', 'redirection' ) );
			}
		}

		$groups = Red_Group::get_for_select();
		$this->render( 'options', array( 'options' => red_get_options(), 'groups' => $groups ) );
	}

	function admin_screen_log() {
		$options = red_get_options();

		$this->render( 'log', array( 'options' => $options, 'title' => __( 'Redirection Log', 'redirection' ) ) );
	}

	function admin_screen_404() {
		$options = red_get_options();

		$this->render( 'log', array( 'options' => $options, 'title' => __( 'Redirection 404', 'redirection' ) ) );
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

	private function check_ajax_referer( $nonce ) {
		if ( check_ajax_referer( $nonce, false, false ) === false ) {
			return $this->output_ajax_response( array( 'error' => __( 'Unable to perform action' ).' - bad nonce ("'.$nonce.'")' ) );
		}

		if ( $this->user_has_access() === false ) {
			return $this->output_ajax_response( array( 'error' => __( 'No permissions to perform action' ) ) );
		}

		return true;
	}

	private function user_has_access() {
		return current_user_can( apply_filters( 'redirection_role', 'administrator' ) );
	}

	public function ajax_group_edit() {
		$group_id = intval( $_POST['id'] );

		$this->check_ajax_referer( 'red-edit_'.$group_id );

		$group = Red_Group::get( $group_id );
		if ( $group )
			$json['html'] = $this->capture( 'group-edit', array( 'group' => $group, 'modules' => Red_Module::get_for_select() ) );
		else
			$json['error'] = __( 'Unable to perform action' ).' - could not find group';

		return $this->output_ajax_response( $json );
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

		return $this->output_ajax_response( $json );
	}

	public function ajax_redirect_edit() {
		$this->check_ajax_referer( 'red-edit_'.intval( $_POST['id'] ) );
		$redirect = Red_Item::get_by_id( intval( $_POST['id'] ) );

		if ( $redirect )
			$json['html'] = $this->capture( 'item-edit', array( 'redirect' => $redirect, 'groups' => Red_Group::get_for_select() ) );
		else
			$json['error'] = __( 'Unable to perform action' ).' - could not find redirect';

		return $this->output_ajax_response( $json );
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

		return $this->output_ajax_response( $json );
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

		return $this->output_ajax_response( $json );
	}

	public function ajax_get_module( $params ) {
		$ajax = $this->check_ajax_referer( 'wp_rest' );
		if ( $ajax !== true ) {
			return $ajax;
		}

		if ( empty( $params ) ) {
			$params = $_POST;
		}

		$modules = array( 'apache', 'nginx', 'wordpress' );
		$moduleType = false;

		if ( isset( $params['moduleName'] ) && in_array( $params['moduleName'], array( 'apache', 'nginx', 'wordpress' ) ) ) {
			$modules = array( $params['moduleName'] );
		}

		if ( isset( $params['moduleType'] ) && in_array( $params['moduleType'], array( 'csv', 'apache', 'nginx' ) ) ) {
			$moduleType = $params['moduleType'];
		}

		foreach ( $modules as $module ) {
			$result[ $module ] = $this->get_module_data( $module, $moduleType );
		}

		if ( isset( $result[ 'apache'] ) ) {
			$apache = Red_Module::get( Apache_Module::MODULE_ID );

			$result['apache']['data'] = array(
				'installed' => ABSPATH,
				'location' => $apache->get_location(),
				'canonical' => $apache->get_canonical(),
			);
		}

		return $this->output_ajax_response( $result );
	}

	public function ajax_set_module( $params ) {
		$ajax = $this->check_ajax_referer( 'wp_rest' );
		if ( $ajax !== true ) {
			return $ajax;
		}

		if ( empty( $params ) ) {
			$params = $_POST;
		}

		if ( isset( $params['module'] ) ) {
			$module = $this->get_module( $params['module'] );

			if ( $module ) {
				$module->update( $params );
			}
		}

		return $this->ajax_get_module( $params );
	}

	private function get_module( $module_name ) {
		$module_id_name = array(
			'apache' => Apache_Module::MODULE_ID,
			'wordpress' => WordPress_Module::MODULE_ID,
			'nginx' => Nginx_Module::MODULE_ID,
		);

		if ( isset( $module_id_name[ $module_name ] ) ) {
			return Red_Module::get( $module_id_name[ $module_name ] );
		}

		return false;
	}

	private function get_module_data( $moduleName, $type ) {
		$module = $this->get_module( $moduleName );
		$data = array( 'redirects' => $module->get_total_redirects() );

		if ( $type ) {
			$exporter = Red_FileIO::create( $type );
			$data['data'] = $exporter->get( Red_Item::get_all_for_module( $module->get_id() ) );
		}

		return $data;
	}

	public function ajax_delete_plugin() {
		$ajax = $this->check_ajax_referer( 'wp_rest' );
		if ( $ajax !== true ) {
			return $ajax;
		}

		$this->plugin_uninstall();

		$current = get_option( 'active_plugins' );
		array_splice( $current, array_search( basename( dirname( REDIRECTION_FILE ) ).'/'.basename( REDIRECTION_FILE ), $current ), 1 );
		update_option( 'active_plugins', $current );

		return $this->output_ajax_response( array( 'location' => admin_url().'plugins.php' ) );
	}

	public function ajax_load_settings() {
		$ajax = $this->check_ajax_referer( 'wp_rest' );
		if ( $ajax !== true ) {
			return $ajax;
		}
		return $this->output_ajax_response( array( 'settings' => red_get_options(), 'groups' => $this->groups_to_json( Red_Group::get_for_select() ) ) );
	}

	public function ajax_save_settings( $settings = array() ) {
		$ajax = $this->check_ajax_referer( 'wp_rest' );
		if ( $ajax !== true ) {
			return $ajax;
		}

		if ( empty( $settings ) ) {
			$settings = $_POST;
		}

		$options = red_get_options();

		if ( isset( $settings['monitor_post'] ) ) {
			$options['monitor_post'] = max( 0, intval( $settings['monitor_post'], 10 ) );
		}

		if ( isset( $settings['auto_target'] ) ) {
			$options['auto_target'] = stripslashes( $settings['auto_target'] );
		}

		if ( isset( $settings['support'] ) ) {
			$options['support'] = $settings['support'] === 'true' ? true : false;
		}

		if ( isset( $settings['token'] ) ) {
			$options['token'] = stripslashes( $settings['token'] );
		}

		if ( !isset( $settings['token'] ) || trim( $options['token'] ) === '' ) {
			$options['token'] = md5( uniqid() );
		}

		if ( isset( $settings['newsletter'] ) ) {
			$options['newsletter'] = $settings['newsletter'] === 'true' ? true : false;
		}

		if ( isset( $settings['expire_redirect'] ) ) {
			$options['expire_redirect'] = max( -1, min( intval( $settings['expire_redirect'], 10 ), 60 ) );
		}

		if ( isset( $settings['expire_404'] ) ) {
			$options['expire_404'] = max( -1, min( intval( $settings['expire_404'], 10 ), 60 ) );
		}

		update_option( 'redirection_options', $options );
		do_action( 'redirection_save_options', $options );

		return $this->output_ajax_response( array( 'settings' => $options, 'groups' => $this->groups_to_json( Red_Group::get_for_select() ) ) );
	}

	public function ajax_get_logs( $params ) {
		$ajax = $this->check_ajax_referer( 'wp_rest' );
		if ( $ajax !== true ) {
			return $ajax;
		}

		if ( empty( $params ) ) {
			$params = $_POST;
		}

		$result = $this->get_logs( $params );

		return $this->output_ajax_response( $result );
	}

	public function ajax_log_action( $params ) {
		$ajax = $this->check_ajax_referer( 'wp_rest' );
		if ( $ajax !== true ) {
			return $ajax;
		}

		if ( empty( $params ) ) {
			$params = $_POST;
		}

		// Do the action
		if ( isset( $params['bulk'] ) && isset( $params['items'] ) && $params['bulk'] === 'delete' ) {
			$items = explode( ',', $params['items'] );

			if ( $this->get_log_type( $params ) === 'log' ) {
				array_map( array( 'RE_Log', 'delete' ), $items );
			} else {
				array_map( array( 'RE_404', 'delete' ), $items );
			}
		}

		$result = $this->get_logs( $params );

		return $this->output_ajax_response( $result );
	}

	public function ajax_delete_all( $params ) {
		$ajax = $this->check_ajax_referer( 'wp_rest' );
		if ( $ajax !== true ) {
			return $ajax;
		}

		if ( empty( $params ) ) {
			$params = $_POST;
		}

		if ( isset( $params['logType'] ) ) {
			if ( $params['logType'] === 'log' ) {
				RE_Log::delete_all();
			} else {
				RE_404::delete_all();
			}
		}

		$result = $this->get_logs( $params );

		return $this->output_ajax_response( $result );
	}

	private function get_log_type( $params ) {
		$type = 'log';

		if ( isset( $params['logType'] ) && in_array( $params['logType'], array( 'log', '404' ), true ) ) {
			$type = $params['logType'];
		}

		return $type;
	}

	private function get_logs( array $params ) {
		$type = $this->get_log_type( $params );

		if ( $type === 'log' ) {
			return RE_Filter_Log::get( 'redirection_logs', 'RE_Log', $params );
		} else if ( $type === '404' ) {
			if ( isset( $params['filterBy'] ) && isset( $params['filter'] ) && $params['filterBy'] === 'ip' ) {
				$params['filter'] = ip2long( $params['filter'] );
			}

			return RE_Filter_Log::get( 'redirection_404', 'RE_404', $params );
		}

		return array( 'items' => array(), 'total' => 0 );
	}

	private function groups_to_json( $groups, $depth = 0 ) {
		$items = array();

		foreach ( $groups as $text => $value ) {
			if ( is_array( $value ) && $depth === 0 ) {
				$items[] = (object)array( 'text' => $text, 'value' => $this->groups_to_json( $value, 1 ) );
			} else {
				$items[] = (object)array( 'text' => $value, 'value' => $text );
			}
		}

		return $items;
	}

	private function output_ajax_response( array $data ) {
		$result = wp_json_encode( $data );

		if ( defined( 'DOING_AJAX' ) ) {
			header( 'Content-Type: application/json' );
			echo $result;
			wp_die();
		}

		return $result;
	}
}

register_activation_hook( REDIRECTION_FILE, array( 'Redirection_Admin', 'plugin_activated' ) );

add_action( 'init', array( 'Redirection_Admin', 'init' ) );
