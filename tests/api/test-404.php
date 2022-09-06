<?php

use Redirection\Log;
use Redirection\Plugin;
use Redirection\Group;
use Redirection\Redirect;

class RedirectionApi404Test extends Redirection_Api_Test {
	public static function setupBeforeClass() : void {
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
			Log\Error::create( 'domain', 'test' . ( $i + 1 ), '192.168.1.' . ( $i + 1 ), $details );
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
			Plugin\Capabilities::CAP_404_MANAGE => [ [ '404', 'GET' ] ],
			Plugin\Capabilities::CAP_404_DELETE => [
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
		$group = Group\Group::create( 'test', 1 );

		$result = $this->callApi( 'bulk/404/cats', array( 'items' => '1' ), 'POST' );
		$this->assertEquals( 'rest_no_route', $result->data['code'] );
	}

	public function testDeleteBulkItem() {
		$this->setNonce();
		Log\Error::create( 'test1', 'agent', '192.168.1.1', [ 'referrer' => 'referrer' ] );
		$last = Log\Error::create( 'test2', 'agent', '192.168.1.2', [ 'referrer' => 'referrer' ] );

		$result = $this->callApi( 'bulk/404/delete', array( 'items' => $last ), 'POST' );
		$this->assertEquals( 1, count( $result->data['items'] ) );
	}

	public function testDeleteBulkFilter() {
		$this->setNonce();

		Log\Error::create( 'test1', 'agent', '192.168.1.1', [ 'referrer' => 'referrer1' ] );
		Log\Error::create( 'test2', 'agent', '192.168.1.2', [ 'referrer' => 'referrer2' ] );
		Log\Error::create( 'test2', 'agent', '192.168.1.2', [ 'referrer' => 'referrer3' ] );

		$result = $this->callApi( 'bulk/404/delete', [ 'global' => true, 'filterBy' => [ 'referrer' => 'referrer2' ] ], 'POST' );
		$result = $this->callApi( '404' );

		$this->assertEquals( 2, count( $result->data['items'] ) );
	}
}
