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
		add_action( 'plugin_action_links_'.basename( dirname( REDIRECTION_FILE ) ).'/'.basename( REDIRECTION_FILE ), array( $this, 'plugin_settings' ), 10, 4 );
		add_filter( 'redirection_save_options', array( $this, 'flush_schedule' ) );
		add_filter( 'set-screen-option', array( $this, 'set_per_page' ), 10, 3 );

		if ( defined( 'REDIRECTION_FLYING_SOLO' ) && REDIRECTION_FLYING_SOLO ) {
			add_filter( 'script_loader_src', array( $this, 'flying_solo' ), 10, 2 );
		}

		register_deactivation_hook( REDIRECTION_FILE, array( 'Redirection_Admin', 'plugin_deactivated' ) );
		register_uninstall_hook( REDIRECTION_FILE, array( 'Redirection_Admin', 'plugin_uninstall' ) );

		$this->monitor = new Red_Monitor( red_get_options() );
		$this->api = new Redirection_Api();
	}

	// These are only called on the single standard site, or in the network admin of the multisite - they run across all available sites
	public static function plugin_activated() {
		if ( is_network_admin() ) {
			foreach ( get_sites() as $site ) {
				switch_to_blog( $site->blog_id );

				Redirection_Admin::update();
				Red_Flusher::schedule();
				red_set_options();

				restore_current_blog();
			}
		} else {
			Redirection_Admin::update();
			Red_Flusher::schedule();
			red_set_options();
		}
	}

	// These are only called on the single standard site, or in the network admin of the multisite - they run across all available sites
	public static function plugin_deactivated() {
		if ( is_network_admin() ) {
			foreach ( get_sites() as $site ) {
				switch_to_blog( $site->blog_id );

				Red_Flusher::clear();

				restore_current_blog();
			}
		} else {
			Red_Flusher::clear();
		}
	}

	// These are only called on the single standard site, or in the network admin of the multisite - they run across all available sites
	public static function plugin_uninstall() {
		include_once dirname( REDIRECTION_FILE ).'/models/database.php';

		$db = new RE_Database();

		if ( is_network_admin() ) {
			foreach ( get_sites() as $site ) {
				switch_to_blog( $site->blog_id );

				$db->remove( REDIRECTION_FILE );

				restore_current_blog();
			}
		} else {
			$db->remove( REDIRECTION_FILE );
		}
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

			$database->upgrade( $version, REDIRECTION_DB_VERSION );
		}
	}

	// So it finally came to this... some plugins include their JS in all pages, whether they are needed or not. If there is an error
	// then this can prevent Redirection running, it's a little sensitive about that. We use the nuclear option here to disable
	// all other JS while viewing Redirection
	public function flying_solo( $src, $handle ) {
		if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], 'page=redirection.php' ) !== false ) {
			if ( substr( $src, 0, 4 ) === 'http' && $handle !== 'redirection' && strpos( $src, 'plugins' ) !== false ) {
				if ( $this->ignore_this_plugin( $src ) ) {
					return false;
				}
			}
		}

		return $src;
	}

	private function ignore_this_plugin( $src ) {
		if ( strpos( $src, 'mootools' ) !== false ) {
			return true;
		}

		if ( strpos( $src, 'wp-seo-' ) !== false ) {
			return true;
		}

		return false;
	}

	public function flush_schedule( $options ) {
		Red_Flusher::schedule();
		return $options;
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
		$versions = array(
			'Plugin: '.REDIRECTION_VERSION,
			'WordPress: '.$wp_version,
			'PHP: '.phpversion(),
			'Browser: '.Redirection_Request::get_user_agent(),
		);

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
			'versions' => implode( "\n", $versions ),
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
		$hook = add_management_page( 'Redirection', 'Redirection', apply_filters( 'redirection_role', 'administrator' ), basename( REDIRECTION_FILE ), array( &$this, 'admin_screen' ) );
		add_action( 'load-'.$hook, array( $this, 'redirection_head' ) );
	}

	private function check_minimum_wp() {
		$wp_version = get_bloginfo( 'version' );

		if ( version_compare( $wp_version, REDIRECTION_MIN_WP, '<' ) ) {
?>
	<div class="react-error">
		<h1><?php _e( 'Unable to load Redirection', 'redirection' ); ?></h1>
		<p style="text-align: left"><?php printf( __( 'Redirection requires WordPress v%1s, you are using v%2s - please update your WordPress', 'redirection' ), REDIRECTION_MIN_WP, $wp_version ); ?></p>
	</div>
<?php
			return false;
		}

		return true;
	}

	private function check_tables_exist() {
		include_once dirname( REDIRECTION_FILE ).'/models/database.php';

		$database = new RE_Database();
		$status = $database->get_status();

		if ( $status['status'] !== 'good' ) {
			?>
				<div class="error">
					<h3><?php _e( 'Redirection not installed properly', 'redirection' ); ?></h3>
					<p style="text-align: left"><?php printf( __( 'Problems were detected with your database tables. Please visit the <a href="%s">support page</a> for more details.', 'redirection' ), 'tools.php?page=redirection.php&amp;sub=support' ); ?></p>
				</div>
			<?php

			return false;
		}

		return true;
	}

	function admin_screen() {
		$version = get_plugin_data( REDIRECTION_FILE );
		$version = $version['Version'];

	  	Redirection_Admin::update();

		if ( $this->check_minimum_wp() === false ) {
			return;
		}

		if ( $this->check_tables_exist() === false && ( ! isset( $_GET['sub'] ) || $_GET['sub'] !== 'support' ) ) {
			return false;
		}
?>
<div id="react-ui">
	<div class="react-loading">
		<h1><?php _e( 'Loading, please wait...', 'redirection' ); ?></h1>

		<span class="react-loading-spinner"></span>
	</div>
	<noscript>Please enable JavaScript</noscript>

	<div class="react-error" style="display: none">
		<h1><?php _e( 'Unable to load Redirection', 'redirection' ); ?> v<?php echo esc_html( $version ); ?></h1>
		<p><?php _e( "This may be caused by another plugin - look at your browser's error console for more details.", 'redirection' ); ?></p>
		<p><?php _e( 'If you are using a page caching plugin or service (CloudFlare, OVH, etc) then you can also try clearing that cache.', 'redirection' ); ?></p>
		<p><?php _e( 'Also check if your browser is able to load <code>redirection.js</code>:', 'redirection' ); ?></p>
		<p><code><?php echo esc_html( plugin_dir_url( REDIRECTION_FILE ).'redirection.js?ver='.urlencode( REDIRECTION_VERSION ).'-'.urlencode( REDIRECTION_BUILD ) ); ?></code></p>
		<p><?php _e( "If you think Redirection is at fault then create an issue.", 'redirection' ); ?></p>
		<p class="versions"><?php _e( '<code>Redirectioni10n</code> is not defined. This usually means another plugin is blocking Redirection from loading. Please disable all plugins and try again.', 'redirection' ); ?></p>
		<p>
			<a class="button-primary" target="_blank" href="https://github.com/johngodley/redirection/issues/new?title=Problem%20starting%20Redirection%20<?php echo esc_attr( $version ) ?>">
				<?php _e( 'Create Issue', 'redirection' ); ?>
			</a>
		</p>
	</div>
</div>

<script>
	var prevError = window.onerror;
	var errors = [];
	var timeout = 0;
	var timer = setInterval( function() {
		if ( isRedirectionLoaded() ) {
			resetAll();
		} else if ( errors.length > 0 || timeout++ === 5 ) {
			showError();
		}
	}, 5000 );

	function isRedirectionLoaded() {
		return typeof redirection !== 'undefined';
	}

	function showError() {
		var errorText = "";

		if ( errors.length > 0 ) {
			errorText = "```\n" + errors.join( ',' ) + "\n```\n\n";
		}

		resetAll();
		document.querySelector( '.react-loading' ).style.display = 'none';
		document.querySelector( '.react-error' ).style.display = 'block';

		if ( typeof Redirectioni10n !== 'undefined' ) {
			document.querySelector( '.versions' ).innerHTML = Redirectioni10n.versions.replace( /\n/g, '<br />' );
			document.querySelector( '.react-error .button-primary' ).href += '&body=' + encodeURIComponent( errorText ) + encodeURIComponent( Redirectioni10n.versions );
		}
	}

	function resetAll() {
		clearInterval( timer );
		window.onerror = prevError;
	}

	window.onerror = function( error, url, line ) {
		console.error( error );
		errors.push( error + ' ' + url + ' ' + line );
	};
</script>
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
		if ( $this->user_has_access() && $_GET['sub'] === 'io' && isset( $_GET['exporter'] ) && isset( $_GET['export'] ) ) {
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
