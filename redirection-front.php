<?php

include_once dirname( __FILE__ ).'/modules/wordpress.php';

class Redirection {
	private static $instance = null;
	private $module;

	static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Redirection();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->module = Red_Module::get( WordPress_Module::MODULE_ID );
		$this->module->start();

		add_action( Red_Flusher::DELETE_HOOK, array( $this, 'clean_redirection_logs' ) );
		add_action( 'redirection_url_target', array( $this, 'replace_special_tags' ) );
	}

	public function clean_redirection_logs() {
		$flusher = new Red_Flusher();
		$flusher->flush();
	}

	/**
	 * From the distant Redirection past. Undecided whether to keep
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

	public function get_module() {
		return $this->module;
	}
}

add_action( 'plugins_loaded', array( 'Redirection', 'init' ) );
