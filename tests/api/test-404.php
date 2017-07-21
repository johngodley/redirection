<?php

class RedirectionApi404Test extends WP_Ajax_UnitTestCase {
	public static $redirection;

	private function get_logs( $params = array() ) {
		return json_decode( self::$redirection->ajax_get_logs( array_merge( array( 'logType' => '404'), $params ) ) );
	}

	public static function setupBeforeClass() {
		self::$redirection = Redirection_Admin::init()->api;
	}

	private function setNonce() {
		$this->_setRole( 'administrator' );
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'wp_rest' );
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
			RE_404::create( 'test'.( $i + 1 ), 'agent', '192.168.1.'.( $i + 1 ), 'referrer' );
		}

		$this->setNonce();
	}

	public function testGet404() {
		$this->createAB();

		$result = $this->get_logs();

		$this->isBthenA( $result );
	}

	public function testIPFilter() {
		$this->createAB( 5 );

		$result = $this->get_logs( array( 'filter' => '192.168.1.1', 'filterBy' => 'ip' ) );

		$this->assertEquals( 1, count( $result->items ) );
	}

	public function testBadIPFilter() {
		$this->createAB();

		$result = $this->get_logs( array( 'filter' => 'cats', 'filterBy' => 'ip' ) );

		$this->assertEquals( 2, count( $result->items ) );
	}
}
