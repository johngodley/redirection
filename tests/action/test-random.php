<?php

class RandomTest extends WP_UnitTestCase {
	public function testRandomPost() {
		$post1 = $this->factory->post->create( array( 'post_title' => 'trash me1' ) );
		$post1 = $this->factory->post->create( array( 'post_title' => 'trash me2' ) );

		$action = Red_Action::create( 'random', 1 );
		$found = array();

		for ( $i = 0; $i < 10; $i++ ) {
			$url = $action->process_before( 301, '/url' );
			if ( $url ) {
				$found[$url] = true;
			}

			if ( count( $found ) > 1 ) {
				break;
			}
		}

		$this->assertEquals( 2, count( $found ) );
	}
}
