<?php

class R404_Test extends WP_UnitTestCase {
	public function testUrlOnly() {
		$log = Red_404_Log::create( 'url', false, '192.1.1.1', false );
		$this->assertTrue( $log !== false );
	}

	public function testLongUrl() {
		$log = Red_404_Log::create( str_repeat( 'a', 500 ), false, false, false );
		$this->assertTrue( $log !== false );
	}

	public function testLongAgent() {
		$log = Red_404_Log::create( 'url', str_repeat( 'a', 500 ), false, false );
		$this->assertTrue( $log !== false );
	}

	public function testLongReferrer() {
		$log = Red_404_Log::create( 'url', false, false, str_repeat( 'a', 500 ) );
		$this->assertTrue( $log !== false );
	}

	public function testIP4() {
		$log = Red_404_Log::create( 'url', false, '192.168.1.1', false );
		$log = Red_404_Log::get_by_id( $log );
		$this->assertEquals( '192.168.1.1', $log->ip );
	}

	public function testIP6() {
		$log = Red_404_Log::create( 'url', false, '2001:db8:1234:0000:0000:0000:0000:0000', false );
		$log = Red_404_Log::get_by_id( $log );
		$this->assertEquals( '2001:db8:1234:0000:0000:0000:0000:0000', $log->ip );
	}
}
