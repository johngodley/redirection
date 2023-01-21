<?php

require dirname( __FILE__ ) . '/../../matches/user-agent.php';

class UserAgentMatchTest extends WP_UnitTestCase {
	public function testTargetSanitized() {
		$match = new Agent_Match();
		$saved = array(
			'url_from' => '/some/url somethingelse1',
			'url_notfrom' => '/some/url somethingelse2',
			'agent' => 'user agent here',
			'regex' => false,
		);
		$source = array(
			'url_from' => "/some/url\nsomethingelse1",
			'url_notfrom' => "/some/url\nsomethingelse2",
			'agent' => "user agent\nhere",
		);

		$this->assertEquals( $saved, $match->save( $source ) );
	}

	public function testBadData() {
		$match = new Agent_Match();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'regex' => false,
			'agent' => '',
		);
		$this->assertEquals( $saved, $match->save( array( 'bad' => 'thing' ) ) );
	}

	public function testLoadBad() {
		$match = new Agent_Match();
		$match->load( serialize( array( 'url_from' => 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', 'url_notfrom' => 'yes', 'regex' => true, 'agent' => '' ) ) );
		$this->assertEquals( 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', $match->url_from );
	}

	public function testNoMatch() {
		$_SERVER['HTTP_USER_AGENT'] = 'nothing';

		$match = new Agent_Match( serialize( array( 'agent' => 'other', 'regex' => false, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
	}

	public function testNoMatchNoExists() {
		unset( $_SERVER['HTTP_USER_AGENT'] );

		$match = new Agent_Match( serialize( array( 'agent' => 'other', 'regex' => false, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
	}

	public function testMatch() {
		$_SERVER['HTTP_USER_AGENT'] = 'nothing';

		$match = new Agent_Match( serialize( array( 'agent' => 'nothing', 'regex' => true, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertTrue( $match->is_match( '' ) );
	}

	public function testMatchRegex() {
		$_SERVER['HTTP_USER_AGENT'] = 'agent1';

		$match = new Agent_Match( serialize( array( 'agent' => 'agent.*', 'regex' => true, 'url_from' => '/from', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertTrue( $match->is_match( '' ) );
	}
}
