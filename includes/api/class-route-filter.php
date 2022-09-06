<?php

namespace Redirection\Api;

class Filter_Route extends Route {
	public function validate_filter( $value, $request, $param ) {
		$fields = $request->get_attributes()['args']['filterBy']['filter_fields'];

		if ( ! is_array( $value ) ) {
			return new \WP_Error( 'rest_invalid_param', 'Filter is not an array', array( 'status' => 400 ) );
		}

		if ( ! empty( $fields ) ) {
			foreach ( array_keys( $value ) as $key ) {
				if ( ! in_array( $key, $fields, true ) ) {
					return new \WP_Error( 'rest_invalid_param', 'Filter type is not supported: ' . $key, array( 'status' => 400 ) );
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
			$this->get_route( \WP_REST_Server::EDITABLE, $callback, $permissions ),
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
