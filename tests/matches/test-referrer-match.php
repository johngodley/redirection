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
			'action_data_url_from' => "/some/url\nsomethingelse1",
			'action_data_url_notfrom' => "/some/url\nsomethingelse2",
			'action_data_referrer' => "some\nreferrer",
		);

		$this->assertEquals( $saved, $match->save( $source ) );
	}

	public function testNoTargetNoUrl() {
		$_SERVER['HTTP_REFERER'] = 'nothing';

		$match = new Referrer_Match( serialize( array( 'referrer' => 'other', 'regex' => false, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertEquals( false, $match->get_target( 'a', 'b', false ) );
	}

	public function testRegexNoTargetNoUrl() {
		$_SERVER['HTTP_REFERER'] = 'nothing';

		$match = new Referrer_Match( serialize( array( 'referrer' => 'other', 'regex' => true, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertEquals( false, $match->get_target( 'a', 'b', true ) );
	}

	public function testNoTargetUrl() {
		$_SERVER['HTTP_REFERER'] = 'nothing';

		$match = new Referrer_Match( serialize( array( 'referrer' => 'nothing', 'regex' => false, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertEquals( false, $match->get_target( 'a', 'b', true ) );
	}

	public function testNoTargetNotFrom() {
		$_SERVER['HTTP_REFERER'] = 'nothing';

		$match = new Referrer_Match( serialize( array( 'referrer' => 'other', 'regex' => false, 'url_from' => '/from', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertEquals( '/notfrom', $match->get_target( 'a', 'b', false ) );
	}

	public function testNoTargetFrom() {
		$_SERVER['HTTP_REFERER'] = 'nothing';

		$match = new Referrer_Match( serialize( array( 'referrer' => 'nothing', 'regex' => false, 'url_from' => '/from', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertEquals( '/from', $match->get_target( 'a', 'b', false ) );
	}

	public function testRegexTarget() {
		$_SERVER['HTTP_REFERER'] = 'nothing|other|cat';

		$match = new Referrer_Match( serialize( array( 'referrer' => 'cat', 'regex' => true, 'url_from' => '/from', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertEquals( '/from', $match->get_target( 'a', 'b', false ) );
	}

	public function testRegexUrl() {
		$_SERVER['HTTP_REFERER'] = 'cat';

		$match = new Referrer_Match( serialize( array( 'referrer' => 'cat', 'regex' => false, 'url_from' => '/other/$1', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertEquals( '/other/1', $match->get_target( '/category/1', '/category/(.*?)', true ) );
	}
}
