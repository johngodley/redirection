<?php

require dirname( __FILE__ ) . '/../../matches/cookie.php';

class CookieMatchTest extends WP_UnitTestCase {
	public function testTargetSanitized() {
		$match = new Cookie_Match();
		$saved = array(
			'url_from' => '/some/url',
			'url_notfrom' => '/some/url',
			'regex' => false,
			'name' => "thisisits-_",
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

	public function testNoTargetNoUrl() {
		$_COOKIE['cookie'] = 'nothing';

		$match = new Cookie_Match( serialize( array( 'name' => 'cookie', 'value' => 'other', 'regex' => false, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertEquals( false, $match->get_target( 'a', 'b', false ) );
		unset( $_COOKIE['cookie'] );
	}

	public function testRegexNoTargetNoUrl() {
		$_COOKIE['cookie'] = 'nothing';

		$match = new Cookie_Match( serialize( array( 'name' => 'cookie', 'value' => 'other', 'regex' => false, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertEquals( false, $match->get_target( 'a', 'b', true ) );
		unset( $_COOKIE['cookie'] );
	}

	public function testNoTargetUrl() {
		$_COOKIE['cookie'] = 'nothing';

		$match = new Cookie_Match( serialize( array( 'name' => 'cookie', 'value' => 'other', 'regex' => false, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertEquals( false, $match->get_target( 'a', 'b', true ) );
		unset( $_COOKIE['cookie'] );
	}

	public function testNoTargetNotFrom() {
		$_COOKIE['cookie'] = 'nothing';

		$match = new Cookie_Match( serialize( array( 'name' => 'cookie', 'value' => 'other', 'regex' => false, 'url_from' => '/from', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertEquals( '/notfrom', $match->get_target( 'a', 'b', false ) );
		unset( $_COOKIE['cookie'] );
	}

	public function testNoTargetFrom() {
		$_COOKIE['cookie'] = 'nothing';

		$match = new Cookie_Match( serialize( array( 'name' => 'cookie', 'value' => 'nothing', 'regex' => false, 'url_from' => '/from', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertEquals( '/from', $match->get_target( 'a', 'b', false ) );
		unset( $_COOKIE['cookie'] );
	}

	public function testRegexTarget() {
		$_COOKIE['cookie'] = 'nothing|other|cat';

		$match = new Cookie_Match( serialize( array( 'name' => 'cookie', 'value' => 'cat', 'regex' => true, 'url_from' => '/from', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertEquals( '/from', $match->get_target( 'a', 'b', false ) );
		unset( $_COOKIE['cookie'] );
	}

	public function testRegexUrl() {
		$_COOKIE['cookie'] = 'cat';

		$match = new Cookie_Match( serialize( array( 'name' => 'cookie', 'value' => 'cat', 'regex' => false, 'url_from' => '/other/$1', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertEquals( '/other/1', $match->get_target( '/category/1', '/category/(.*?)', true ) );
		unset( $_COOKIE['cookie'] );
	}
}
