<?php

require_once __DIR__ . '/modules/wordpress.php';
require_once __DIR__ . '/models/canonical.php';
require_once __DIR__ . '/database/database-status.php';

/**
 * This powers all of the front-end redirecting
 */
class Redirection {
	/**
	 * Instance variable
	 *
	 * @var Redirection|null
	 */
	private static $instance = null;

	/**
	 * WordPress module
	 *
	 * @var WordPress_Module|null
	 */
	private $module = null;

	/**
	 * Singleton
	 *
	 * @return Redirection
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Redirection();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( ! $this->can_start() ) {
			return;
		}

		$this->module = Red_Module::get( WordPress_Module::MODULE_ID );
		if ( $this->module ) {
			$this->module->start();
		}

		add_action( Red_Flusher::DELETE_HOOK, array( $this, 'clean_redirection_logs' ) );
		add_filter( 'redirection_url_target', array( $this, 'replace_special_tags' ) );

		$options = red_get_options();
		if ( $options['ip_logging'] === 0 ) {
			add_filter( 'redirection_request_ip', array( $this, 'no_ip_logging' ) );
		} elseif ( $options['ip_logging'] === 2 ) {
			add_filter( 'redirection_request_ip', array( $this, 'mask_ip' ) );
		}
	}

	/**
	 * Check if Redirection can run. We require the database to be installed.
	 *
	 * @return boolean
	 */
	public function can_start() {
		$status = new Red_Database_Status();
		if ( $status->needs_installing() ) {
			return false;
		}

		return true;
	}

	/**
	 * Override the IP with an empty value
	 *
	 * @param String $ip IP.
	 * @return String
	 */
	public function no_ip_logging( $ip ) {
		return '';
	}

	/**
	 * Override the IP with a masked IP
	 *
	 * @param String $ip IP.
	 * @return String
	 */
	public function mask_ip( $ip ) {
		$ip = trim( $ip );

		if ( strpos( $ip, ':' ) !== false ) {
			// phpcs:ignore
			$ip = @inet_pton( trim( $ip ) );

			// phpcs:ignore
			return @inet_ntop( $ip & pack( 'a16', 'ffff:ffff:ffff:ffff::ff00::0000::0000::0000' ) );
		}

		$parts = [];
		if ( strlen( $ip ) > 0 ) {
			$parts = explode( '.', $ip );
		}

		if ( count( $parts ) > 0 ) {
			$parts[ count( $parts ) - 1 ] = 0;
		}

		return implode( '.', $parts );
	}

	/**
	 * Hook to flush the logs
	 *
	 * @return void
	 */
	public function clean_redirection_logs() {
		$flusher = new Red_Flusher();
		$flusher->flush();
	}

	/**
	 * Replace special tags in the target URL.
	 *
	 * From the distant Redirection past. Undecided whether to keep
	 *
	 * @param String $url Target URL.
	 * @return String
	 */
	public function replace_special_tags( $url ) {
		if ( is_numeric( $url ) ) {
			$url = get_permalink( $url );
		} else {
			$user = wp_get_current_user();

			if ( ! empty( $user ) ) {
				$url = str_replace( '%userid%', $user->ID, $url );
				$url = str_replace( '%userlogin%', isset( $user->user_login ) ? $user->user_login : '', $url );
				$url = str_replace( '%userurl%', isset( $user->user_url ) ? $user->user_url : '', $url );
			}
		}

		return $url;
	}

	/**
	 * Used for unit tests
	 *
	 * @return WordPress_Module|null
	 */
	public function get_module() {
		return $this->module;
	}
}

add_action( 'plugins_loaded', array( 'Redirection', 'init' ) );
