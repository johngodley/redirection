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

	public function testFile() {
		$action = Red_Action::create( 'pass', 1 );

 		ob_start();
		$action->process_file( 'file://' . dirname( __FILE__ ) . '/fixtures/file-pass.php' );
		$file = ob_get_contents();
		ob_end_clean();

		$this->assertEquals( 'PASS', $file );
	}

	// public function testExternal() {
	// 	$action = Red_Action::create( 'pass', 1 );

	// 	ob_start();
	// 	$action->process_external( 'http://apple.com/robots.txt' );
	// 	$file = ob_get_contents();
	// 	ob_end_clean();

	// 	$this->assertTrue( strlen( $file ) !== 0 );
	// }

	public function testInternal() {
		$action = Red_Action::create( 'pass', 1 );

		$action->process_internal( 'test.php?thing=1' );

		$this->assertEquals( 'test.php?thing=1', $_SERVER['REQUEST_URI'] );
		$this->assertEquals( array( 'thing' => '1' ), $_GET );
	}
}
