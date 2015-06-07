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
	}

	public function clean_redirection_logs() {
		$flusher = new Red_Flusher();
		$flusher->flush();
	}
}

add_action( 'plugins_loaded', array( 'Redirection', 'init' ) );
