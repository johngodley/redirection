<?php

require_once dirname( __FILE__ ) . '/../../matches/url.php';

class UrlMatchTest extends WP_UnitTestCase {
	public function testTargetSanitized() {
		$match = new URL_Match();
		$saved = '/some/url';

		$this->assertEquals( $match->save( array( 'action_data' => "/some/url\nsomethingelse1" ) ), $saved );
		$this->assertEquals( $match->save( array( 'action_data' => "/some/url\rsomethingelse2" ) ), $saved );
		$this->assertEquals( $match->save( array( 'action_data' => "/some/url\r\nsomethingelse3" ) ), $saved );
	}

	public function testDefaultSlash() {
		$match = new URL_Match();

		$this->assertEquals( $match->save( array() ), '/' );
	}

	public function testTarget() {
		$match = new URL_Match( '/something' );
		$this->assertEquals( '/something', $match->get_target( '/a', '/b', false ) );
	}

	public function testRegexTarget() {
		$match = new URL_Match( '/other/$1' );
		$this->assertEquals( '/other/1', $match->get_target( '/category/1', '/category/(.*?)', true ) );
	}
}
