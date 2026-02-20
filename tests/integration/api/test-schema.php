<?php

/**
 * API schema contract tests
 *
 * These tests assert that API responses match the shape expected by the Zod schemas
 * on the frontend. Any mismatch here would cause a Zod validation error in the browser.
 *
 * Field types and nullability must match what the frontend schemas declare in:
 *   - src/types/schemas/log.ts (LogSchema, Error404Schema)
 *   - src/types/schemas/redirect.ts (RedirectSchema)
 */
class RedirectionApiSchemaTest extends Redirection_Api_Test {
	private $group;

	public function setUp(): void {
		parent::setUp();

		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->prefix}redirection_logs" );
		$wpdb->query( "TRUNCATE {$wpdb->prefix}redirection_404" );
		$wpdb->query( "DELETE FROM {$wpdb->prefix}redirection_items" );

		$this->group = Red_Group::create( 'schema-test-group', 1 );
		$this->setNonce();
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	private function check_string_or_null( $item, $field ) {
		$value = isset( $item[ $field ] ) ? $item[ $field ] : null;
		$this->assertTrue(
			$value === null || is_string( $value ),
			"Field '$field' must be string|null, got " . gettype( $value )
		);
	}

	private function check_string_field( $item, $field ) {
		$this->assertArrayHasKey( $field, $item, "Field '$field' missing from response" );
		$this->assertIsString( $item[ $field ], "Field '$field' must be a string" );
	}

	private function check_int_field( $item, $field ) {
		$this->assertArrayHasKey( $field, $item, "Field '$field' missing from response" );
		$this->assertIsInt( $item[ $field ], "Field '$field' must be an int" );
	}

	private function check_id_int_or_string( $item ) {
		$this->assertArrayHasKey( 'id', $item );
		$this->assertTrue(
			is_int( $item['id'] ) || is_string( $item['id'] ),
			"Field 'id' must be int|string, got " . gettype( $item['id'] )
		);
	}

	private function check_paginated_response( $result ) {
		$this->assertEquals( 200, $result->status, 'Expected 200 response' );
		// $result->data may be an array or stdClass depending on the response — normalise to array
		$data = (array) $result->data;
		$this->assertArrayHasKey( 'items', $data, "'items' key missing from response" );
		$this->assertArrayHasKey( 'total', $data, "'total' key missing from response" );
		$this->assertIsArray( $data['items'], "'items' must be an array" );
		$this->assertIsInt( $data['total'], "'total' must be an int" );
	}

	/**
	 * Get a response item normalised as array (items can be stdClass or array).
	 */
	private function get_item( $result, $index = 0 ) {
		$data = (array) $result->data;
		return (array) $data['items'][ $index ];
	}

	/**
	 * Get all items from a response, each normalised as array.
	 */
	private function get_items( $result ) {
		$data = (array) $result->data;
		return array_map(
			static function ( $item ) {
				return (array) $item;
			},
			$data['items']
		);
	}

	// -------------------------------------------------------------------------
	// Redirect log schema tests
	// -------------------------------------------------------------------------

	/**
	 * Log list response must match LogSchema:
	 *   id: number | string
	 *   created?: string
	 *   url?: string
	 *   sent_to?: string | null
	 *   agent?: string | null
	 *   referrer?: string | null
	 *   ip?: string | null
	 *   domain?: string | null
	 *   redirect_id?: number
	 *   redirection_id?: number
	 *   request_method?: string | null
	 *   http_code?: number
	 *   redirect_by?: string | null
	 *   count?: number
	 */
	public function testLogItemSchema() {
		Red_Redirect_Log::create( 'https://example.com', '/test-url', '192.168.1.1', [
			'agent'    => 'Mozilla/5.0',
			'target'   => '/target-url',
			'referrer' => 'https://referrer.example.com',
		] );

		$result = $this->callApi( 'log' );

		$this->check_paginated_response( $result );
		$item = $this->get_item( $result );

		$this->check_id_int_or_string( $item );

		// String fields present for a standard log entry
		$this->check_string_field( $item, 'created' );
		$this->check_string_field( $item, 'url' );

		// Nullable string fields — must be string or null, never an unexpected type
		$this->check_string_or_null( $item, 'sent_to' );
		$this->check_string_or_null( $item, 'agent' );
		$this->check_string_or_null( $item, 'referrer' );
		$this->check_string_or_null( $item, 'ip' );
		$this->check_string_or_null( $item, 'domain' );
		$this->check_string_or_null( $item, 'request_method' );
		$this->check_string_or_null( $item, 'redirect_by' );

		if ( isset( $item['http_code'] ) ) {
			$this->assertIsInt( $item['http_code'], 'http_code must be int' );
		}
	}

