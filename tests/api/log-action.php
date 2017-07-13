<?php

class RedirectionApiLogActionTest extends WP_Ajax_UnitTestCase {
	public static $redirection;
	private $logs = array();

	private function do_action( $params = array() ) {
		return json_decode( self::$redirection->ajax_log_action( $params ) );
	}

	public static function setupBeforeClass() {
		self::$redirection = Redirection_Admin::init();
	}

	private function createAB( $total = 2 ) {
		$this->logs = array();

		for ( $i = 0; $i < $total; $i++ ) {
			$this->logs[] = RE_Log::create( 'test'.( $i + 1 ), 'target', 'agent', '192.168.1.'.( $i + 1 ), 'referrer' );
		}

		$this->setNonce();
	}

	private function setNonce() {
		$this->_setRole( 'administrator' );
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'wp_rest' );
	}

	public function testNonce() {
		$result = $this->do_action();
		$this->assertTrue( isset( $result->error ) );
	}

	public function testPermissions() {
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'wp_rest' );
		$result = $this->do_action();
		$this->assertTrue( isset( $result->error ) );
	}

	public function testLogNoParams() {
		$this->createAB();
		$result = $this->do_action();

		$this->assertTrue( is_array( $result->items ) );
		$this->assertEquals( 2, $result->total );
	}

	public function testLogNoIDs() {
		$this->createAB();
		$result = $this->do_action( array( 'bulk' => 'delete', 'items' => '' ) );

		$this->assertEquals( 2, $result->total );
	}

	public function testLogInvalidParams() {
		$this->createAB();
		$result = $this->do_action( array( 'bulk' => 'cats', 'items' => '' ) );

		$this->assertEquals( 2, $result->total );
	}

	public function testLogBadID() {
		$this->createAB();
		$result = $this->do_action( array( 'bulk' => 'cats', 'items' => '4,5,6' ) );

		$this->assertEquals( 2, $result->total );
	}

	public function testLogDeleteOne() {
		$this->createAB();
		$result = $this->do_action( array( 'bulk' => 'delete', 'items' => $this->logs[ 0 ] ) );

		$this->assertEquals( 1, $result->total );
	}

	public function testLogDeleteMultiple() {
		$this->createAB();
		$result = $this->do_action( array( 'bulk' => 'delete', 'items' => implode( ',', $this->logs ) ) );

		$this->assertEquals( 0, $result->total );
	}
}
