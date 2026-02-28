<?php

class RedirectionApi404Test extends Redirection_Api_Test {
	public static function setupBeforeClass(): void {
		global $wpdb;

		$wpdb->query( "TRUNCATE {$wpdb->prefix}redirection_404" );
	}

	private function isAthenB( $result ) {
		$this->assertEquals( 'test1', $result['items'][0]['url'] );
		$this->assertEquals( 'test2', $result['items'][1]['url'] );
	}

	private function isBthenA( $result ) {
		$this->assertEquals( 'test2', $result['items'][0]['url'] );
		$this->assertEquals( 'test1', $result['items'][1]['url'] );
	}

	private function createAB( $total = 2 ) {
		for ( $i = 0; $i < $total; $i++ ) {
			$details = [
				'agent' => 'agent' . $i,
				'referrer' => 'referrer' . $i,
				'request_method' => 'get',
				'request_data' => 'data ' . $i,
				'http_code' => 404 + $i,
			];
			Red_404_Log::create( 'domain', 'test' . ( $i + 1 ), '192.168.1.' . ( $i + 1 ), $details );
		}

		$this->setNonce();
	}

	private function get_endpoints() {
		return [
			[ '404', 'GET', [] ],
			[ 'bulk/404/delete', 'POST', [ 'items' => [] ] ],
			[ 'bulk/404/delete', 'POST', [] ],
		];
	}

	public function testNoPermission() {
		$this->setUnauthorised();

		// None of these should work
		$this->check_endpoints( $this->get_endpoints() );
	}

	public function testEditorPermission() {
		// Everything else is 403
		$working = [
			Redirection_Capabilities::CAP_404_MANAGE => [ [ '404', 'GET' ] ],
			Redirection_Capabilities::CAP_404_DELETE => [
				[ 'bulk/404/delete', 'POST' ],
			],
		];

		$this->setEditor();

		foreach ( $working as $cap => $working_caps ) {
			$this->add_capability( $cap );
			$this->check_endpoints( $this->get_endpoints(), $working_caps );
			$this->clear_capability();
		}
	}

	public function testAdminPermission() {
		// All of these should work
		$this->check_endpoints( $this->get_endpoints(), $this->get_endpoints() );
	}

