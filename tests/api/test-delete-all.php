<?php

class RedirectionApiDeleteAllTest extends WP_Ajax_UnitTestCase {
	public static $redirection;
	private $logs = array();

	private function do_action( $params = array() ) {
		return json_decode( self::$redirection->ajax_delete_all( $params ) );
	}

	public static function setupBeforeClass() {
		self::$redirection = Redirection_Admin::init()->api;
	}

	public function setUp() {
		parent::setUp();
		RE_Log::delete_all();
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

	public function testNoParams() {
		$this->createAB();
		$result = $this->do_action();

		$this->assertTrue( is_array( $result->items ) );
		$this->assertEquals( 2, $result->total );
	}

	public function testDelete() {
		$this->createAB();
		$result = $this->do_action( array( 'logType' => 'log' ) );

		$this->assertTrue( is_array( $result->items ) );
		$this->assertEquals( 0, $result->total );
	}

	public function testDeleteFilter() {
		$this->createAB( 4 );
		$result = $this->do_action( array( 'logType' => 'log', 'filter' => 'test1', 'filterBy' => 'url' ) );

		$this->assertTrue( is_array( $result->items ) );
		$this->assertEquals( 3, $result->total );
	}

	public function testDeleteFilterIP() {
		$this->createAB( 4 );
		$result = $this->do_action( array( 'logType' => 'log', 'filter' => '192.168.1.2', 'filterBy' => 'ip' ) );

		$this->assertTrue( is_array( $result->items ) );
		$this->assertEquals( 3, $result->total );
	}
}
