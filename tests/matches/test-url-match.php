<?php

require_once dirname( __FILE__ ) . '/../../matches/url.php';

class UrlMatchTest extends WP_UnitTestCase {
	public function testTargetSanitized() {
		$match = new URL_Match();
		$saved = '/some/url';

		$this->assertEquals( $match->save( array( 'url' => "/some/url\nsomethingelse1" ) ), $saved );
		$this->assertEquals( $match->save( array( 'url' => "/some/url\rsomethingelse2" ) ), $saved );
		$this->assertEquals( $match->save( array( 'url' => "/some/url\r\nsomethingelse3" ) ), $saved );
	}

	public function testBadData() {
		$match = new URL_Match();
		$saved = 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}';
		$this->assertEquals( $saved, $match->save( array( 'url' => 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}' ) ) );
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

	public function testMatch() {
		$match = new URL_Match( '/something' );
		$this->assertTrue( $match->is_match( '' ) );
	}

	public function testMatchedTarget() {
		$match = new URL_Match( '/url' );
		$this->assertEquals( '/url', $match->get_target_url( '', '', new Red_Source_Flags(), true ) );
	}

	public function testRegexTarget() {
		$match = new URL_Match( '/url/$1' );
		$this->assertEquals( '/url/1', $match->get_target_url( '/category/1', '/category/(.*?)', new Red_Source_Flags( [ 'flag_regex' => true ] ), true ) );
	}
}