	public function testListBadOrderBy() {
		$this->createAB();

		$result = $this->callApi( '404', array( 'orderby' => 'cats' ) );

		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testListBadDirection() {
		$this->createAB();
		$result = $this->callApi( '404', array( 'direction' => 'cats' ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testGet404() {
		$this->createAB();

		$result = $this->callApi( '404' );

		$this->isBthenA( $result->data );
	}

	public function testListBadPerPage() {
		$this->createAB( 5 );

		$result = $this->callApi( '404', array( 'per_page' => 'cats' ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testListPerPageMin() {
		$this->createAB( 20 );

		$result = $this->callApi( '404', array( 'per_page' => 5 ) );
		$this->assertEquals( 5, count( $result->data['items'] ) );
	}

	public function testListPerPageMax() {
		$this->createAB( 201 );

		$result = $this->callApi( '404', array( 'per_page' => 201 ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testListBadPage() {
		$this->createAB( 20 );

		$result = $this->callApi( '404', array( 'per_page' => 10, 'page' => 'cats' ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testBadFilter() {
		$this->createAB();

		$result = $this->callApi( '404', array( 'filterBy' => 'cats' ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );

		$result = $this->callApi( '404', [ 'filterBy' => [ 'cats' => 'thing' ] ] );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testIPFilter() {
		$this->createAB( 5 );

		$result = $this->callApi( '404', array( 'filterBy' => [ 'ip' => '192.168.1.1' ] ) );
		$this->assertEquals( 1, count( $result->data['items'] ) );

		$result = $this->callApi( '404', array( 'filterBy' => [ 'ip' => '192.168.1' ] ) );
		$this->assertEquals( 5, count( $result->data['items'] ) );
	}

	public function testUrlFilter() {
		$this->createAB( 5 );

		$result = $this->callApi( '404', array( 'filterBy' => [ 'url' => 'test1' ] ) );
		$this->assertEquals( 1, count( $result->data['items'] ) );
	}

	public function testReferrerFilter() {
		$this->createAB( 5 );

		$result = $this->callApi( '404', array( 'filterBy' => [ 'referrer' => 'referrer1' ] ) );
		$this->assertEquals( 1, count( $result->data['items'] ) );
	}

	public function testUserAgentFilter() {
		$this->createAB( 5 );

		$result = $this->callApi( '404', array( 'filterBy' => [ 'agent' => 'agent1' ] ) );
		$this->assertEquals( 1, count( $result->data['items'] ) );
	}

	public function testBadBulk() {
		$this->setNonce();
		$this->createAB();
		$group = Red_Group::create( 'test', 1 );

		$result = $this->callApi( 'bulk/404/cats', array( 'items' => '1' ), 'POST' );
		$this->assertEquals( 'rest_no_route', $result->data['code'] );
	}

	public function testDeleteBulkItem() {
		$this->setNonce();
		Red_404_Log::create( 'test1', 'agent', '192.168.1.1', [ 'referrer' => 'referrer' ] );
		$last = Red_404_Log::create( 'test2', 'agent', '192.168.1.2', [ 'referrer' => 'referrer' ] );

		$result = $this->callApi( 'bulk/404/delete', array( 'items' => $last ), 'POST' );
		$this->assertEquals( 1, count( $result->data['items'] ) );
	}

	public function testDeleteBulkFilter() {
		$this->setNonce();

		Red_404_Log::create( 'test1', 'agent', '192.168.1.1', [ 'referrer' => 'referrer1' ] );
		Red_404_Log::create( 'test2', 'agent', '192.168.1.2', [ 'referrer' => 'referrer2' ] );
		Red_404_Log::create( 'test2', 'agent', '192.168.1.2', [ 'referrer' => 'referrer3' ] );

		$result = $this->callApi( 'bulk/404/delete', [ 'global' => true, 'filterBy' => [ 'referrer' => 'referrer2' ] ], 'POST' );
		$result = $this->callApi( '404' );

		$this->assertEquals( 2, count( $result->data['items'] ) );
	}

	public function testDeleteBulkAllNoFilter() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->prefix}redirection_404" );
		$this->setNonce();

		Red_404_Log::create( 'domain', '/page1', '192.168.1.1', [] );
		Red_404_Log::create( 'domain', '/page2', '192.168.1.2', [] );
		Red_404_Log::create( 'domain', '/page3', '192.168.1.3', [] );

		// global=true with no filterBy — the "select all and delete" case
		$result = $this->callApi( 'bulk/404/delete', [ 'global' => true ], 'POST' );
		$this->assertEquals( 200, $result->status );

		$result = $this->callApi( '404' );
		$this->assertEquals( 0, count( $result->data['items'] ) );
	}

	public function testDeleteBulkEmptyFilterByIsAccepted() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->prefix}redirection_404" );
		$this->setNonce();

		Red_404_Log::create( 'domain', '/page1', '192.168.1.1', [] );

		// filterBy sent as empty string (client serialisation quirk) is treated as "no filter"
		$result = $this->callApi( 'bulk/404/delete', [ 'global' => true, 'filterBy' => '' ], 'POST' );
		$this->assertEquals( 200, $result->status );

		// Verify that the 404 entry was actually deleted (empty filterBy behaves as no filter)
		$result = $this->callApi( '404' );
		$this->assertEquals( 0, count( $result->data['items'] ) );
	}

	public function testDeleteBulkGroupedByUrl() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->prefix}redirection_404" );
		$this->setNonce();

		// Create multiple 404 entries with the same URL but different IPs
		Red_404_Log::create( 'domain', '/missing-page', '192.168.1.1', [ 'agent' => 'agent1', 'referrer' => 'ref1' ] );
		Red_404_Log::create( 'domain', '/missing-page', '192.168.1.2', [ 'agent' => 'agent2', 'referrer' => 'ref2' ] );
		Red_404_Log::create( 'domain', '/another-404', '192.168.1.3', [ 'agent' => 'agent3', 'referrer' => 'ref3' ] );
		Red_404_Log::create( 'domain', '/third-404', '192.168.1.4', [ 'agent' => 'agent4', 'referrer' => 'ref4' ] );

		// Delete grouped by URL - should delete all entries with /missing-page
		$result = $this->callApi( 'bulk/404/delete', [ 'items' => [ '/missing-page' ], 'groupBy' => 'url' ], 'POST' );
		$result = $this->callApi( '404' );

		// Should have 2 remaining entries
		$this->assertEquals( 2, count( $result->data['items'] ) );
		$this->assertEquals( '/third-404', $result->data['items'][0]['url'] );
		$this->assertEquals( '/another-404', $result->data['items'][1]['url'] );
	}

	public function testDeleteBulkGroupedByIp() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->prefix}redirection_404" );
		$this->setNonce();

		// Create multiple 404 entries with the same IP but different URLs
		Red_404_Log::create( 'domain', '/url1', '192.168.1.100', [ 'agent' => 'agent1', 'referrer' => 'ref1' ] );
		Red_404_Log::create( 'domain', '/url2', '192.168.1.100', [ 'agent' => 'agent2', 'referrer' => 'ref2' ] );
		Red_404_Log::create( 'domain', '/url3', '192.168.1.200', [ 'agent' => 'agent3', 'referrer' => 'ref3' ] );
		Red_404_Log::create( 'domain', '/url4', '192.168.1.300', [ 'agent' => 'agent4', 'referrer' => 'ref4' ] );

		// Delete grouped by IP - should delete all entries with 192.168.1.100
		$result = $this->callApi( 'bulk/404/delete', [ 'items' => [ '192.168.1.100' ], 'groupBy' => 'ip' ], 'POST' );
		$result = $this->callApi( '404' );

		// Should have 2 remaining entries
		$this->assertEquals( 2, count( $result->data['items'] ) );
		$this->assertEquals( '192.168.1.300', $result->data['items'][0]['ip'] );
		$this->assertEquals( '192.168.1.200', $result->data['items'][1]['ip'] );
	}

	public function testDeleteBulkGroupedByAgent() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->prefix}redirection_404" );
		$this->setNonce();

		// Create multiple 404 entries with the same agent but different URLs
		Red_404_Log::create( 'domain', '/url1', '192.168.1.1', [ 'agent' => 'Mozilla/5.0 Chrome', 'referrer' => 'ref1' ] );
		Red_404_Log::create( 'domain', '/url2', '192.168.1.2', [ 'agent' => 'Mozilla/5.0 Chrome', 'referrer' => 'ref2' ] );
		Red_404_Log::create( 'domain', '/url3', '192.168.1.3', [ 'agent' => 'Mozilla/5.0 Firefox', 'referrer' => 'ref3' ] );
		Red_404_Log::create( 'domain', '/url4', '192.168.1.4', [ 'agent' => 'Safari', 'referrer' => 'ref4' ] );

		// Delete grouped by agent - should delete all entries with Mozilla/5.0 Chrome
		$result = $this->callApi( 'bulk/404/delete', [ 'items' => [ 'Mozilla/5.0 Chrome' ], 'groupBy' => 'agent' ], 'POST' );
		$result = $this->callApi( '404' );

		// Should have 2 remaining entries
		$this->assertEquals( 2, count( $result->data['items'] ) );
		$this->assertEquals( 'Safari', $result->data['items'][0]['agent'] );
		$this->assertEquals( 'Mozilla/5.0 Firefox', $result->data['items'][1]['agent'] );
	}

	public function testDeleteMultipleBulkGroupedByUrl() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->prefix}redirection_404" );
		$this->setNonce();

		// Create entries for multiple URLs
		Red_404_Log::create( 'domain', '/delete-me-1', '192.168.1.1', [ 'agent' => 'agent1', 'referrer' => 'ref1' ] );
		Red_404_Log::create( 'domain', '/delete-me-1', '192.168.1.2', [ 'agent' => 'agent2', 'referrer' => 'ref2' ] );
		Red_404_Log::create( 'domain', '/delete-me-2', '192.168.1.3', [ 'agent' => 'agent3', 'referrer' => 'ref3' ] );
		Red_404_Log::create( 'domain', '/keep-me', '192.168.1.4', [ 'agent' => 'agent4', 'referrer' => 'ref4' ] );

		// Delete multiple URLs at once
		$result = $this->callApi( 'bulk/404/delete', [
			'items' => [ '/delete-me-1', '/delete-me-2' ],
			'groupBy' => 'url'
		], 'POST' );
		$result = $this->callApi( '404' );

		// Should have 1 remaining entry
		$this->assertEquals( 1, count( $result->data['items'] ) );
		$this->assertEquals( '/keep-me', $result->data['items'][0]['url'] );
	}
}