	/**
	 * A log entry with null agent must not cause a Zod validation failure.
	 */
	public function testLogItemNullAgent() {
		Red_Redirect_Log::create( 'https://example.com', '/test-null-agent', '10.0.0.1', [
			'agent'    => null,
			'referrer' => null,
		] );

		$result = $this->callApi( 'log' );
		$this->assertEquals( 200, $result->status );

		$item = $this->get_item( $result );

		$this->check_string_or_null( $item, 'agent' );
		$this->check_string_or_null( $item, 'referrer' );
	}

	/**
	 * Grouped log response (groupBy=url) must return string id equal to the URL value.
	 * LogSchema: id: number | string
	 */
	public function testLogGroupedByUrlSchema() {
		Red_Redirect_Log::create( 'https://example.com', '/group-url', '192.168.1.1', [ 'agent' => 'bot' ] );
		Red_Redirect_Log::create( 'https://example.com', '/group-url', '192.168.1.2', [ 'agent' => 'bot2' ] );

		$result = $this->callApi( 'log', [ 'groupBy' => 'url' ] );

		$this->check_paginated_response( $result );
		$item = $this->get_item( $result );

		$this->assertIsString( $item['id'], 'Grouped log id must be a string (the URL value)' );
		$this->assertArrayHasKey( 'count', $item, "'count' must be present for grouped results" );
		$this->assertIsInt( $item['count'], "'count' must be an int" );
	}

	/**
	 * Grouped log response (groupBy=ip) — id is the IP string, count is present.
	 */
	public function testLogGroupedByIpSchema() {
		Red_Redirect_Log::create( 'https://example.com', '/url1', '10.10.10.10', [ 'agent' => 'bot' ] );
		Red_Redirect_Log::create( 'https://example.com', '/url2', '10.10.10.10', [ 'agent' => 'bot' ] );

		$result = $this->callApi( 'log', [ 'groupBy' => 'ip' ] );

		$this->check_paginated_response( $result );
		$item = $this->get_item( $result );

		$this->check_id_int_or_string( $item );
		$this->assertArrayHasKey( 'count', $item );
		$this->assertIsInt( $item['count'] );
	}

	/**
	 * Grouped log response (groupBy=agent) with null agent — id must be string, not null.
	 * Zod schema accepts string id for grouped results.
	 */
	public function testLogGroupedByAgentWithNullAgent() {
		Red_Redirect_Log::create( 'https://example.com', '/url1', '192.168.0.1', [ 'agent' => null ] );
		Red_Redirect_Log::create( 'https://example.com', '/url2', '192.168.0.2', [ 'agent' => null ] );

		$result = $this->callApi( 'log', [ 'groupBy' => 'agent' ] );

		$this->check_paginated_response( $result );

		$data = (array) $result->data;
		foreach ( $data['items'] as $raw_item ) {
			$item = (array) $raw_item;
			// id must be int or string — never null (PHP returns '' for null agent group)
			$this->assertTrue(
				is_int( $item['id'] ) || is_string( $item['id'] ),
				'Grouped agent id must be int|string, got: ' . gettype( $item['id'] )
			);
		}
	}

	// -------------------------------------------------------------------------
	// 404 error log schema tests
	// -------------------------------------------------------------------------

