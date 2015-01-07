<?php

include dirname( __FILE__ ).'/models/module.php';
include dirname( __FILE__ ).'/models/log.php';
include dirname( __FILE__ ).'/models/match.php';
include dirname( __FILE__ ).'/models/action.php';
include dirname( __FILE__ ).'/modules/wordpress.php';
include dirname( __FILE__ ).'/models/redirect.php';

class Redirection {
	const DELETE_HOOK = 'redirection_log_delete';
	const DELETE_FREQ = 'daily';
	const DELETE_MAX = 1000;

	private static $instance = null;
	private $module;

	static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Redirection();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->module = new WordPress_Module();
		$this->module->start();

		add_action( 'redirection_log_delete', array( $this, 'clean_redirection_logs' ) );

		if ( !wp_next_scheduled( self::DELETE_HOOK ) )
			wp_schedule_event( time(), self::DELETE_FREQ, self::DELETE_HOOK );
	}

	public function clean_redirection_logs() {
		$options = red_get_options();

		if ( $this->expire_logs( $options['expire_redirect'] ) || $this->expire_404( $options['expire_404'] ) )
			$this->optimize_logs();
	}

	private function optimize_logs() {
		$rand = mt_rand( 1, 5000 );

		if ( $rand === 11 )
			$wpdb->query( "OPTIMIZE TABLE {$wpdb->prefix}redirection_logs" );
		elseif ( $rand === 12 )
			$wpdb->query( "OPTIMIZE TABLE {$wpdb->prefix}redirection_404" );
	}

	private function expire_logs( $expiry_time ) {
		global $wpdb;

		if ( $expiry_time > 0 ) {
			$logs = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_logs WHERE created < DATE_SUB(NOW(), INTERVAL %d DAY)", $expiry_time ) );

			if ( $logs > 0 ) {
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_logs WHERE created < DATE_SUB(NOW(), INTERVAL %d DAY) LIMIT %d", $expiry_time, self::DELETE_MAX ) );
				return true;
			}
		}

		return false;
	}

	private function expire_404( $expiry_time ) {
		global $wpdb;

		if ( $expiry_time > 0 ) {
			$l404 = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_404 WHERE created < DATE_SUB(NOW(), INTERVAL %d DAY)", $expiry_time ) );

			if ( $l404 > 0 ) {
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_404 WHERE created < DATE_SUB(NOW(), INTERVAL %d DAY) LIMIT %d", $expiry_time, self::DELETE_MAX ) );
				return true;
			}
		}

		return false;
	}
}

add_action( 'plugins_loaded', array( 'Redirection', 'init' ) );
