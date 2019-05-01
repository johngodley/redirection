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

	public function testNotMatch() {
		$_SERVER['HTTP_REFERER'] = 'nothing';

		$match = new Referrer_Match( serialize( array( 'referrer' => 'other', 'regex' => false, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
	}

	public function testNotMatchNotExists() {
		unset( $_SERVER['HTTP_REFERER'] );

		$match = new Referrer_Match( serialize( array( 'referrer' => 'other', 'regex' => true, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
	}

	public function testMatch() {
		$_SERVER['HTTP_REFERER'] = 'nothing';

		$match = new Referrer_Match( serialize( array( 'referrer' => 'nothing', 'regex' => false, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertTrue( $match->is_match( '' ) );
	}

	public function testMatchRegex() {
		$_SERVER['HTTP_REFERER'] = 'agent1';

		$match = new Referrer_Match( serialize( array( 'referrer' => 'agent.*', 'regex' => true, 'url_from' => '/other/$1', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertTrue( $match->is_match( '' ) );
	}
}
