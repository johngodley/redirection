<?php

namespace Redirection\Api;

use Redirection\Api\Route\Error;
use Redirection\Api\Route\Export;
use Redirection\Api\Route\Group;
use Redirection\Api\Route\Import;
use Redirection\Api\Route\Log;
use Redirection\Api\Route\Plugin;
use Redirection\Api\Route\Redirect;
use Redirection\Api\Route\Settings;

if ( ! defined( 'REDIRECTION_API_NAMESPACE' ) ) {
	define( 'REDIRECTION_API_NAMESPACE', 'redirection/v1' );
}

class Api {
	/**
	 * @var Api|null
	 */
	private static $instance = null;

	/**
	 * @var array<int, Route>
	 * @phpstan-ignore property.onlyWritten
	 */
	private $routes = array();

	/**
	 * @return Api
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Api();
		}

		return self::$instance;
	}

	/**
	 * @return void
	 */
	public function __construct() {
		global $wpdb;

		$wpdb->hide_errors();

		$this->routes[] = new Redirect( REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Group( REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Log( REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Error( REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Settings( REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Plugin( REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Import( REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Export( REDIRECTION_API_NAMESPACE );
	}
}
