<?php

class ActionUrlTest extends WP_UnitTestCase {
	private $target = false;
	private $code = false;

	public function hook_redirect( $target, $code ) {
		$this->target = $target;
		$this->code = $code;

		return false;
	}

	public function testIsFile() {
		add_filter( 'wp_redirect', array( $this, 'hook_redirect' ), 10, 2 );

		$action = Red_Action::create( 'url', 301 );
		$action->set_target( '/new' );
		$action->run();

		$this->assertEquals( 301, $this->code );
		$this->assertEquals( '/new', $this->target );
	}
}
