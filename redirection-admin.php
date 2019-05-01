<?php

include_once dirname( __FILE__ ) . '/models/group.php';
include_once dirname( __FILE__ ) . '/models/monitor.php';
include_once dirname( __FILE__ ) . '/models/file-io.php';
include_once dirname( __FILE__ ) . '/database/database.php';

define( 'RED_DEFAULT_PER_PAGE', 25 );
define( 'RED_MAX_PER_PAGE', 200 );

class Redirection_Admin {
	private static $instance = null;
	private $monitor;
	private $fixit_failed = false;

	static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Redirection_Admin();
		}

		return self::$instance;
	}

	function __construct() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_notices', [ $this, 'update_nag' ] );
		// add_action( 'network_admin_notices', [ $this, 'update_nag' ] );
		add_action( 'plugin_action_links_' . basename( dirname( REDIRECTION_FILE ) ) . '/' . basename( REDIRECTION_FILE ), [ $this, 'plugin_settings' ], 10, 4 );
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 4 );
		add_filter( 'redirection_save_options', [ $this, 'flush_schedule' ] );
		add_filter( 'set-screen-option', [ $this, 'set_per_page' ], 10, 3 );
		add_action( 'redirection_redirect_updated', [ $this, 'set_default_group' ], 10, 2 );

		if ( defined( 'REDIRECTION_FLYING_SOLO' ) && REDIRECTION_FLYING_SOLO ) {
			add_filter( 'script_loader_src', [ $this, 'flying_solo' ], 10, 2 );
		}

		register_deactivation_hook( REDIRECTION_FILE, [ 'Redirection_Admin', 'plugin_deactivated' ] );
		register_uninstall_hook( REDIRECTION_FILE, [ 'Redirection_Admin', 'plugin_uninstall' ] );

		$this->monitor = new Red_Monitor( red_get_options() );
		$this->run_hacks();
	}

	// These are only called on the single standard site, or in the network admin of the multisite - they run across all available sites
	public static function plugin_activated() {
		Red_Database::apply_to_sites( function() {
			Red_Flusher::clear();
			red_set_options();
		} );
	}

	// These are only called on the single standard site, or in the network admin of the multisite - they run across all available sites
	public static function plugin_deactivated() {
		Red_Database::apply_to_sites( function() {
			Red_Flusher::clear();
		} );
	}

	// These are only called on the single standard site, or in the network admin of the multisite - they run across all available sites
	public static function plugin_uninstall() {
		$database = Red_Database::get_latest_database();

		Red_Database::apply_to_sites( function() use ( $database ) {
			$database->remove();
		} );
	}

	public function update_nag() {
		if ( ! $this->user_has_access() ) {
			return;
		}

		$status = new Red_Database_Status();

		$message = false;
		if ( $status->needs_installing() ) {
			/* translators: 1: URL to plugin page */
			$message = sprintf( __( 'Please complete your <a href="%s">Redirection setup</a> to activate the plugin.' ), 'tools.php?page=' . basename( REDIRECTION_FILE ) );
		} elseif ( $status->needs_updating() ) {
			/* translators: 1: URL to plugin page, 2: current version, 3: target version */
			$message = sprintf( __( 'Redirection\'s database needs to be updated - <a href="%1$1s">click to update</a>.' ), 'tools.php?page=' . basename( REDIRECTION_FILE ) );
		}

		if ( ! $message || strpos( Redirection_Request::get_request_url(), 'page=redirection.php' ) !== false ) {
			return;
		}

		// Contains HTML
		echo '<div class="update-nag">' . $message . '</div>';
	}

	// So it finally came to this... some plugins include their JS in all pages, whether they are needed or not. If there is an error
	// then this can prevent Redirection running and it's a little sensitive about that. We use the nuclear option here to disable
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
		$ignore = array(
			'mootools',
			'wp-seo-',
			'authenticate',
			'wordpress-seo',
			'yikes',
		);

		foreach ( $ignore as $text ) {
			if ( strpos( $src, $text ) !== false ) {
				return true;
			}
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
		$status = new Red_Database_Status();
		if ( $status->needs_updating() ) {
			array_unshift( $links, '<a style="color: red" href="tools.php?page=' . basename( REDIRECTION_FILE ) . '&amp;sub=support">' . __( 'Upgrade Database', 'redirection' ) . '</a>' );
		}

		array_unshift( $links, '<a href="tools.php?page=' . basename( REDIRECTION_FILE ) . '&amp;sub=options">' . __( 'Settings', 'redirection' ) . '</a>' );
		return $links;
	}

	function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
		if ( $plugin_file === basename( dirname( REDIRECTION_FILE ) ) . '/' . basename( REDIRECTION_FILE ) ) {
			$plugin_data['Description'] .= '<p>' . __( 'Please upgrade your database', 'redirection' ) . '</p>';
		}

		return $plugin_meta;
	}

	function redirection_head() {
		global $wp_version;

		if ( isset( $_REQUEST['action'] ) && isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'wp_rest' ) ) {
			if ( $_REQUEST['action'] === 'fixit' ) {
				$this->run_fixit();
			} elseif ( $_REQUEST['action'] === 'rest_api' ) {
				$this->set_rest_api( intval( $_REQUEST['rest_api'], 10 ) );
			}
		}

		$build = REDIRECTION_VERSION . '-' . REDIRECTION_BUILD;
		$preload = $this->get_preload_data();
		$options = red_get_options();
		$versions = array(
			'Plugin: ' . REDIRECTION_VERSION,
			'WordPress: ' . $wp_version . ' (' . ( is_multisite() ? 'multi' : 'single' ) . ')',
			'PHP: ' . phpversion(),
			'Browser: ' . Redirection_Request::get_user_agent(),
			'JavaScript: ' . plugin_dir_url( REDIRECTION_FILE ) . 'redirection.js',
			'REST API: ' . red_get_rest_api(),
		);

		$this->inject();

		if ( in_array( $this->get_menu_page(), array( 'redirects', 'log', '404s', 'groups' ) ) ) {
			add_screen_option( 'per_page', array(
				/* translators: maximum number of log entries */
				'label' => sprintf( __( 'Log entries (%d max)', 'redirection' ), RED_MAX_PER_PAGE ),
				'default' => RED_DEFAULT_PER_PAGE,
				'option' => 'redirection_log_per_page',
			) );
		}

		if ( defined( 'REDIRECTION_DEV_MODE' ) && REDIRECTION_DEV_MODE ) {
			wp_enqueue_script( 'redirection', 'http://localhost:3312/redirection.js', array(), $build, true );
		} else {
			wp_enqueue_script( 'redirection', plugin_dir_url( REDIRECTION_FILE ) . 'redirection.js', array(), $build, true );
		}

		wp_enqueue_style( 'redirection', plugin_dir_url( REDIRECTION_FILE ) . 'redirection.css', array(), $build );

		$status = new Red_Database_Status();
		$status->check_tables_exist();

		wp_localize_script( 'redirection', 'Redirectioni10n', array(
			'api' => [
				'WP_API_root' => esc_url_raw( red_get_rest_api() ),
				'WP_API_nonce' => wp_create_nonce( 'wp_rest' ),
				'current' => $options['rest_api'],
				'routes' => [
					REDIRECTION_API_JSON => red_get_rest_api( REDIRECTION_API_JSON ),
					REDIRECTION_API_JSON_INDEX => red_get_rest_api( REDIRECTION_API_JSON_INDEX ),
					REDIRECTION_API_JSON_RELATIVE => red_get_rest_api( REDIRECTION_API_JSON_RELATIVE ),
				],
			],
			'pluginBaseUrl' => plugins_url( '', REDIRECTION_FILE ),
			'pluginRoot' => admin_url( 'tools.php?page=redirection.php' ),
			'per_page' => $this->get_per_page(),
			'locale' => $this->get_i18n_data(),
			'localeSlug' => get_locale(),
			'settings' => $options,
			'preload' => $preload,
			'versions' => implode( "\n", $versions ),
			'version' => REDIRECTION_VERSION,
			'database' => $status->get_json(),
		) );

		$this->add_help_tab();
	}

	// Some plugins misbehave, so this attempts to 'fix' them so Redirection can get on with it's work
	private function run_hacks() {
		add_filter( 'ip-geo-block-admin', array( $this, 'ip_geo_block' ) );
	}

	/**
	 * This works around the IP Geo Block plugin being very aggressive and breaking Redirection
	 */
	public function ip_geo_block( $validate ) {
		$url = Redirection_Request::get_request_url();
		$override = array(
			'tools.php?page=redirection.php',
			'action=red_proxy&rest_path=redirection',
		);

		foreach ( $override as $path ) {
			if ( strpos( $url, $path ) !== false ) {
				return array(
					'result' => 'passed',
					'auth' => false,
					'asn' => false,
					'code' => false,
					'ip' => false,
				);
			}
		}

		return $validate;
	}

	private function run_fixit() {
		if ( $this->user_has_access() ) {
			include_once dirname( REDIRECTION_FILE ) . '/models/fixer.php';

			$fixer = new Red_Fixer();
			$result = $fixer->fix( $fixer->get_status() );

			if ( is_wp_error( $result ) ) {
				$this->fixit_failed = $result;
			}
		}
	}

	private function set_rest_api( $api ) {
		if ( $api >= 0 && $api <= REDIRECTION_API_JSON_RELATIVE ) {
			red_set_options( array( 'rest_api' => intval( $api, 10 ) ) );
		}
	}

	private function get_preload_data() {
		if ( $this->get_menu_page() === 'support' ) {
			include_once dirname( REDIRECTION_FILE ) . '/models/fixer.php';

			$fixer = new Red_Fixer();

			return array(
				'pluginStatus' => $fixer->get_json(),
			);
		}

		return array();
	}

	private function add_help_tab() {
		/* translators: URL */
		$content = sprintf( __( 'You can find full documentation about using Redirection on the <a href="%s" target="_blank">redirection.me</a> support site.', 'redirection' ), 'https://redirection.me/support/?utm_source=redirection&utm_medium=plugin&utm_campaign=context-help' );
		$title = __( 'Redirection Support', 'redirection' );

		$current_screen = get_current_screen();
		$current_screen->add_help_tab( array(
			'id'        => 'redirection',
			'title'     => 'Redirection',
			'content'   => "<h2>$title</h2><p>$content</p>",
		) );
	}

	private function get_per_page() {
		$per_page = intval( get_user_meta( get_current_user_id(), 'redirection_log_per_page', true ), 10 );

		return $per_page > 0 ? max( 5, min( $per_page, RED_MAX_PER_PAGE ) ) : RED_DEFAULT_PER_PAGE;
	}

	private function get_i18n_data() {
		$locale = get_locale();

		// WP 4.7
		if ( function_exists( 'get_user_locale' ) ) {
			$locale = get_user_locale();
		}

		$i18n_json = dirname( REDIRECTION_FILE ) . '/locale/json/redirection-' . $locale . '.json';

		if ( is_file( $i18n_json ) && is_readable( $i18n_json ) ) {
			$locale_data = @file_get_contents( $i18n_json );

			if ( $locale_data ) {
				return json_decode( $locale_data );
			}
		}

		// Return empty if we have nothing to return so it doesn't fail when parsed in JS
		return array();
	}

	public function admin_menu() {
		$hook = add_management_page( 'Redirection', 'Redirection', $this->get_access_role(), basename( REDIRECTION_FILE ), [ $this, 'admin_screen' ] );
		add_action( 'load-' . $hook, [ $this, 'redirection_head' ] );
	}

	public function get_access_role() {
		return apply_filters( 'redirection_role', 'manage_options' );
	}

	private function check_minimum_wp() {
		$wp_version = get_bloginfo( 'version' );

		if ( version_compare( $wp_version, REDIRECTION_MIN_WP, '<' ) ) {
			return false;
		}

		return true;
	}

	public function set_default_group( $id, $redirect ) {
		red_set_options( array( 'last_group_id' => $redirect->get_group_id() ) );
	}

	public function admin_screen() {
		if ( ! $this->user_has_access() ) {
			die( 'You do not have sufficient permissions to access this page.' );
		}

		if ( $this->check_minimum_wp() === false ) {
			return $this->show_minimum_wordpress();
		}

		if ( $this->fixit_failed ) {
			$this->show_fixit_failed();
		}

		Red_Flusher::schedule();

		$this->show_main();
	}

	private function show_fixit_failed() {
		?>
		<div class="notice notice-error">
			<h1><?php echo esc_html( $this->fixit_failed->get_error_message() ); ?></h1>
			<p><?php echo esc_html( $this->fixit_failed->get_error_data() ); ?></p>
		</div>
		<?php
	}

	private function show_minimum_wordpress() {
		/* translators: 1: Expected WordPress version, 2: Actual WordPress version */
		$wp_requirement = sprintf( __( 'Redirection requires WordPress v%1$1s, you are using v%2$2s - please update your WordPress', 'redirection' ), REDIRECTION_MIN_WP, $wp_version );
		?>
	<div class="react-error">
		<h1><?php esc_html_e( 'Unable to load Redirection', 'redirection' ); ?></h1>
		<p><?php echo esc_html( $wp_requirement ); ?></p>
	</div>
		<?php
	}

	private function show_load_fail() {
		?>
	<div class="react-error" style="display: none">
		<h1><?php esc_html_e( 'Unable to load Redirection ☹️', 'redirection' ); ?> v<?php echo esc_html( REDIRECTION_VERSION ); ?></h1>
		<p><?php esc_html_e( "This may be caused by another plugin - look at your browser's error console for more details.", 'redirection' ); ?></p>
		<p><?php esc_html_e( 'If you are using a page caching plugin or service (CloudFlare, OVH, etc) then you can also try clearing that cache.', 'redirection' ); ?></p>
		<p><?php _e( 'Also check if your browser is able to load <code>redirection.js</code>:', 'redirection' ); ?></p>
		<p><code><?php echo esc_html( plugin_dir_url( REDIRECTION_FILE ) . 'redirection.js?ver=' . urlencode( REDIRECTION_VERSION ) . '-' . urlencode( REDIRECTION_BUILD ) ); ?></code></p>
		<p><?php esc_html_e( 'Please note that Redirection requires the WordPress REST API to be enabled. If you have disabled this then you won\'t be able to use Redirection', 'redirection' ); ?></p>
		<p><?php _e( 'Please see the <a href="https://redirection.me/support/problems/">list of common problems</a>.', 'redirection' ); ?></p>
		<p><?php esc_html_e( 'If you think Redirection is at fault then create an issue.', 'redirection' ); ?></p>
		<p class="versions"><?php _e( '<code>Redirectioni10n</code> is not defined. This usually means another plugin is blocking Redirection from loading. Please disable all plugins and try again.', 'redirection' ); ?></p>
		<p>
			<a class="button-primary" target="_blank" href="https://github.com/johngodley/redirection/issues/new?title=Problem%20starting%20Redirection%20<?php echo esc_attr( REDIRECTION_VERSION ); ?>">
				<?php esc_html_e( 'Create Issue', 'redirection' ); ?>
			</a>
		</p>
	</div>
		<?php
	}

	private function show_main() {
		?>
	<div id="react-modal"></div>
	<div id="react-ui">
		<div class="react-loading">
			<h1><?php esc_html_e( 'Loading, please wait...', 'redirection' ); ?></h1>

			<span class="react-loading-spinner"></span>
		</div>
		<noscript><?php esc_html_e( 'Please enable JavaScript', 'redirection' ); ?></noscript>

		<?php $this->show_load_fail(); ?>
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
		return current_user_can( $this->get_access_role() );
	}

	private function inject() {
		if ( isset( $_GET['page'] ) && $this->get_menu_page() !== 'redirects' && $_GET['page'] === 'redirection.php' ) {
			$this->try_export_logs();
			$this->try_export_redirects();
			$this->try_export_rss();
		}
	}

	private function get_menu_page() {
		if ( isset( $_GET['sub'] ) && in_array( $_GET['sub'], array( 'group', '404s', 'log', 'io', 'options', 'support', true ) ) ) {
			return $_GET['sub'];
		}

		return 'redirects';
	}

	public function try_export_rss() {
		if ( isset( $_GET['token'] ) && $this->get_menu_page() === 'rss' ) {
			$options = red_get_options();

			if ( $_GET['token'] === $options['token'] && ! empty( $options['token'] ) ) {
				$items = Red_Item::get_all_for_module( intval( $_GET['module'] ) );

				$exporter = Red_FileIO::create( 'rss' );
				$exporter->force_download();
				echo $exporter->get_data( $items, array() );
				die();
			}
		}
	}

	private function try_export_logs() {
		if ( $this->user_has_access() && isset( $_POST['export-csv'] ) && check_admin_referer( 'wp_rest' ) ) {
			if ( $this->get_menu_page() === 'log' ) {
				RE_Log::export_to_csv();
			} else {
				RE_404::export_to_csv();
			}

			die();
		}
	}

	private function try_export_redirects() {
		if ( $this->user_has_access() && $_GET['sub'] === 'io' && isset( $_GET['exporter'] ) && isset( $_GET['export'] ) && check_admin_referer( 'wp_rest' ) ) {
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
