<?php

include dirname( __FILE__ ).'/models/group.php';
include dirname( __FILE__ ).'/models/monitor.php';
include dirname( __FILE__ ).'/models/file-io.php';
include dirname( __FILE__ ).'/redirection-api.php';

define( 'RED_DEFAULT_PER_PAGE', 25 );
define( 'RED_MAX_PER_PAGE', 200 );

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
		add_action( 'redirection_save_options', array( $this, 'notify_schedule' ) );
		add_filter( 'set-screen-option', array( $this, 'set_per_page' ), 10, 3 );

		if ( defined( 'REDIRECTION_FLYING_SOLO' ) && REDIRECTION_FLYING_SOLO ) {
			add_filter( 'script_loader_src', array( $this, 'flying_solo' ), 10, 2 );
		}

		register_deactivation_hook( REDIRECTION_FILE, array( 'Redirection_Admin', 'plugin_deactivated' ) );
		register_uninstall_hook( REDIRECTION_FILE, array( 'Redirection_Admin', 'plugin_uninstall' ) );

		$this->monitor = new Red_Monitor( red_get_options() );
		$this->api = new Redirection_Api();
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

	// So it finally came to this... some plugins include their JS in all pages, whether they are needed or not. If there is an error
	// then this can prevent Redirection running, it's a little sensitive about that. We use the nuclear option here to disable
	// all other JS while viewing Redirection
	public function flying_solo( $src, $handle ) {
		if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], 'page=redirection.php' ) !== false ) {
			if ( substr( $src, 0, 4 ) === 'http' && $handle !== 'redirection' && strpos( $src, 'plugins' ) !== false ) {
				return false;
			}
		}

		return $src;
	}

	public function flush_schedule() {
		Red_Flusher::schedule();
	}
	
	public function notify_schedule() {
		$notifier = new Red_Notify();
		$notifier->schedule();
	}

	private static function update() {
		$version = get_option( 'redirection_version' );

		Red_Flusher::schedule();

		if ( $version !== REDIRECTION_DB_VERSION ) {
			include_once dirname( REDIRECTION_FILE ).'/models/database.php';

			$database = new RE_Database();

			if ( $version === false ) {
				$database->install();
			}

			return $database->upgrade( $version, REDIRECTION_DB_VERSION );
		}

		return true;
	}

	function set_per_page( $status, $option, $value ) {
		if ( $option === 'redirection_log_per_page' ) {
			return max( 1, min( intval( $value, 10 ), RED_MAX_PER_PAGE ) );
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

		$build = REDIRECTION_VERSION.'-'.REDIRECTION_BUILD;
		$options = red_get_options();

		$this->inject();

		if ( ! isset( $_GET['sub'] ) || ( isset( $_GET['sub'] ) && ( in_array( $_GET['sub'], array( 'log', '404s', 'groups' ) ) ) ) ) {
			add_screen_option( 'per_page', array( 'label' => sprintf( __( 'Log entries (%d max)', 'redirection' ), RED_MAX_PER_PAGE ), 'default' => RED_DEFAULT_PER_PAGE, 'option' => 'redirection_log_per_page' ) );
		}

		if ( defined( 'REDIRECTION_DEV_MODE' ) && REDIRECTION_DEV_MODE ) {
			wp_enqueue_script( 'redirection', 'http://localhost:3312/redirection.js', array(), $build, true );
		} else {
			wp_enqueue_script( 'redirection', plugin_dir_url( REDIRECTION_FILE ).'redirection.js', array(), $build, true );
		}

		wp_enqueue_style( 'redirection', plugin_dir_url( REDIRECTION_FILE ).'redirection.css', array(), $build );

		wp_localize_script( 'redirection', 'Redirectioni10n', array(
			'WP_API_root' => admin_url( 'admin-ajax.php' ),
			'WP_API_nonce' => wp_create_nonce( 'wp_rest' ),
			'pluginBaseUrl' => plugins_url( '', REDIRECTION_FILE ),
			'pluginRoot' => admin_url( 'tools.php?page=redirection.php' ),
			'per_page' => $this->get_per_page(),
			'locale' => $this->get_i18n_data(),
			'localeSlug' => get_locale(),
			'token' => $options['token'],
			'autoGenerate' => $options['auto_target'],
			'versions' => implode( ', ', array( 'Plugin '.REDIRECTION_VERSION, 'WordPress '.$wp_version, 'PHP '.phpversion() ) ),
			'version' => REDIRECTION_VERSION,
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

	function admin_screen() {
	  	Redirection_Admin::update();

		$version = get_plugin_data( REDIRECTION_FILE );
		$version = $version['Version'];
?>
<div id="react-ui">
	<div class="react-loading">
		<h1><?php _e( 'Loading the bits, please wait...', 'redirection' ); ?></h1>

		<span class="react-loading-spinner" />
	</div>
	<noscript>Please enable JavaScript</noscript>
</div>

<?php
	}

	private function user_has_access() {
		return current_user_can( apply_filters( 'redirection_role', 'administrator' ) );
	}

	function inject() {
		if ( isset( $_GET['page'] ) && isset( $_GET['sub'] ) && $_GET['page'] === 'redirection.php' ) {
			$this->tryExportLogs();
			$this->tryExportRedirects();
			$this->tryExportRSS();
		}
	}

	function tryExportRSS() {
		if ( isset( $_GET['token'] ) && $_GET['sub'] === 'rss' ) {
			$options = red_get_options();

			if ( $_GET['token'] === $options['token'] && !empty( $options['token'] ) ) {
				$items = Red_Item::get_all_for_module( intval( $_GET['module'] ) );

				$exporter = Red_FileIO::create( 'rss' );
				$exporter->force_download();
				echo $exporter->get_data( $items, array() );
				die();
			}
		}
	}

	private function tryExportLogs() {
		if ( $this->user_has_access() && isset( $_POST['export-csv'] ) && check_admin_referer( 'wp_rest' ) ) {
			if ( isset( $_GET['sub'] ) && $_GET['sub'] === 'log' ) {
				RE_Log::export_to_csv();
			} else {
				RE_404::export_to_csv();
			}

			die();
		}
	}

	private function tryExportRedirects() {
		if ( $this->user_has_access() && $_GET['sub'] === 'modules' && isset( $_GET['exporter'] ) && isset( $_GET['export'] ) ) {
			$export = Red_FileIO::export( $_GET['export'], $_GET['exporter'] );

			if ( $export !== false ) {
				$export['exporter']->force_download();
				echo $export['data'];
				die();
			}
		}
	}
}

register_activation_hook( REDIRECTION_FILE, array( 'Redirection_Admin', 'plugin_activated' ) );

add_action( 'init', array( 'Redirection_Admin', 'init' ) );