	/**
	 * 404 list response must match Error404Schema:
	 *   id: number | string
	 *   created?: string
	 *   url?: string
	 *   agent?: string | null
	 *   referrer?: string | null
	 *   domain?: string | null
	 *   ip?: string | null
	 *   http_code?: number
	 *   request_method?: string | null
	 *   request_data?: unknown | null
	 *   count?: number
	 */
	public function test404ItemSchema() {
		Red_404_Log::create( 'https://example.com', '/not-found', '172.16.0.1', [
			'agent'          => 'TestAgent/1.0',
			'referrer'       => 'https://referrer.example.com',
			'request_method' => 'GET',
			'http_code'      => 404,
		] );

		$result = $this->callApi( '404' );

		$this->check_paginated_response( $result );
		$item = $this->get_item( $result );

		$this->check_id_int_or_string( $item );
		$this->assertIsInt( $item['id'], 'Individual 404 id must be an int' );

		if ( isset( $item['created'] ) ) {
			$this->assertIsString( $item['created'], 'created must be string' );
		}
		if ( isset( $item['url'] ) ) {
			$this->assertIsString( $item['url'], 'url must be string' );
		}

		// Nullable string fields
		$this->check_string_or_null( $item, 'agent' );
		$this->check_string_or_null( $item, 'referrer' );
		$this->check_string_or_null( $item, 'domain' );
		$this->check_string_or_null( $item, 'ip' );
		$this->check_string_or_null( $item, 'request_method' );

		if ( isset( $item['http_code'] ) ) {
			$this->assertIsInt( $item['http_code'], 'http_code must be int' );
		}
	}

	/**
	 * 404 entry with null agent/ip must not cause Zod validation failure.
	 */
	public function test404ItemNullFields() {
		Red_404_Log::create( 'https://example.com', '/null-fields', null, [
			'agent'    => null,
			'referrer' => null,
		] );

		$result = $this->callApi( '404' );
		$this->assertEquals( 200, $result->status );

		$item = $this->get_item( $result );

		$this->check_string_or_null( $item, 'agent' );
		$this->check_string_or_null( $item, 'ip' );
	}

	/**
	 * Grouped 404 response (groupBy=url) — id is string (URL), count is int.
	 */
	public function test404GroupedByUrlSchema() {
		Red_404_Log::create( 'https://example.com', '/grouped-404', '10.0.0.1', [] );
		Red_404_Log::create( 'https://example.com', '/grouped-404', '10.0.0.2', [] );

		$result = $this->callApi( '404', [ 'groupBy' => 'url' ] );

		$this->check_paginated_response( $result );
		$item = $this->get_item( $result );

		$this->assertIsString( $item['id'], 'Grouped 404 id must be a string (URL value)' );
		$this->assertArrayHasKey( 'count', $item );
		$this->assertIsInt( $item['count'] );
	}

	/**
	 * Grouped 404 response (groupBy=agent) with null agent — id must be string, not null.
	 * This was the original source of Zod validation failures ("items.N.id: Invalid input").
	 */
	public function test404GroupedByAgentNullId() {
		Red_404_Log::create( 'https://example.com', '/url-a', '10.0.0.1', [ 'agent' => null ] );
		Red_404_Log::create( 'https://example.com', '/url-b', '10.0.0.2', [ 'agent' => null ] );

		$result = $this->callApi( '404', [ 'groupBy' => 'agent' ] );

		$this->check_paginated_response( $result );

		$data = (array) $result->data;
		foreach ( $data['items'] as $raw_item ) {
			$item = (array) $raw_item;
			$this->assertTrue(
				is_int( $item['id'] ) || is_string( $item['id'] ),
				'Grouped agent id must be int|string (not null), got: ' . gettype( $item['id'] )
			);
		}
	}

	// -------------------------------------------------------------------------
	// Redirect item schema tests
	// -------------------------------------------------------------------------

