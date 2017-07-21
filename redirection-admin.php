<?php

include dirname( __FILE__ ).'/models/group.php';
include dirname( __FILE__ ).'/models/monitor.php';
include dirname( __FILE__ ).'/models/file-io.php';
include dirname( __FILE__ ).'/redirection-api.php';

define( 'RED_DEFAULT_PER_PAGE', 25 );

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
		add_action( 'redirection_save_options', array( $this, 'flush_schedule' ) );
		add_filter( 'set-screen-option', array( $this, 'set_per_page' ), 10, 3 );

		register_deactivation_hook( REDIRECTION_FILE, array( 'Redirection_Admin', 'plugin_deactivated' ) );
		register_uninstall_hook( REDIRECTION_FILE, array( 'Redirection_Admin', 'plugin_uninstall' ) );

		$this->monitor = new Red_Monitor( red_get_options() );
		$this->api = new Redirection_Api();

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
		global $wp_version;

		$options = red_get_options();
		$version = get_plugin_data( REDIRECTION_FILE );
		$version = $version['Version'];

		$this->inject();

		if ( ! isset( $_GET['sub'] ) || ( isset( $_GET['sub'] ) && ( in_array( $_GET['sub'], array( 'log', '404s', 'groups' ) ) ) ) ) {
			add_screen_option( 'per_page', array( 'label' => __( 'Log entries (100 max)', 'redirection' ), 'default' => RED_DEFAULT_PER_PAGE, 'option' => 'redirection_log_per_page' ) );
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
			'versions' => implode( ', ', array( 'Plugin '.$version, 'WordPress '.$wp_version, 'PHP '.phpversion() ) ),
		) );
	}

	private function get_per_page() {
		$per_page = intval( get_user_meta( get_current_user_id(), 'redirection_log_per_page', true ), 10 );

		return $per_page > 0 ? $per_page : RED_DEFAULT_PER_PAGE;
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

	private function user_has_access() {
		return current_user_can( apply_filters( 'redirection_role', 'administrator' ) );
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
		$this->render( 'group-list', array( 'options' => red_get_options() ) );
	}

	function admin_redirects( $group_id ) {
		$this->render( 'item-list', array( 'options' => red_get_options(), 'group' => $group_id ) );
	}
}

register_activation_hook( REDIRECTION_FILE, array( 'Redirection_Admin', 'plugin_activated' ) );

add_action( 'init', array( 'Redirection_Admin', 'init' ) );
