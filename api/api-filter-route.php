<?php

class Redirection_Api_Filter_Route extends Redirection_Api_Route {
	/**
	 * Validate filter param against allowed fields
	 *
	 * @param mixed $value Provided filter value.
	 * @param WP_REST_Request $request Request.
	 * @param string $param Param name.
	 *
	 * @phpstan-param mixed $value
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 * @phpstan-param string $param
	 * @phpstan-return true|WP_Error
	 * @return true|WP_Error
	 */
	public function validate_filter( $value, $request, $param ) {
		$attrs = $request->get_attributes();
		/** @var array<string, mixed> $attrs */
		$fields = [];
		if ( isset( $attrs['args'] ) && is_array( $attrs['args'] ) ) {
			$args = $attrs['args'];
			if ( isset( $args['filterBy'] ) && is_array( $args['filterBy'] ) ) {
				$filter_by = $args['filterBy'];
				if ( isset( $filter_by['filter_fields'] ) && is_array( $filter_by['filter_fields'] ) ) {
					/** @var list<string> $fields */
					$fields = array_values( array_map( 'strval', $filter_by['filter_fields'] ) );
				}
			}
		}

		// Value should be an array at this point based on REST validation
		if ( ! is_array( $value ) ) {
			return new WP_Error( 'rest_invalid_param', 'Filter is not an array', array( 'status' => 400 ) );
		}

		/** @var array<string, mixed> $value */

		if ( count( $fields ) > 0 ) {
			foreach ( array_keys( $value ) as $key ) {
				if ( ! in_array( $key, $fields, true ) ) {
					return new WP_Error( 'rest_invalid_param', 'Filter type is not supported: ' . $key, array( 'status' => 400 ) );
				}
			}
		}

		return true;
	}

	/**
	 * Build shared filter args
	 *
	 * @param list<string> $order_fields Fields allowed for orderby.
	 * @param list<string> $filters Fields allowed to filter by.
	 * @return array<string, mixed>
	 */
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
				'type' => 'string',
				'default' => 'desc',
				'enum' => [ 'asc', 'desc' ],
			],
			'per_page' => [
				'description' => 'Number of results per page',
				'type' => 'integer',
				'default' => 25,
				'minimum' => 5,
				'maximum' => Red_Item::MAX_PER_PAGE,
			],
			'page' => [
				'description' => 'Page offset',
				'type' => 'integer',
				'minimum' => 0,
				'default' => 0,
			],
		];
	}

	/**
	 * Register a bulk action endpoint for a resource
	 *
	 * @param string $namespace REST namespace.
	 * @param string $route REST route.
	 * @param list<string> $orders Allowed order fields.
	 * @param list<string> $filters Allowed filter fields.
	 * @param string $callback Handler method on this class.
	 * @param callable|array{0:self,1:string}|false $permissions Permission callback or false for default.
	 * @return void
	 */
	public function register_bulk( $namespace, $route, $orders, $filters, $callback, $permissions = false ) {
		register_rest_route(
			$namespace,
			$route,
			array_merge(
				$this->get_route( WP_REST_Server::EDITABLE, $callback, $permissions ),
				[
					'args' => array_merge(
						$this->get_filter_args( $orders, $filters ),
						[
							'items' => [
								'description' => 'Comma separated list of item IDs to perform action on',
								'type' => 'array',
								'items' => [
									'type' => 'string',
								],
							],
						]
					),
				]
			)
		);
	}
}
