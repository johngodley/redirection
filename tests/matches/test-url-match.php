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

	public function testBadData() {
		$match = new URL_Match();
		$saved = 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}';
		$this->assertEquals( $saved, $match->save( array( 'action_data' => 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}' ) ) );
	}

	public function testLoadBad() {
		$match = new URL_Match();
		$match->load( 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}' );
		$this->assertEquals( 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', $match->url );
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
