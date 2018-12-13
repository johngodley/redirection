<?php

include_once dirname( __FILE__ ) . '/api/api-group.php';
include_once dirname( __FILE__ ) . '/api/api-redirect.php';
include_once dirname( __FILE__ ) . '/api/api-log.php';
include_once dirname( __FILE__ ) . '/api/api-404.php';
include_once dirname( __FILE__ ) . '/api/api-settings.php';
include_once dirname( __FILE__ ) . '/api/api-plugin.php';
include_once dirname( __FILE__ ) . '/api/api-import.php';
include_once dirname( __FILE__ ) . '/api/api-export.php';

define( 'REDIRECTION_API_NAMESPACE', 'redirection/v1' );

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
		return current_user_can( apply_filters( 'redirection_role', 'manage_options' ) );
	}

	public function get_route( $method, $callback ) {
		return array(
			'methods' => $method,
			'callback' => array( $this, $callback ),
			'permission_callback' => array( $this, 'permission_callback' ),
		);
	}
}

class Redirection_Api_Filter_Route extends Redirection_Api_Route {
	protected function get_filter_args( $filter_fields, $order_fields ) {
		return array(
			'filterBy' => array(
				'description' => 'Field to filter by',
				'type' => 'enum',
				'enum' => $filter_fields,
			),
			'filter' => array(
				'description' => 'Value to filter by',
				'type' => 'string',
			),
			'orderby' => array(
				'description' => 'Field to order results by',
				'type' => 'enum',
				'enum' => $order_fields,
			),
			'direction' => array(
				'description' => 'Direction of ordered results',
				'type'        => 'enum',
				'default' => 'desc',
				'enum' => array( 'asc', 'desc' ),
			),
			'per_page' => array(
				'description' => 'Number of results per page',
				'type'        => 'integer',
				'default'     => 25,
				'minimum'     => 5,
				'maximum'     => RED_MAX_PER_PAGE,
			),
			'page' => array(
				'description' => 'Page offset',
				'type'        => 'integer',
				'minimum'     => 0,
				'default'     => 0,
			),
		);
	}

	public function register_bulk( $namespace, $route, $filters, $orders, $callback ) {
		register_rest_route( $namespace, $route, array(
			$this->get_route( WP_REST_Server::EDITABLE, $callback ),
			'args' => array_merge( $this->get_filter_args( $filters, $orders ), array(
				'items' => array(
					'description' => 'Comma separated list of item IDs to perform action on',
					'type' => 'string|integer',
					'required' => true,
				),
			) ),
		) );
	}
}

class Redirection_Api {
	private static $instance = null;
	private $routes = array();

	static function init() {
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
