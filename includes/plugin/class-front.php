<?php

namespace Redirection\Plugin;

use Redirection\Database\Status as DatabaseStatus;
use Redirection\Module\Module;
use Redirection\Module\Plugin;
use Redirection\Settings\Settings;
use Redirection\Url\Transform;

/**
 * This powers all of the front-end redirecting
 */
class Front {
	/**
	 * Instance variable
	 *
	 * @var Front|null
	 */
	private static $instance = null;

	/**
	 * WordPress module
	 *
	 * @var Plugin|false
	 */
	private $module = false;

	/**
	 * Singleton
	 *
	 * @return Front
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Front();
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

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( ! $this->can_start() ) {
			return;
		}

		/** @var Plugin|false */
		$module = Module::get( Plugin::MODULE_ID );
		$this->module = $module;
		if ( $this->module !== false ) {
			$this->module->start();
		}

		add_action( Flusher::DELETE_HOOK, [ $this, 'clean_redirection_logs' ] );
		add_filter( 'redirection_url_target', [ $this, 'transform_url' ] );

		$options = Settings::get();
		if ( $options['ip_logging'] === 0 ) {
			add_filter( 'redirection_request_ip', [ $this, 'no_ip_logging' ] );
		} elseif ( $options['ip_logging'] === 2 ) {
			add_filter( 'redirection_request_ip', [ $this, 'mask_ip' ] );
		}
	}

	/**
	 * @param string $url
	 * @return string
	 */
	public function transform_url( $url ) {
		$transformer = new Transform();

		return $transformer->transform( $url );
	}

	/**
	 * Check if Redirection can run. We require the database to be installed.
	 *
	 * @return boolean
	 */
	public function can_start() {
		$status = new DatabaseStatus();
		if ( $status->needs_installing() ) {
			return false;
		}

		return true;
	}

	/**
	 * Override the IP with an empty value
	 *
	 * @param string $ip IP.
	 * @return string
	 */
	public function no_ip_logging( $ip ) {
		return '';
	}

	/**
	 * Override the IP with a masked IP
	 *
	 * @param string $ip IP.
	 * @return string
	 */
	public function mask_ip( $ip ) {
		$ip = trim( $ip );

		if ( strpos( $ip, ':' ) !== false ) {
			$packed = inet_pton( trim( $ip ) );
			if ( $packed === false ) {
				return '';
			}

			// Mask lower 64 bits of the IPv6 address (keep the upper 64 bits).
			$mask = str_repeat( "\xff", 8 ) . str_repeat( "\x00", 8 );
			$masked = $packed & $mask;
			$converted = inet_ntop( $masked );
			if ( $converted === false ) {
				return '';
			}

			return $converted;
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
		$flusher = new Flusher();
		$flusher->flush();
	}

	/**
	 * Used for unit tests
	 *
	 * @return Plugin|false
	 */
	public function get_module() {
		return $this->module;
	}
}
