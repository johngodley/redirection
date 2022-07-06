<?php

namespace Redirection\Api;

require_once __DIR__ . '/class-route.php';
require_once __DIR__ . '/class-route-filter.php';
require_once __DIR__ . '/route/route-group.php';
require_once __DIR__ . '/route/route-redirect.php';
require_once __DIR__ . '/route/route-log.php';
require_once __DIR__ . '/route/route-404.php';
require_once __DIR__ . '/route/route-settings.php';
require_once __DIR__ . '/route/route-plugin.php';
require_once __DIR__ . '/route/route-import.php';
require_once __DIR__ . '/route/route-export.php';

/**
 * @apiDefine 401Error
 *
 * @apiError (Error 401) rest_forbidden You are not authorized to access this API endpoint
 * @apiErrorExample {json} 401 Error Response:
 *     HTTP/1.1 401 Bad Request
 *     {
 *       "code": "rest_forbidden",
 *       "message": "Sorry, you are not allowed to do that."
 *     }
 */

/**
 * @apiDefine 404Error
 *
 * @apiError (Error 404) rest_no_route Endpoint not found
 * @apiErrorExample {json} 404 Error Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "code": "rest_no_route",
 *       "message": "No route was found matching the URL and request method"
 *     }
 */

/**
 * @apiDefine 400Error
 *
 * @apiError rest_forbidden You are not authorized to access this API endpoint
 * @apiErrorExample {json} 400 Error Response:
 *     HTTP/1.1 400 Bad Request
 *     {
 *       "error": "invalid",
 *       "message": "Invalid request"
 *     }
 */

/**
 * @apiDefine 400MissingError
 * @apiError (Error 400) rest_missing_callback_param Some required parameters are not present or not in the correct format
 * @apiErrorExample {json} 400 Error Response:
 *     HTTP/1.1 400 Bad Request
 *     {
 *       "code": "rest_missing_callback_param",
 *       "message": "Missing parameter(s): PARAM"
 *     }
 */

class Api {
	const REDIRECTION_API_NAMESPACE = 'redirection/v1';
	private static $instance = null;
	private $routes = array();

	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		global $wpdb;

		$this->routes[] = new Route\Redirect( self::REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Route\Group( self::REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Route\Log( self::REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Route\Error( self::REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Route\Settings( self::REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Route\Plugin( self::REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Route\Import( self::REDIRECTION_API_NAMESPACE );
		$this->routes[] = new Route\Export( self::REDIRECTION_API_NAMESPACE );
	}
}
