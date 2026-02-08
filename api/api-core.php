<?php

class Redirection_Api {
	/**
	 * @var Redirection_Api|null
	 */
	private static $instance = null;

	/**
	 * @var array<int, Redirection_Api_Route>
	 * @phpstan-ignore property.onlyWritten
	 */
	private $routes = array();

	/**
	 * @return Redirection_Api
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Redirection_Api();
		}

		return self::$instance;
	}

	/**
	 * @return void
	 */
	public function __construct() {
		global $wpdb;

		$wpdb->hide_errors();

		$this->routes[] = new Redirection_Api_Redirect( REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Redirection_Api_Group( REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Redirection_Api_Log( REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Redirection_Api_404( REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Redirection_Api_Settings( REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Redirection_Api_Plugin( REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Redirection_Api_Import( REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Redirection_Api_Export( REDIRECTION_API_NAMESPACE );
	}
}
