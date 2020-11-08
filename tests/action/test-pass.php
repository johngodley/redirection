<?php

class PassTest extends WP_UnitTestCase {
	public function testIsExternal() {
		$action = Red_Action::create( 'pass', 1 );
		$this->assertTrue( $action->is_external( 'http://something' ) );
		$this->assertTrue( $action->is_external( 'https://something' ) );
	}

	public function testIsNotExternal() {
		$action = Red_Action::create( 'pass', 1 );
		$this->assertFalse( $action->is_external( 'x://something' ) );
	}

	public function testInternal() {
		$action = Red_Action::create( 'pass', 1 );

		$action->process_internal( 'test.php?thing=1' );

		$this->assertEquals( 'test.php?thing=1', $_SERVER['REQUEST_URI'] );
		$this->assertEquals( array( 'thing' => '1' ), $_GET );
	}
}
