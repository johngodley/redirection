<?php

class RedirectionApiLogTest extends Redirection_Api_Test {
	public static function setupBeforeClass(): void {
		global $wpdb;

		$wpdb->query( "TRUNCATE {$wpdb->prefix}redirection_logs" );
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
				'target' => 'target' . $i,
				'referrer' => 'referrer' . $i,
			];

			Red_Redirect_Log::create( 'domain', 'test' . ( $i + 1 ), '192.168.1.' . ( $i + 1 ), $details );
		}

		$this->setNonce();
	}

	private function get_endpoints() {
		return [
			[ 'log', 'GET', [] ],
			[ 'bulk/log/delete', 'POST', [ 'items' => [] ] ],
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
			Redirection_Capabilities::CAP_LOG_MANAGE => [ [ 'log', 'GET' ] ],
			Redirection_Capabilities::CAP_LOG_DELETE => [
				[ 'bulk/log/delete', 'POST' ],
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

		$result = $this->callApi( 'log', array( 'orderby' => 'cats' ) );

		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testListBadDirection() {
		$this->createAB();
		$result = $this->callApi( 'log', array( 'direction' => 'cats' ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testGetlog() {
		$this->createAB();

		$result = $this->callApi( 'log' );

		$this->isBthenA( $result->data );
	}

	public function testListBadPerPage() {
		$this->createAB( 5 );

		$result = $this->callApi( 'log', array( 'per_page' => 'cats' ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testListPerPageMin() {
		$this->createAB( 20 );

		$result = $this->callApi( 'log', array( 'per_page' => 5 ) );
		$this->assertEquals( 5, count( $result->data['items'] ) );
	}

	public function testListPerPageMax() {
		$this->createAB( 201 );

		$result = $this->callApi( 'log', array( 'per_page' => 201 ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testListBadPage() {
		$this->createAB( 20 );

		$result = $this->callApi( 'log', array( 'per_page' => 10, 'page' => 'cats' ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testBadFilter() {
		$this->createAB();

		$result = $this->callApi( 'log', array( 'filterBy' => 'cats' ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );

		$result = $this->callApi( 'log', [ 'filterBy' => [ 'cats' => 'thing' ] ] );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}


	public function testIPFilter() {
		$this->createAB( 5 );

		$result = $this->callApi( 'log', array( 'filterBy' => [ 'ip' => '192.168.1.1' ] ) );
		$this->assertEquals( 1, count( $result->data['items'] ) );

		$result = $this->callApi( 'log', array( 'filterBy' => [ 'ip' => '192.168.1' ] ) );
		$this->assertEquals( 5, count( $result->data['items'] ) );
	}

	public function testUrlFilter() {
		$this->createAB( 5 );

		$result = $this->callApi( 'log', array( 'filterBy' => [ 'url' => 'test1' ] ) );
		$this->assertEquals( 1, count( $result->data['items'] ) );
	}

	public function testReferrerFilter() {
		$this->createAB( 5 );

		$result = $this->callApi( 'log', array( 'filterBy' => [ 'referrer' => 'referrer1' ] ) );
		$this->assertEquals( 1, count( $result->data['items'] ) );
	}

	public function testUserAgentFilter() {
		$this->createAB( 5 );

		$result = $this->callApi( 'log', array( 'filterBy' => [ 'agent' => 'agent1' ] ) );
		$this->assertEquals( 1, count( $result->data['items'] ) );
	}

	public function testTargetFilter() {
		$this->createAB( 5 );

		$result = $this->callApi( 'log', array( 'filterBy' => [ 'target' => 'target1' ] ) );
		$this->assertEquals( 1, count( $result->data['items'] ) );
	}

	public function testBadBulk() {
		$this->createAB();
		$group = Red_Group::create( 'test', 1 );

		$result = $this->callApi( 'bulk/log/cats', array( 'items' => '1' ), 'POST' );
		$this->assertEquals( 'rest_no_route', $result->data['code'] );
	}

	public function testDeleteBulkItem() {
		$this->setNonce();
		Red_Redirect_Log::create( 'test1', 'agent', '192.168.1.1', [ 'referrer' => 'referrer' ] );
		$last = Red_Redirect_Log::create( 'test2', 'agent', '192.168.1.2', [ 'referrer' => 'referrer' ] );

		$result = $this->callApi( 'bulk/log/delete', array( 'items' => $last ), 'POST' );
		$this->assertEquals( 1, count( $result->data['items'] ) );
	}

	public function testDeleteBulkFilter() {
		$this->setNonce();

		Red_Redirect_Log::create( 'test1', 'agent', '192.168.1.1', [ 'referrer' => 'referrer1' ] );
		Red_Redirect_Log::create( 'test2', 'agent', '192.168.1.2', [ 'referrer' => 'referrer2' ] );
		Red_Redirect_Log::create( 'test2', 'agent', '192.168.1.2', [ 'referrer' => 'referrer3' ] );

		$result = $this->callApi( 'bulk/log/delete', [ 'global' => true, 'filterBy' => [ 'referrer' => 'referrer2' ] ], 'POST' );
		$result = $this->callApi( 'log' );

		$this->assertEquals( 2, count( $result->data['items'] ) );
	}
}
