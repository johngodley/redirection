<?php

require dirname( __FILE__ ) . '/../../matches/referrer.php';

class ReferrerMatchTest extends WP_UnitTestCase {
	public function testTargetSanitized() {
		$match = new Referrer_Match();
		$saved = array(
			'url_from' => '/some/url',
			'url_notfrom' => '/some/url',
			'regex' => false,
			'referrer' => "some",
		);
		$source = array(
			'url_from' => "/some/url\nsomethingelse1",
			'url_notfrom' => "/some/url\nsomethingelse2",
			'referrer' => "some\nreferrer",
		);

		$this->assertEquals( $saved, $match->save( $source ) );
	}

	public function testBadData() {
		$match = new Referrer_Match();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'regex' => false,
			'referrer' => '',
		);
		$this->assertEquals( $saved, $match->save( array( 'bad' => 'thing' ) ) );
	}

	public function testLoadBad() {
		$match = new Referrer_Match();
		$match->load( serialize( array( 'url_from' => 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', 'url_notfrom' => 'yes', 'regex' => true, 'referrer' => '' ) ) );
		$this->assertEquals( 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', $match->url_from );
	}

	public function testNoTargetNoUrl() {
		$_SERVER['HTTP_REFERER'] = 'nothing';

		$match = new Referrer_Match( serialize( array( 'referrer' => 'other', 'regex' => false, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertEquals( false, $match->get_target( 'a', 'b', new Red_Source_Flags() ) );
	}

	public function testRegexNoTargetNoUrl() {
		$_SERVER['HTTP_REFERER'] = 'nothing';

		$match = new Referrer_Match( serialize( array( 'referrer' => 'other', 'regex' => true, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertEquals( false, $match->get_target( 'a', 'b', new Red_Source_Flags( [ 'flag_regex' => true ] ) ) );
	}

	public function testNoTargetUrl() {
		$_SERVER['HTTP_REFERER'] = 'nothing';

		$match = new Referrer_Match( serialize( array( 'referrer' => 'nothing', 'regex' => false, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertEquals( false, $match->get_target( 'a', 'b', new Red_Source_Flags( [ 'flag_regex' => true ] ) ) );
	}

	public function testNoTargetNotFrom() {
		$_SERVER['HTTP_REFERER'] = 'nothing';

		$match = new Referrer_Match( serialize( array( 'referrer' => 'other', 'regex' => false, 'url_from' => '/from', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertEquals( '/notfrom', $match->get_target( 'a', 'b', new Red_Source_Flags() ) );
	}

	public function testNoTargetFrom() {
		$_SERVER['HTTP_REFERER'] = 'nothing';

		$match = new Referrer_Match( serialize( array( 'referrer' => 'nothing', 'regex' => false, 'url_from' => '/from', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertEquals( '/from', $match->get_target( 'a', 'b', new Red_Source_Flags() ) );
	}

	public function testRegexTarget() {
		$_SERVER['HTTP_REFERER'] = 'nothing|other|cat';

		$match = new Referrer_Match( serialize( array( 'referrer' => 'cat', 'regex' => true, 'url_from' => '/from', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertEquals( '/from', $match->get_target( 'a', 'b', new Red_Source_Flags() ) );
	}

	public function testRegexUrl() {
		$_SERVER['HTTP_REFERER'] = 'cat';

		$match = new Referrer_Match( serialize( array( 'referrer' => 'cat', 'regex' => false, 'url_from' => '/other/$1', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertEquals( '/other/1', $match->get_target( '/category/1', '/category/(.*?)', new Red_Source_Flags( [ 'flag_regex' => true ] ) ) );
	}
}
