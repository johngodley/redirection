<?php

class RedirectionApiLogTest extends WP_Ajax_UnitTestCase {
	public static $redirection;

	public function setUp() {
		parent::setUp();
		RE_Log::delete_all();
	}

	private function get_logs( $params = array() ) {
		return json_decode( self::$redirection->ajax_get_logs( $params ) );
	}

	public static function setupBeforeClass() {
		self::$redirection = Redirection_Admin::init()->api;
	}

	private function setNonce() {
		$this->_setRole( 'administrator' );
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'wp_rest' );
	}

	public function testLogNoData() {
		$this->setNonce();
		$result = $this->get_logs();

		$this->assertEquals( array(), $result->items );
		$this->assertEquals( 0, $result->total );
	}

	public function testBadTypeDefaultsLog() {
		RE_Log::create( 'test', 'target', 'agent', '192.168.1.1', 'referrer' );

		$this->setNonce();
		$result = $this->get_logs( array( 'logType' => 'cats' ) );

		$this->assertTrue( is_array( $result->items ) );
		$this->assertEquals( 1, $result->total );
	}

	private function isAthenB( $result ) {
		$this->assertEquals( 'test1', $result->items[ 0 ]->url );
		$this->assertEquals( 'test2', $result->items[ 1 ]->url );
	}

	private function isBthenA( $result ) {
		$this->assertEquals( 'test2', $result->items[ 0 ]->url );
		$this->assertEquals( 'test1', $result->items[ 1 ]->url );
	}

	private function createAB( $total = 2 ) {
		for ( $i = 0; $i < $total; $i++ ) {
			RE_Log::create( 'test'.( $i + 1 ), 'target', 'agent', '192.168.1.'.( $i + 1 ), 'referrer' );
		}

		$this->setNonce();
	}

	public function testOrderByDate() {
		$this->createAB();

		$result = $this->get_logs();

		$this->isBthenA( $result );
	}

	public function testOrderByDateAsc() {
		$this->createAB();

		$result = $this->get_logs( array( 'direction' => 'asc' ) );

		$this->isAthenB( $result );
	}

	public function testOrderByURL() {
		$this->createAB();

		$result = $this->get_logs( array( 'orderBy' => 'url' ) );

		$this->isBthenA( $result );
	}

	public function testOrderByURLAsc() {
		$this->createAB();

		$result = $this->get_logs( array( 'orderBy' => 'url', 'direction' => 'asc' ) );

		$this->isAthenB( $result );
	}

	public function testOrderByIP() {
		$this->createAB();

		$result = $this->get_logs( array( 'orderBy' => 'ip' ) );

		$this->isBthenA( $result );
	}

	public function testOrderByIPAsc() {
		$this->createAB();

		$result = $this->get_logs( array( 'orderBy' => 'ip', 'direction' => 'asc' ) );

		$this->isAthenB( $result );
	}

	public function testOrderBadDefaultsDate() {
		$this->createAB();

		$result = $this->get_logs( array( 'orderBy' => 'cats' ) );

		$this->isBthenA( $result );
	}

	public function testDirectionBadDefaultsDesc() {
		$this->createAB();

		$result = $this->get_logs( array( 'direction' => 'cats' ) );

		$this->isBthenA( $result );
	}

	public function testPerPageMin() {
		$this->createAB( 12 );

		$result = $this->get_logs( array( 'perPage' => 1 ) );
		$this->assertEquals( 10, count( $result->items ) );
	}

	public function testBadPerPageDefault10() {
		$this->createAB( 20 );

		$result = $this->get_logs( array( 'perPage' => 'cats' ) );
		$this->assertEquals( 10, count( $result->items ) );
	}

	public function testPerPageMax() {
		$this->createAB( 102 );

		$result = $this->get_logs( array( 'perPage' => '101' ) );
		$this->assertEquals( 100, count( $result->items ) );
	}

	public function testPerPage() {
		$this->createAB( 22 );

		$result = $this->get_logs( array( 'perPage' => '20' ) );
		$this->assertEquals( 20, count( $result->items ) );
	}

	public function testPageMinDefault0() {
		$this->createAB( 12 );

		$result = $this->get_logs( array( 'page' => '-1', 'perPage' => '10', 'direction' => 'asc' ) );

		$this->assertEquals( 10, count( $result->items ) );
		$this->assertEquals( 'test1', $result->items[ 0 ]->url );
	}

	public function testPageBadDefault0() {
		$this->createAB( 12 );

		$result = $this->get_logs( array( 'page' => 'cats', 'perPage' => '10', 'direction' => 'asc' ) );

		$this->assertEquals( 10, count( $result->items ) );
		$this->assertEquals( 'test1', $result->items[ 0 ]->url );
	}

	public function testPage() {
		$this->createAB( 12 );

		$result = $this->get_logs( array( 'page' => '1', 'perPage' => '10', 'direction' => 'asc' ) );

		$this->assertEquals( 2, count( $result->items ) );
		$this->assertEquals( 'test11', $result->items[ 0 ]->url );
	}

	public function testEmptySearch() {
		$this->createAB();

		$result = $this->get_logs( array( 'filter' => '' ) );

		$this->assertEquals( 2, count( $result->items ) );
	}

	public function testSearch() {
		$this->createAB();

		$result = $this->get_logs( array( 'filter' => 'test1' ) );

		$this->assertEquals( 1, count( $result->items ) );
		$this->assertEquals( 'test1', $result->items[ 0 ]->url );
	}

	public function testBadSearch() {
		$this->createAB();

		$result = $this->get_logs( array( 'filter' => '"%\'cats' ) );

		$this->assertEquals( 0, count( $result->items ) );
	}

	public function testEmptyIPFilter() {
		$this->createAB();

		$result = $this->get_logs( array( 'filter' => '', 'filterBy' => 'ip' ) );

		$this->assertEquals( 2, count( $result->items ) );
	}

	public function testIPFilter() {
		$this->createAB( 5 );

		$result = $this->get_logs( array( 'filter' => '192.168.1.1', 'filterBy' => 'ip' ) );

		$this->assertEquals( 1, count( $result->items ) );
	}

	public function testBadIPFilter() {
		$this->createAB();

		$result = $this->get_logs( array( 'filter' => '"%\'cats', 'filterBy' => 'ip' ) );

		$this->assertEquals( 0, count( $result->items ) );
	}
}