	/**
	 * Redirect list response must match RedirectSchema:
	 *   id: number (int)
	 *   url: string
	 *   match_url?: string
	 *   match_type: string
	 *   action_type: string
	 *   action_code: number (int)
	 *   action_data: unknown
	 *   match_data?: { source?: { flag_regex, flag_trailing, flag_case, flag_query }, options?: {} } | null
	 *   group_id: number (int)
	 *   title: string
	 *   position: number (int, >= 0)
	 *   regex?: boolean
	 *   last_access?: string
	 *   enabled?: boolean
	 *   hits?: number (int, >= 0)
	 */
	public function testRedirectItemSchema() {
		Red_Item::create( [
			'url'         => '/schema-test',
			'group_id'    => $this->group->get_id(),
			'action_type' => 'url',
			'action_data' => [ 'url' => '/destination' ],
			'match_type'  => 'url',
			'title'       => 'Schema Test',
		] );

		$result = $this->callApi( 'redirect' );

		$this->check_paginated_response( $result );
		$item = $this->get_item( $result );

		$this->check_int_field( $item, 'id' );
		$this->check_string_field( $item, 'url' );
		$this->check_string_field( $item, 'match_type' );
		$this->check_string_field( $item, 'action_type' );
		$this->check_int_field( $item, 'action_code' );
		$this->check_int_field( $item, 'group_id' );
		$this->check_string_field( $item, 'title' );
		$this->check_int_field( $item, 'position' );
		$this->assertGreaterThanOrEqual( 0, $item['position'] );

		// action_data can be any type (unknown in Zod) — just check it's present
		$this->assertArrayHasKey( 'action_data', $item, "'action_data' must be present" );

		if ( isset( $item['regex'] ) ) {
			$this->assertIsBool( $item['regex'], 'regex must be bool' );
		}
		if ( isset( $item['enabled'] ) ) {
			$this->assertIsBool( $item['enabled'], 'enabled must be bool' );
		}
		if ( isset( $item['hits'] ) ) {
			$this->assertIsInt( $item['hits'], 'hits must be int' );
			$this->assertGreaterThanOrEqual( 0, $item['hits'] );
		}
		if ( isset( $item['last_access'] ) ) {
			$this->assertIsString( $item['last_access'], 'last_access must be string' );
		}
	}

	/**
	 * match_data must be null or an object with optional 'source' and 'options' keys.
	 * RedirectMatchDataSchema: { source?: { flag_regex, flag_trailing, flag_case, flag_query }, options?: {} }
	 */
	public function testRedirectMatchDataSchema() {
		Red_Item::create( [
			'url'         => '/match-data-test',
			'group_id'    => $this->group->get_id(),
			'action_type' => 'url',
			'action_data' => [ 'url' => '/dest' ],
			'match_type'  => 'url',
			'title'       => '',
		] );

		$result = $this->callApi( 'redirect' );
		$this->check_paginated_response( $result );

		$item = $this->get_item( $result );

		$this->assertArrayHasKey( 'match_data', $item, "'match_data' must be present" );

		$match_data = isset( $item['match_data'] ) ? (array) $item['match_data'] : null;

		if ( $match_data !== null ) {
			// If source is present, it must have the required boolean/string flags
			if ( isset( $match_data['source'] ) ) {
				$source = (array) $match_data['source'];
				$this->assertArrayHasKey( 'flag_regex', $source );
				$this->assertArrayHasKey( 'flag_trailing', $source );
				$this->assertArrayHasKey( 'flag_case', $source );
				$this->assertArrayHasKey( 'flag_query', $source );
				$this->assertIsBool( $source['flag_regex'], 'flag_regex must be bool' );
				$this->assertIsBool( $source['flag_trailing'], 'flag_trailing must be bool' );
				$this->assertIsBool( $source['flag_case'], 'flag_case must be bool' );
				$this->assertIsString( $source['flag_query'], 'flag_query must be string' );
			}

			if ( isset( $match_data['options'] ) ) {
				$this->assertIsArray( (array) $match_data['options'], 'match_data.options must be an object' );
			}
		}
	}

	/**
	 * A redirect with a non-url match type (referrer) must still return valid match_data.
	 * The match_data.source.flag_query must be a string, not null or a boolean.
	 */
	public function testRedirectReferrerMatchDataSchema() {
		Red_Item::create( [
			'url'         => '/referrer-match',
			'group_id'    => $this->group->get_id(),
			'action_type' => 'url',
			'action_data' => [
				'url'         => '/dest',
				'url_from'    => '/from',
				'url_notfrom' => '/notfrom',
			],
			'match_type'  => 'referrer',
			'title'       => '',
		] );

		$result = $this->callApi( 'redirect' );
		$this->check_paginated_response( $result );

		$item = $this->get_item( $result );

		$this->assertArrayHasKey( 'match_data', $item );

		if ( $item['match_data'] !== null && isset( $item['match_data'] ) ) {
			$match_data = (array) $item['match_data'];
			if ( isset( $match_data['source'] ) ) {
				$source = (array) $match_data['source'];
				$this->assertIsString( $source['flag_query'], 'flag_query must be a string, not bool/null' );
			}
		}
	}

