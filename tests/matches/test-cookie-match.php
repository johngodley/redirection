<?php

require dirname( __FILE__ ) . '/../../matches/cookie.php';

class CookieMatchTest extends WP_UnitTestCase {
	public function testTargetSanitized() {
		$match = new Cookie_Match();
		$saved = array(
			'url_from' => '/some/url somethingelse1',
			'url_notfrom' => '/some/url somethingelse2',
			'regex' => false,
			'name' => 'thisisits-_',
			'value' => 'value',
		);
		$source = array(
			'url_from' => "/some/url\nsomethingelse1",
			'url_notfrom' => "/some/url\nsomethingelse2",
			'name' => 'this is it@s-_',
			'value' => 'value',
		);

		$this->assertEquals( $saved, $match->save( $source ) );
	}

	public function testBadData() {
		$match = new Cookie_Match();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'regex' => false,
			'name' => '',
			'value' => '',
		);
		$this->assertEquals( $saved, $match->save( array( 'bad' => 'thing' ) ) );
	}

	public function testLoadBad() {
		$match = new Cookie_Match();
		$match->load( serialize( array( 'url_from' => 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', 'url_notfrom' => 'yes', 'regex' => true, 'name' => '', 'value' => '' ) ) );
		$this->assertEquals( 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', $match->url_from );
	}

	public function testMatch() {
		$_COOKIE['cookie'] = 'nothing';

		$match = new Cookie_Match( serialize( array( 'name' => 'cookie', 'value' => 'nothing', 'regex' => false, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertTrue( $match->is_match( '' ) );

		unset( $_COOKIE['cookie'] );
	}

	public function testMatchRegex() {
		$_COOKIE['cookie'] = 'nothing';

		$match = new Cookie_Match( serialize( array( 'name' => 'cookie', 'value' => 'no.*', 'regex' => true, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertTrue( $match->is_match( '' ) );

		unset( $_COOKIE['cookie'] );
	}

	public function testNotMatchExist() {
		$match = new Cookie_Match( serialize( array( 'name' => 'cookie', 'value' => 'other', 'regex' => false, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
	}

	public function testNotMatch() {
		$_COOKIE['cookie'] = 'other';

		$match = new Cookie_Match( serialize( array( 'name' => 'cookie', 'value' => 'nothing', 'regex' => false, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );

		unset( $_COOKIE['cookie'] );
	}
}
