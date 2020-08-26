<?php

require_once __DIR__ . '/api-group.php';
require_once __DIR__ . '/api-redirect.php';
require_once __DIR__ . '/api-log.php';
require_once __DIR__ . '/api-404.php';
require_once __DIR__ . '/api-settings.php';
require_once __DIR__ . '/api-plugin.php';
require_once __DIR__ . '/api-import.php';
require_once __DIR__ . '/api-export.php';

define( 'REDIRECTION_API_NAMESPACE', 'redirection/v1' );

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
class Redirection_Api_Route {
	protected function add_error_details( WP_Error $error, $line, $code = 400 ) {
		global $wpdb;

		$data = array(
			'status' => $code,
			'error_code' => $line,
		);

		if ( isset( $wpdb->last_error ) && $wpdb->last_error ) {
			$data['wpdb'] = $wpdb->last_error;
		}

		$error->add_data( $data );
		return $error;
	}

	public function permission_callback( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_PLUGIN );
	}

	public function get_route( $method, $callback, $permissions = false ) {
		return [
			'methods' => $method,
			'callback' => [ $this, $callback ],
			'permission_callback' => $permissions ? $permissions : [ $this, 'permission_callback' ],
		];
	}
}

class Redirection_Api_Filter_Route extends Redirection_Api_Route {
	public function validate_filter( $value, $request, $param ) {
		$fields = $request->get_attributes()['args']['filterBy']['filter_fields'];

		if ( ! is_array( $value ) ) {
			return new WP_Error( 'rest_invalid_param', 'Filter is not an array', array( 'status' => 400 ) );
		}

		if ( ! empty( $fields ) ) {
			foreach ( array_keys( $value ) as $key ) {
				if ( ! in_array( $key, $fields, true ) ) {
					return new WP_Error( 'rest_invalid_param', 'Filter type is not supported: ' . $key, array( 'status' => 400 ) );
				}
			}
		}

		return true;
	}

	protected function get_filter_args( $order_fields, $filters = [] ) {
		return [
			'filterBy' => [
				'description' => 'Field to filter by',
				'validate_callback' => [ $this, 'validate_filter' ],
				'filter_fields' => $filters,
			],
			'orderby' => [
				'description' => 'Field to order results by',
				'type' => 'string',
				'enum' => $order_fields,
			],
			'direction' => [
				'description' => 'Direction of ordered results',
				'type'        => 'string',
				'default' => 'desc',
				'enum' => [ 'asc', 'desc' ],
			],
			'per_page' => [
				'description' => 'Number of results per page',
				'type'        => 'integer',
				'default'     => 25,
				'minimum'     => 5,
				'maximum'     => RED_MAX_PER_PAGE,
			],
			'page' => [
				'description' => 'Page offset',
				'type'        => 'integer',
				'minimum'     => 0,
				'default'     => 0,
			],
		];
	}

	/**
	 * Register a bulk action route
	 *
	 * @param String  $namespace Namespace.
	 * @param String  $route Route.
	 * @param Array   $orders
	 * @param Array   $filters
	 * @param Object  $callback
	 * @param boolean $permissions
	 * @return void
	 */
	public function register_bulk( $namespace, $route, $orders, $filters, $callback, $permissions = false ) {
		register_rest_route( $namespace, $route, array(
			$this->get_route( WP_REST_Server::EDITABLE, $callback, $permissions ),
			'args' => array_merge( $this->get_filter_args( $orders, $filters ), [
				'items' => [
					'description' => 'Comma separated list of item IDs to perform action on',
					'type' => 'array',
					'items' => [
						'type' => 'string',
					],
				],
			] ),
		) );
	}
}

class Redirection_Api {
	private static $instance = null;
	private $routes = array();

	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Redirection_Api();
		}

		return self::$instance;
	}

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
