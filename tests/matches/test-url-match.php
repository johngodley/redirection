<?php

class UrlMatchTest extends WP_UnitTestCase {
	public function testTargetSanitized() {
		$match = new URL_Match();
		$saved = array( 'url' => '/some/url' );

		$this->assertEquals( $match->save( array( 'target' => "/some/url\nsomethingelse1" ) ), $saved );
		$this->assertEquals( $match->save( array( 'target' => "/some/url\rsomethingelse2" ) ), $saved );
		$this->assertEquals( $match->save( array( 'target' => "/some/url\r\nsomethingelse3" ) ), $saved );
	}

	public function testDefaultSlash() {
		$match = new URL_Match();

		$this->assertEquals( $match->save( array() ), array( 'url' => '/' ) );
	}
}
