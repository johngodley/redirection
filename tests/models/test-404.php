<?php

class R404_Test extends WP_UnitTestCase {
	public function testUrlOnly() {
		$log = RE_404::create( 'url', false, '192.1.1.1', false );
		$this->assertTrue( $log !== false );
	}

	public function testLongUrl() {
		$log = RE_404::create( str_repeat( 'a', 500 ), false, false, false );
		$this->assertTrue( $log !== false );
	}

	public function testLongAgent() {
		$log = RE_404::create( 'url', str_repeat( 'a', 500 ), false, false );
		$this->assertTrue( $log !== false );
	}

	public function testLongReferrer() {
		$log = RE_404::create( 'url', false, false, str_repeat( 'a', 500 ) );
		$this->assertTrue( $log !== false );
	}

	public function testIP4() {
		$log = RE_404::create( 'url', false, '192.168.1.1', false );
		$log = RE_404::get_by_id( $log );
		$this->assertEquals( '192.168.1.1', $log->ip );
	}

	public function testIP6() {
		$log = RE_404::create( 'url', false, '2001:db8:1234:0000:0000:0000:0000:0000', false );
		$log = RE_404::get_by_id( $log );
		$this->assertEquals( '2001:db8:1234:0000:0000:0000:0000:0000', $log->ip );
	}
}
