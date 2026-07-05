<?php

/**
 * @phpstan-type RedirectListResponse array{
 *   items: list<array<string, mixed>>,
 *   total: int
 * }
 *
 * Redirect API endpoint
 */
class Redirection_Api_Redirect extends Redirection_Api_Filter_Route {
	/**
	 * Redirect API endpoint constructor
	 *
	 * @param non-falsy-string $api_namespace Namespace.
	 */
	public function __construct( $api_namespace ) {
		$orders = [ 'source', 'last_count', 'last_access', 'position', 'id', '' ];
		$filters = [ 'status', 'url-match', 'match', 'action', 'http', 'access', 'url', 'target', 'title', 'group', 'id' ];

		// GET /redirect - List redirects
		// POST /redirect - Create redirect
		register_rest_route(
			$api_namespace,
			'/redirect',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_list' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
					'args' => $this->get_filter_args( $orders, $filters ),
				],
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'route_create' ],
					'permission_callback' => [ $this, 'permission_callback_add' ],
					'args' => $this->get_filter_args( $orders, $filters ),
				],
			]
		);

		// POST /redirect/:id - Update redirect
		register_rest_route(
			$api_namespace,
			'/redirect/(?P<id>[\d]+)',
			[
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'route_update' ],
					'permission_callback' => [ $this, 'permission_callback_add' ],
				],
			]
		);

		// GET /redirect/post - Search for posts
		register_rest_route(
			$api_namespace,
			'/redirect/post',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'route_match_post' ],
					'permission_callback' => [ $this, 'permission_callback_manage' ],
					'args' => [
						'text' => [
							'description' => 'Text to match',
							'type' => 'string',
							'required' => true,
						],
					],
				],
			]
		);

		// POST /bulk/redirect/:bulk - Bulk actions on redirects
		register_rest_route(
			$api_namespace,
			'/bulk/redirect/(?P<bulk>delete|enable|disable|reset)',
			[
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'route_bulk' ],
					'permission_callback' => [ $this, 'permission_callback_bulk' ],
					'args' => array_merge(
						$this->get_filter_args( $orders, $filters ),
						[
							'global' => [
								'description' => 'Apply bulk action globally, as per filters',
								'type' => 'boolean',
							],
							'items' => [
								'description' => 'Array of IDs to perform action on',
								'type' => 'array',
								'items' => [
									'description' => 'Item ID',
									'type' => [ 'string', 'number' ],
								],
							],
						]
					),
				],
			]
		);
	}

	/**
	 * Checks a manage capability
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request.
	 * @return bool
	 */
	public function permission_callback_manage( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_REDIRECT_MANAGE );
	}

	/**
	 * Checks a bulk capability
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request.
	 * @return bool
	 */
	public function permission_callback_bulk( WP_REST_Request $request ) {
		if ( $request['bulk'] === 'delete' ) {
			return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_REDIRECT_DELETE );
		}

		return $this->permission_callback_add( $request );
	}

	/**
	 * Checks a create capability
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request.
	 * @return bool
	 */
	public function permission_callback_add( WP_REST_Request $request ) {
		return Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_REDIRECT_ADD );
	}

	/**
	 * Get redirect list
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request The request.
	 * @return RedirectListResponse
	 */
	public function route_list( WP_REST_Request $request ) {
		return Red_Item::get_filtered( $request->get_params() );
	}

	/**
	 * Create new redirect(s)
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request The request.
	 * @return RedirectListResponse|WP_Error
	 */
	public function route_create( WP_REST_Request $request ) {
		$params = $request->get_params();
		$urls = array();

		if ( isset( $params['url'] ) ) {
			$urls = array( $params['url'] );

			if ( is_array( $params['url'] ) ) {
				$urls = $params['url'];
			}

			// Remove duplicates
			$unique = [];
			foreach ( $urls as $url ) {
				$unique[ $url ] = $url;
			}

			foreach ( $unique as $url ) {
				$params['url'] = $url;

				// Data is sanitized in the create function
				$redirect = Red_Item::create( $params );

				if ( is_wp_error( $redirect ) ) {
					return $this->add_error_details( $redirect, __LINE__ );
				}
			}
		}

		return $this->route_list( $request );
	}

	/**
	 * Update redirect
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request The request.
	 * @return array{item: array<string, mixed>}|WP_Error
	 */
	public function route_update( WP_REST_Request $request ) {
		$params = $request->get_params();
		$redirect = Red_Item::get_by_id( intval( $params['id'], 10 ) );

		if ( $redirect !== false ) {
			$result = $redirect->update( $params );

			if ( is_wp_error( $result ) ) {
				return $this->add_error_details( $result, __LINE__ );
			}

			return [ 'item' => $redirect->to_json() ];
		}

		return $this->add_error_details( new WP_Error( 'redirect_update_failed', 'Invalid redirect details' ), __LINE__ );
	}

	/**
	 * Perform bulk action on redirects
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request The request.
	 * @return RedirectListResponse|WP_Error
	 */
	public function route_bulk( WP_REST_Request $request ) {
		$params = $request->get_params();
		$action = sanitize_text_field( $request['bulk'] );

		if ( isset( $params['items'] ) && is_array( $params['items'] ) && count( $params['items'] ) > 0 ) {
			$items = $params['items'];

			foreach ( $items as $item ) {
				$redirect = Red_Item::get_by_id( intval( $item, 10 ) );

				if ( $redirect === false ) {
					return $this->add_error_details( new WP_Error( 'redirect_bulk_failed', 'Invalid redirect' ), __LINE__ );
				}

				if ( $action === 'delete' ) {
					$redirect->delete();
				} elseif ( $action === 'disable' ) {
					$redirect->disable();
				} elseif ( $action === 'enable' ) {
					$redirect->enable();
				} elseif ( $action === 'reset' ) {
					$redirect->reset();
				}
			}
		} elseif ( isset( $params['global'] ) && $params['global'] !== false ) {
			// Params are sanitized in the filter class
			if ( $action === 'delete' ) {
				Red_Item::delete_all( $params );
			} elseif ( $action === 'reset' ) {
				Red_Item::reset_all( $params );
			} elseif ( $action === 'enable' || $action === 'disable' ) {
				Red_Item::set_status_all( $action, $params );
			}
		}

		return $this->route_list( $request );
	}

	/**
	 * Search for a post
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request The request.
	 * @return list<array{title: string, value: string|false}>
	 */
	public function route_match_post( WP_REST_Request $request ) {
		global $wpdb;

		$params = $request->get_params();
		$search = sanitize_text_field( $params['text'] );
		$results = [];

		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID,post_title,post_name FROM $wpdb->posts WHERE post_status='publish' AND (post_title LIKE %s OR post_name LIKE %s) " .
				"AND post_type IN ('post','page')",
				'%' . $wpdb->esc_like( $search ) . '%',
				'%' . $wpdb->esc_like( $search ) . '%'
			)
		);

		foreach ( (array) $posts as $post ) {
			$title = $post->post_name;
			if ( strpos( $post->post_title, $search ) !== false ) {
				$title = $post->post_title;
			}

			$results[] = [
				'title' => $title,
				'value' => get_permalink( $post->ID ),
			];
		}

		return $results;
	}
}
