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
}
