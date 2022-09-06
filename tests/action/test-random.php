<?php

use Redirection\Front;
use Redirection\Action;

class RandomTest extends WP_UnitTestCase {
	private $target = null;
	private $code = 0;

	public function hook_redirect( $target, $code ) {
		$this->target = $target;
		$this->code = $code;

		return false;
	}

	public function testRandomPost() {
		add_filter( 'wp_redirect', array( $this, 'hook_redirect' ), 10, 2 );

		$post1 = $this->factory->post->create( [ 'post_title' => 'trash me1', 'post_name' => 'post-1' ] );
		$post1 = $this->factory->post->create( [ 'post_title' => 'trash me2', 'post_name' => 'post-2' ] );

		$action = Action\Action::create( 'random', 301 );
		$action->run();

		$this->assertEquals( 301, $this->code );
		$this->assertTrue( $this->target !== null );
	}
}