	// -------------------------------------------------------------------------
	// Group schema tests
	// -------------------------------------------------------------------------

	/**
	 * Group list response must match GroupSchema:
	 *   id: number, name: string, moduleName: string, module_id: number,
	 *   enabled: boolean, redirects: number
	 */
	public function testGroupListSchema() {
		$result = $this->callApi( 'group' );
		$this->check_paginated_response( $result );

		$item = $this->get_item( $result );

		$this->check_int_field( $item, 'id' );
		$this->check_string_field( $item, 'name' );
		$this->check_string_field( $item, 'moduleName' );
		$this->check_int_field( $item, 'module_id' );
		$this->assertArrayHasKey( 'enabled', $item, "Field 'enabled' missing from group response" );
		$this->assertIsBool( $item['enabled'], "Field 'enabled' must be a bool" );
		$this->assertArrayHasKey( 'redirects', $item, "Field 'redirects' missing from group response" );
		$this->assertIsInt( $item['redirects'], "Field 'redirects' must be an int" );
	}

	/**
	 * Group create response must return a paginated list { items, total }, NOT { item }.
	 * Regression test: frontend useGroupCreate previously tried to parse the response
	 * as GroupItemResponseSchema({ item }) causing "item: expected object, received undefined".
	 */
	public function testGroupCreateResponseShape() {
		$result = $this->callApi( 'group', [ 'name' => 'new-test-group', 'moduleId' => 1 ], 'POST' );

		$this->assertEquals( 200, $result->status, 'Expected 200 response from group create' );

		$data = (array) $result->data;
		$this->assertArrayHasKey( 'items', $data, "Group create must return 'items' array, not 'item'" );
		$this->assertArrayHasKey( 'total', $data, "Group create must return 'total'" );
		$this->assertArrayNotHasKey( 'item', $data, "Group create must NOT return a single 'item' key" );
		$this->assertIsArray( $data['items'], "'items' must be an array" );
		$this->assertIsInt( $data['total'], "'total' must be an int" );

		$names = array_column( array_map( function ( $item ) { return (array) $item; }, $data['items'] ), 'name' );
		$this->assertContains( 'new-test-group', $names, 'Newly created group must appear in the returned items' );
	}

	/**
	 * Group update response must return a single item { item }, matching GroupItemResponseSchema.
	 */
	public function testGroupUpdateResponseShape() {
		$group = Red_Group::create( 'update-test-group', 1 );
		$result = $this->callApi( 'group/' . $group->get_id(), [ 'name' => 'updated-name', 'moduleId' => 1 ], 'POST' );

		$this->assertEquals( 200, $result->status, 'Expected 200 response from group update' );

		$data = (array) $result->data;
		$this->assertArrayHasKey( 'item', $data, "Group update must return a single 'item'" );
		$item = (array) $data['item'];
		$this->check_int_field( $item, 'id' );
		$this->check_string_field( $item, 'name' );
		$this->assertEquals( 'updated-name', $item['name'], 'Updated name must be reflected in response' );
	}

	// -------------------------------------------------------------------------
	// Paginated response envelope tests
	// -------------------------------------------------------------------------

	/**
	 * The paginated wrapper must always contain 'items' (array) and 'total' (int).
	 * This matches PaginatedResponseSchema on the frontend.
	 */
	public function testPaginatedEnvelopeLog() {
		$result = $this->callApi( 'log' );
		$this->check_paginated_response( $result );
	}

	public function testPaginatedEnvelope404() {
		$result = $this->callApi( '404' );
		$this->check_paginated_response( $result );
	}

	public function testPaginatedEnvelopeRedirect() {
		$result = $this->callApi( 'redirect' );
		$this->check_paginated_response( $result );
	}
}
