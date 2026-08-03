<?php

namespace Redirection\Plugin;

use Redirection\Database\Database;
use Redirection\Database\Status as DatabaseStatus;
use Redirection\ImportExport\ExportService;
use Redirection\ImportExport\FormatFactory;
use Redirection\ImportExport\Importer\PluginRegistry;
use Redirection\Log\Error as ErrorLog;
use Redirection\Log\Log;
use Redirection\Log\Redirect as RedirectLog;
use Redirection\Redirect\Redirect;
use Redirection\Request\Request;
use Redirection\Core\Fixer;
use Redirection\Core\Capabilities;
use Redirection\Settings\Settings;
use WP_Error;

class Admin {
	/**
	 * @var Admin|null
	 */
	private static $instance = null;

	/**
	 * @var Monitor|null
	 * @phpstan-ignore property.onlyWritten
	 */
	private $monitor;

	/**
	 * @var WP_Error|false
	 */
	private $fixit_failed = false;

	/**
	 * @return Admin|null
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Admin();
		}

		return self::$instance;
	}

	/**
	 * Reset singleton instance (for testing only)
	 *
	 * @return void
	 */
	public static function reset(): void {
		self::$instance = null;
	}

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_notices', [ $this, 'update_nag' ] );
		add_filter( 'plugin_action_links_' . basename( dirname( REDIRECTION_FILE ) ) . '/' . basename( REDIRECTION_FILE ), [ $this, 'plugin_settings' ] );
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 4 );
		add_filter( 'redirection_save_options', [ $this, 'flush_schedule' ] );
		add_filter( 'set-screen-option', [ $this, 'set_per_page' ], 10, 3 );
		add_filter(
			'set_screen_option_redirection_log_per_page',
			fn( $ignore, $option, $value ) => $value,
			10,
			3
		);
		add_action( 'redirection_redirect_updated', [ $this, 'set_default_group' ], 10, 2 );
		add_action( 'redirection_redirect_updated', [ $this, 'clear_cache' ] );

		if ( defined( 'REDIRECTION_FLYING_SOLO' ) && REDIRECTION_FLYING_SOLO ) {
			add_filter( 'script_loader_src', [ $this, 'flying_solo' ], 10, 2 );
		}

		register_deactivation_hook( REDIRECTION_FILE, [ self::class, 'plugin_deactivated' ] );
		register_uninstall_hook( REDIRECTION_FILE, [ self::class, 'plugin_uninstall' ] );

		$this->monitor = new Monitor( Settings::get() );
		$this->run_hacks();
	}

	/**
	 * These are only called on the single standard site, or in the network admin of the multisite - they run across all available sites
	 *
	 * @return void
	 */
	public static function plugin_activated() {
		Database::apply_to_sites(
			function () {
				Flusher::clear();
				Settings::save( [] );
			}
		);
	}

	/**
	 * These are only called on the single standard site, or in the network admin of the multisite - they run across all available sites
	 *
	 * @return void
	 */
	public static function plugin_deactivated() {
		Database::apply_to_sites(
			function () {
				Flusher::clear();
			}
		);
	}

	/**
	 * These are only called on the single standard site, or in the network admin of the multisite - they run across all available sites
	 *
	 * @return void
	 */
	public static function plugin_uninstall() {
		$database = Database::get_latest_database();

		Database::apply_to_sites(
			function () use ( $database ) {
				$database->remove();
			}
		);
	}

	/**
	 * Show the database upgrade nag
	 *
	 * @return void
	 */
	public function update_nag() {
		$options = Settings::get();

		// Is the site configured to upgrade automatically?
		if ( $options['plugin_update'] === 'admin' ) {
			$this->automatic_upgrade();
			return;
		}

		// Can the user perform a manual database upgrade?
		if ( ! Capabilities::has_access( Capabilities::CAP_OPTION_MANAGE ) ) {
			return;
		}

		// Default manual update, with nag
		$status = new DatabaseStatus();

		$message = false;
		if ( $status->needs_installing() ) {
			/* translators: 1: URL to plugin page */
			$message = sprintf( __( 'Please complete your <a href="%s">Redirection setup</a> to activate the plugin.', 'redirection' ), esc_url( $this->get_plugin_url() ) );
		} elseif ( $status->needs_updating() ) {
			/* translators: 1: URL to plugin page, 2: current version, 3: target version */
			$message = sprintf( __( 'Redirection\'s database needs to be updated - <a href="%1$1s">click to update</a>.', 'redirection' ), esc_url( $this->get_plugin_url() ) );
		}

		if ( $message === false || strpos( Request::get_request_url(), 'page=redirection.php' ) !== false ) {
			return;
		}

		// Known HTML and so isn't escaped
		// phpcs:ignore
		echo '<div class="update-nag notice notice-warning" style="width: 95%">' . $message . '</div>';
	}

	/**
	 * So it finally came to this... some plugins include their JS in all pages, whether they are needed or not. If there is an error
	 * then this can prevent Redirection running and it's a little sensitive about that. We use the nuclear option here to disable
	 * all other JS while viewing Redirection
	 *
	 * @param string $src
	 * @param string $handle
	 * @return string|false
	 */
	public function flying_solo( $src, $handle ) {
		$request = Request::get_request_url();

		if ( strpos( $request, 'page=redirection.php' ) !== false ) {
			if ( substr( $src, 0, 4 ) === 'http' && $handle !== 'redirection' && strpos( $src, 'plugins' ) !== false ) {
				if ( $this->ignore_this_plugin( $src ) ) {
					return false;
				}
			}
		}

		return $src;
	}

	/**
	 * @param string $src
	 * @return bool
	 */
	private function ignore_this_plugin( $src ) {
		$ignore = [
			'mootools',
			'wp-seo-',
			'authenticate',
			'wordpress-seo',
			'yikes',
		];

		foreach ( $ignore as $text ) {
			if ( strpos( $src, $text ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Show incomplete installation error
	 *
	 * @return void
	 */
	public function show_incomplete_installation_notice() {
		?>
		<div class="notice notice-error">
			<p>
				<strong><?php esc_html_e( 'Redirection Error: Incomplete Installation Detected', 'redirection' ); ?></strong>
			</p>
			<p>
				<?php esc_html_e( 'Redirection has detected that required files are missing or were not properly loaded. This likely means your cache needs clearing.', 'redirection' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'Please try clearing your cache and reloading the page.', 'redirection' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Perform an automatic DB upgrade
	 *
	 * @return void
	 */
	private function automatic_upgrade() {
		$loop = 0;
		$status = new DatabaseStatus();
		$database = new Database();

		// Loop until the DB is upgraded, or until a max is exceeded (just in case)
		while ( $loop < 20 ) {
			if ( ! $status->needs_updating() ) {
				break;
			}

			$database->apply_upgrade( $status );

			if ( $status->is_error() ) {
				// If an error occurs then switch to 'prompt' mode and let the user deal with it.
				Settings::save( [ 'plugin_update' => 'prompt' ] );
				return;
			}

			$loop++;
		}
	}

	/**
	 * @param mixed $options
	 * @return mixed
	 */
	public function flush_schedule( $options ) {
		Flusher::schedule();
		return $options;
	}

	/**
	 * @param mixed $status
	 * @param mixed $option
	 * @param mixed $value
	 * @return mixed
	 */
	public function set_per_page( $status, $option, $value ) {
		if ( $option === 'redirection_log_per_page' ) {
			$value = max( 1, min( intval( $value, 10 ), Log::MAX_PER_PAGE ) );
			return $value;
		}

		return $status;
	}

	/**
	 * @param array<int|string, string> $links
	 * @return array<int|string, string>
	 */
	public function plugin_settings( array $links ): array {
		$status = new DatabaseStatus();
		if ( $status->needs_updating() ) {
			array_unshift( $links, '<a style="color: red" href="' . esc_url( $this->get_plugin_url() ) . '&amp;sub=support">' . __( 'Upgrade Database', 'redirection' ) . '</a>' );
		}

		array_unshift( $links, '<a href="' . esc_url( $this->get_plugin_url() ) . '&amp;sub=options">' . __( 'Settings', 'redirection' ) . '</a>' );
		return $links;
	}

	/**
	 * @param mixed $plugin_meta
	 * @param mixed $plugin_file
	 * @param mixed $plugin_data
	 * @param mixed $status
	 * @return mixed
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
		if ( $plugin_file === basename( dirname( REDIRECTION_FILE ) ) . '/' . basename( REDIRECTION_FILE ) ) {
			$plugin_data['Description'] .= '<p>' . __( 'Please upgrade your database', 'redirection' ) . '</p>';
		}

		return $plugin_meta;
	}

	/**
	 * @return string
	 */
	private function get_plugin_url() {
		return admin_url( 'tools.php?page=' . basename( REDIRECTION_FILE ) );
	}

	/**
	 * @return string
	 */
	private function get_first_available_page_url() {
		$pages = Capabilities::get_available_pages();

		if ( count( $pages ) > 0 ) {
			return $this->get_plugin_url() . ( $pages[0] === 'redirect' ? '' : '&sub=' . rawurlencode( $pages[0] ) );
		}

		return admin_url();
	}

	/**
	 * @param mixed $name
	 * @return string|null
	 */
	private function get_query( $name ) {
		if ( isset( $_GET[ $name ] ) ) {
			return sanitize_text_field( $_GET[ $name ] );
		}

		return null;
	}

	/**
	 * @return void
	 */
	public function redirection_head() {
		global $wp_version;

		nocache_headers();

		// Does user have access to this page?
		if ( $this->get_current_page() === false ) {
			// Redirect to root plugin page
			wp_safe_redirect( $this->get_first_available_page_url() );
			die();
		}

		if ( isset( $_REQUEST['action'] ) && isset( $_REQUEST['_wpnonce'] ) && is_string( $_REQUEST['action'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'wp_rest' ) !== false ) {
			$action = sanitize_text_field( $_REQUEST['action'] );

			if ( $action === 'fixit' ) {
				$this->run_fixit();
			} elseif ( $action === 'rest_api' && isset( $_REQUEST['rest_api'] ) && is_string( $_REQUEST['rest_api'] ) && Capabilities::has_access( Capabilities::CAP_OPTION_MANAGE ) ) {
				$this->set_rest_api( intval( $_REQUEST['rest_api'], 10 ) );
			}
		}

		$build = REDIRECTION_VERSION;
		$preload = $this->get_preload_data();
		$options = Settings::get();
		$versions = [
			'Plugin: ' . REDIRECTION_VERSION . ' ' . REDIRECTION_DB_VERSION,
			'WordPress: ' . $wp_version . ' (' . ( is_multisite() ? 'multi' : 'single' ) . ')',
			'PHP: ' . phpversion(),
			'Browser: ' . Request::get_user_agent(),
			'JavaScript: ' . plugin_dir_url( REDIRECTION_FILE ) . 'build/redirection.js?ver=' . $build,
			'REST API: ' . red_get_rest_api(),
		];

		$this->inject();

		// Add contextual help to some pages
		if ( in_array( $this->get_current_page(), [ 'redirect', 'log', '404s', 'groups' ], true ) ) {
			add_screen_option(
				'per_page',
				[
					/* translators: maximum number of log entries */
					'label' => sprintf( __( 'Log entries (%d max)', 'redirection' ), Log::MAX_PER_PAGE ),
					'default' => Log::DEFAULT_PER_PAGE,
					'option' => 'redirection_log_per_page',
				]
			);
		}

		$assets = include plugin_dir_path( REDIRECTION_FILE ) . 'build/redirection.asset.php';
		$dependencies = $assets['dependencies'];
		$version = $assets['version'];

		wp_enqueue_script( 'redirection', plugin_dir_url( REDIRECTION_FILE ) . 'build/redirection.js', $dependencies, $version, true );
		wp_enqueue_style( 'redirection', plugin_dir_url( REDIRECTION_FILE ) . 'build/redirection.css', [], $version );

		$is_new = false;
		$major_version = implode( '.', array_slice( explode( '.', REDIRECTION_VERSION ), 0, 2 ) );

		if ( $this->get_query( 'page' ) === 'redirection.php' && strpos( REDIRECTION_VERSION, '-beta' ) === false ) {
			$is_new = version_compare( (string) $options['update_notice'], $major_version ) < 0;
		}

		$status = new DatabaseStatus();
		$status->check_tables_exist();

		// Fix some sites having a version set to +OK - not sure why
		if ( $options['database'] === '+OK' ) {
			Settings::save( [ 'database' => REDIRECTION_DB_VERSION ] );
			$status->stop_update();
		}

		wp_localize_script(
			'redirection',
			'Redirectioni10n',
			[
				'api' => [
					'WP_API_root' => esc_url_raw( red_get_rest_api() ),
					'WP_API_nonce' => wp_create_nonce( 'wp_rest' ),
					'site_health' => admin_url( 'site-health.php' ),
					'current' => $options['rest_api'],
					'routes' => [
						Settings::API_JSON => red_get_rest_api( Settings::API_JSON ),
						Settings::API_JSON_INDEX => red_get_rest_api( Settings::API_JSON_INDEX ),
						Settings::API_JSON_RELATIVE => red_get_rest_api( Settings::API_JSON_RELATIVE ),
					],
				],
				'pluginBaseUrl' => plugins_url( '', REDIRECTION_FILE ),
				'pluginRoot' => $this->get_plugin_url(),
				'per_page' => $this->get_per_page(),
				'locale' => implode( '-', array_slice( explode( '-', str_replace( '_', '-', get_locale() ) ), 0, 2 ) ),
				'settings' => $options,
				'preload' => $preload,
				'versions' => implode( "\n", $versions ),
				'version' => REDIRECTION_VERSION,
				'database' => $status->get_json(),
				'caps' => [
					'pages' => Capabilities::get_available_pages(),
					'capabilities' => Capabilities::get_all_capabilities(),
				],
				'update_notice' => $is_new ? $major_version : false,
			]
		);

		wp_set_script_translations( 'redirection', 'redirection' );

		$this->add_help_tab();
	}

	/**
	 * Some plugins misbehave, so this attempts to 'fix' them so Redirection can get on with it's work
	 *
	 * @return void
	 */
	private function run_hacks() {
		add_filter( 'option_rank_math_notifications', [ $this, 'rank_math_notifications' ] );
		add_filter( 'qtranslate_language_detect_redirect', [ $this, 'qtranslate_language_detect_redirect' ], 10, 2 );
	}

	/**
	 * Really wish this wasn't necessary, but some plugins aggressively mis-represent a problem and add a notice to every
	 * admin page. Here we remove that notice so people don't contact Redirection support for a problem caused by another plugin.
	 *
	 * @param mixed $notifications
	 * @return mixed
	 */
	public function rank_math_notifications( $notifications ) {
		if ( ! is_admin() || ! is_array( $notifications ) ) {
			return $notifications;
		}

		return array_values(
			array_filter(
				$notifications,
				static function ( $notification ) {
					if ( ! is_array( $notification ) || ! isset( $notification['options'] ) || ! is_array( $notification['options'] ) ) {
						return true;
					}

					return ! isset( $notification['options']['id'] ) || $notification['options']['id'] !== 'conflicting_redirections_plugins';
				}
			)
		);
	}

	/**
	 * This is causing a lot of problems with the REST API - disable qTranslate
	 *
	 * @param mixed $lang
	 * @param mixed $url
	 * @return mixed
	 */
	public function qtranslate_language_detect_redirect( $lang, $url ) {
		$url = Request::get_request_url();

		if ( strpos( $url, '/wp-json/' ) !== false || strpos( $url, '?rest_route' ) !== false ) {
			return false;
		}

		return $lang;
	}

	/**
	 * @return void
	 */
	private function run_fixit() {
		if ( Capabilities::has_access( Capabilities::CAP_SUPPORT_MANAGE ) ) {
			$fixer = new Fixer();
			$result = $fixer->fix( $fixer->get_status() );

			if ( is_wp_error( $result ) ) {
				$this->fixit_failed = $result;
			}
		}
	}

	/**
	 * @param mixed $api
	 * @return void
	 */
	private function set_rest_api( $api ) {
		if ( $api >= 0 && $api <= Settings::API_JSON_RELATIVE ) {
			Settings::save( [ 'rest_api' => intval( $api, 10 ) ] );
		}
	}

	/**
	 * @return array{importers: list<array{id: string, name: string, total: int}>}|array{pluginStatus: array<string, mixed>}|array{}
	 */
	private function get_preload_data(): array {
		$status = new DatabaseStatus();

		if ( $status->needs_installing() ) {
			return [
				'importers' => PluginRegistry::get_plugins(),
			];
		}

		if ( $this->get_current_page() === 'support' ) {
			$fixer = new Fixer();

			return [
				'pluginStatus' => $fixer->get_json(),
			];
		}

		return [];
	}

	/**
	 * @return void
	 */
	private function add_help_tab() {
		/* translators: URL */
		$content = sprintf( __( 'You can find full documentation about using Redirection on the <a href="%s" target="_blank">redirection.me</a> support site.', 'redirection' ), 'https://redirection.me/support/?utm_source=redirection&utm_medium=plugin&utm_campaign=context-help' );
		$title = __( 'Redirection Support', 'redirection' );

		$current_screen = get_current_screen();
		if ( $current_screen === null ) {
			return;
		}

		$current_screen->add_help_tab(
			[
				'id'        => 'redirection',
				'title'     => 'Redirection',
				'content'   => "<h2>$title</h2><p>$content</p>",
			]
		);
	}

	/**
	 * @return int
	 */
	private function get_per_page() {
		$per_page = intval( get_user_meta( get_current_user_id(), 'redirection_log_per_page', true ), 10 );

		return $per_page > 0 ? max( 5, min( $per_page, Log::MAX_PER_PAGE ) ) : Log::DEFAULT_PER_PAGE;
	}

	/**
	 * @return void
	 */
	public function admin_menu() {
		$hook = add_management_page( 'Redirection', 'Redirection', Capabilities::get_plugin_access(), basename( REDIRECTION_FILE ), [ $this, 'admin_screen' ] );
		add_action( 'load-' . $hook, [ $this, 'redirection_head' ] );
	}

	/**
	 * @return bool
	 */
	private function check_minimum_wp() {
		$wp_version = get_bloginfo( 'version' );

		if ( version_compare( $wp_version, REDIRECTION_MIN_WP, '<' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Update the cache key when updating or creating a redirect
	 *
	 * @return void
	 */
	public function clear_cache() {
		$settings = Settings::get();

		if ( $settings['cache_key'] > 0 ) {
			Settings::save( [ 'cache_key' => time() ] );
		}
	}

	/**
	 * @param mixed $id
	 * @param mixed $redirect
	 * @return void
	 */
	public function set_default_group( $id, $redirect ) {
		Settings::save( [ 'last_group_id' => $redirect->get_group_id() ] );
	}

	/**
	 * @return void
	 */
	public function admin_screen() {
		if ( count( Capabilities::get_all_capabilities() ) === 0 ) {
			die( 'You do not have sufficient permissions to access this page.' );
		}

		if ( $this->check_minimum_wp() === false ) {
			$this->show_minimum_wordpress();
			return;
		}

		if ( $this->fixit_failed instanceof WP_Error ) {
			$this->show_fixit_failed();
		}

		Flusher::schedule();

		$this->show_main();
	}

	/**
	 * @return void
	 */
	private function show_fixit_failed() {
		if ( $this->fixit_failed === false ) {
			return;
		}
		?>
		<div class="notice notice-error">
			<h1><?php echo esc_html( $this->fixit_failed->get_error_message() ); ?></h1>
			<p><?php echo esc_html( $this->fixit_failed->get_error_data() ); ?></p>
		</div>
		<?php
	}

	/**
	 * @return void
	 */
	private function show_minimum_wordpress() {
		global $wp_version;

		/* translators: 1: Expected WordPress version, 2: Actual WordPress version */
		$wp_requirement = sprintf( __( 'Redirection requires WordPress v%1$1s, you are using v%2$2s - please update your WordPress', 'redirection' ), REDIRECTION_MIN_WP, $wp_version );
		?>
	<div class="react-error">
		<h1><?php esc_html_e( 'Unable to load Redirection', 'redirection' ); ?></h1>
		<p><?php echo esc_html( $wp_requirement ); ?></p>
	</div>
		<?php
	}

	/**
	 * @return void
	 */
	private function show_load_fail() {
		?>
		<div class="react-error" style="display: none">
			<h1><?php esc_html_e( 'Unable to load Redirection ☹️', 'redirection' ); ?> v<?php echo esc_html( REDIRECTION_VERSION ); ?></h1>
			<p><?php esc_html_e( "This may be caused by another plugin - look at your browser's error console for more details.", 'redirection' ); ?></p>
			<p><?php esc_html_e( 'If you are using a page caching plugin or service (CloudFlare, OVH, etc) then you can also try clearing that cache.', 'redirection' ); ?></p>
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %s: redirection script filename */
						__( 'Also check if your browser is able to load %s:', 'redirection' ),
						'<code>' . esc_html( 'redirection.js' ) . '</code>'
					)
				);
				?>
			</p>
			<p><code><?php echo esc_html( plugin_dir_url( REDIRECTION_FILE ) . 'redirection.js?ver=' . rawurlencode( REDIRECTION_VERSION ) ); ?></code></p>
			<p><?php esc_html_e( 'Please note that Redirection requires the WordPress REST API to be enabled. If you have disabled this then you won\'t be able to use Redirection', 'redirection' ); ?></p>
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %s: URL to common problems documentation */
						__( 'Please see the <a href="%s">list of common problems</a>.', 'redirection' ),
						esc_url( 'https://redirection.me/support/problems/' )
					)
				);
				?>
			</p>
			<p><?php esc_html_e( 'If you think Redirection is at fault then create an issue.', 'redirection' ); ?></p>
			<p class="versions">
				<?php
				echo wp_kses_post(
					__( '<code>Redirectioni10n</code> is not defined. This usually means another plugin is blocking Redirection from loading. Please disable all plugins and try again.', 'redirection' )
				);
				?>
			</p>
		<p>
			<a class="button-primary" target="_blank" href="https://github.com/johngodley/redirection/issues/new?title=Problem%20starting%20Redirection%20<?php echo esc_attr( REDIRECTION_VERSION ); ?>">
				<?php esc_html_e( 'Create Issue', 'redirection' ); ?>
			</a>
		</p>
	</div>
		<?php
	}

	/**
	 * @return void
	 */
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

			if ( document.querySelector( '.react-loading' ) ) {
				document.querySelector( '.react-loading' ).style.display = 'none';
				document.querySelector( '.react-error' ).style.display = 'block';

				if ( typeof Redirectioni10n !== 'undefined' && Redirectioni10n ) {
					document.querySelector( '.versions' ).innerHTML = Redirectioni10n.versions.replace( /\n/g, '<br />' );
					document.querySelector( '.react-error .button-primary' ).href += '&body=' + encodeURIComponent( errorText ) + encodeURIComponent( Redirectioni10n.versions );
				}
			} else {
				document.querySelector( '#react-ui' ).innerHTML = '<p>Sorry something went very wrong.</p>';
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

	/**
	 * Get the current plugin page.
	 * Uses $_GET['sub'] to determine the current page unless a page is supplied.
	 *
	 * @param string|false $page Current page.
	 * @return string|boolean Current page, or false.
	 */
	private function get_current_page( $page = false ) {
		if ( $page === false ) {
			$page = 'redirect';

			if ( $this->get_query( 'sub' ) !== null ) {
				$page = $this->get_query( 'sub' );
			}
		}

		// Are we allowed to access this page?
		if ( in_array( $page, Capabilities::get_available_pages(), true ) ) {
			return $page;
		}

		return false;
	}

	/**
	 * @return void
	 */
	private function inject() {
		$page = $this->get_query( 'page' );
		$current_page = $this->get_current_page();

		if ( $page !== null && $current_page !== 'redirect' && $page === 'redirection.php' ) {
			$this->try_export_logs();
			$this->try_export_redirects();
			$this->try_export_rss();
		}
	}

	/**
	 * @return void
	 */
	public function try_export_rss() {
		$token = $this->get_query( 'token' );
		$sub = $this->get_query( 'sub' );

		if ( $token !== null && $sub === 'rss' && Capabilities::has_access( Capabilities::CAP_REDIRECT_MANAGE ) ) {
			$options = Settings::get();

			if ( $token === $options['token'] && $options['token'] !== '' ) {
				$module = $this->get_query( 'module' );

				$items = Redirect::get_all_for_module( intval( $module, 10 ) );

				$formats = new FormatFactory();
				$exporter = $formats->create( 'rss' );
				if ( $exporter !== false ) {
					$exporter->force_download();

					// phpcs:ignore
					echo $exporter->get_data( $this->get_legacy_export_items( $items ), [] );
					die();
				}
			}
		}
	}

	/**
	 * Convert refactored redirects to legacy items expected by export formatters.
	 *
	 * Remove this once the remaining exporters accept Redirection\Redirect\Redirect.
	 *
	 * @param Redirect[] $items Redirects.
	 * @return \Red_Item[]
	 */
	private function get_legacy_export_items( array $items ) {
		return array_map(
			[ $this, 'get_legacy_export_item' ],
			$items
		);
	}

	/**
	 * Convert a refactored redirect to the legacy item expected by export formatters.
	 *
	 * Remove this once the remaining exporters accept Redirection\Redirect\Redirect.
	 *
	 * @param Redirect $item Redirect.
	 * @return \Red_Item
	 */
	private function get_legacy_export_item( Redirect $item ) {
		$match_data = $item->get_match_data();
		$match_data_json = '';

		if ( $match_data !== null ) {
			$encoded = wp_json_encode( $match_data, JSON_UNESCAPED_SLASHES );
			$match_data_json = is_string( $encoded ) ? $encoded : '';
		}

		return new \Red_Item(
			[
				'id' => $item->get_id(),
				'url' => $item->get_url(),
				'match_url' => $item->get_match_url(),
				'match_data' => $match_data_json,
				'regex' => $item->is_regex(),
				'action_data' => $item->get_action_data(),
				'action_code' => $item->get_action_code(),
				'action_type' => $item->get_action_type(),
				'match_type' => $item->get_match_type(),
				'title' => $item->get_title(),
				'last_access' => $item->get_last_hit(),
				'last_count' => $item->get_hits(),
				'status' => $item->is_enabled() ? 'enabled' : 'disabled',
				'position' => $item->get_position(),
				'group_id' => $item->get_group_id(),
			]
		);
	}

	/**
	 * @return void
	 */
	private function try_export_logs() {
		if ( Capabilities::has_access( Capabilities::CAP_IO_MANAGE ) && isset( $_POST['export-csv'] ) && check_admin_referer( 'wp_rest' ) !== false ) {
			if ( $this->get_current_page() === 'log' ) {
				RedirectLog::export_to_csv();
			} elseif ( $this->get_current_page() === '404s' ) {
				ErrorLog::export_to_csv();
			}

			die();
		}
	}

	/**
	 * @return void
	 */
	private function try_export_redirects() {
		$sub = $this->get_query( 'sub' );
		if ( $sub !== 'export' ) {
			return;
		}

		$export = $this->get_query( 'export' );
		$exporter = $this->get_query( 'exporter' );

		if ( Capabilities::has_access( Capabilities::CAP_IO_MANAGE ) && $export !== null && $exporter !== null && check_admin_referer( 'wp_rest' ) !== false ) {
			$export_service = new ExportService();
			$export = $export_service->export( $export, $exporter );

			if ( $export !== false ) {
				$export['exporter']->force_download();

				// This data is not displayed and will be downloaded to a file
				// phpcs:ignore
				echo str_replace( '&amp;', '&', wp_kses( $export['data'], 'strip' ) );
				die();
			}
		}
	}
}
