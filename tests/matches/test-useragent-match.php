<?php

require dirname( __FILE__ ) . '/../../matches/user-agent.php';

class UserAgentMatchTest extends WP_UnitTestCase {
	public function testTargetSanitized() {
		$match = new Agent_Match();
		$saved = array(
			'url_from' => '/some/url',
			'url_notfrom' => '/some/url',
			'agent' => 'user agent',
			'regex' => false,
		);
		$source = array(
			'action_data_url_from' => "/some/url\nsomethingelse1",
			'action_data_url_notfrom' => "/some/url\nsomethingelse2",
			'action_data_agent' => "user agent\nhere",
		);

		$this->assertEquals( $saved, $match->save( $source ) );
	}

	public function testNoTargetNoUrl() {
		$_SERVER['HTTP_USER_AGENT'] = 'nothing';

		$match = new Agent_Match( array( 'agent' => 'other', 'regex' => false, 'url_from' => '', 'url_notfrom' => '' ) );
		$this->assertEquals( false, $match->get_target( 'a', 'b', false ) );
	}

	public function testRegexNoTargetNoUrl() {
		$_SERVER['HTTP_USER_AGENT'] = 'nothing';

		$match = new Agent_Match( array( 'agent' => 'other', 'regex' => true, 'url_from' => '', 'url_notfrom' => '' ) );
		$this->assertEquals( false, $match->get_target( 'a', 'b', true ) );
	}

	public function testNoTargetUrl() {
		$_SERVER['HTTP_USER_AGENT'] = 'nothing';

		$match = new Agent_Match( array( 'agent' => 'nothing', 'regex' => true, 'url_from' => '', 'url_notfrom' => '' ) );
		$this->assertEquals( false, $match->get_target( 'a', 'b', true ) );
	}

	public function testNoTargetNotFrom() {
		$_SERVER['HTTP_USER_AGENT'] = 'nothing';

		$match = new Agent_Match( array( 'agent' => 'other', 'regex' => false, 'url_from' => '/from', 'url_notfrom' => '/notfrom' ) );
		$this->assertEquals( '/notfrom', $match->get_target( 'a', 'b', false ) );
	}

	public function testNoTargetFrom() {
		$_SERVER['HTTP_USER_AGENT'] = 'nothing';

		$match = new Agent_Match( array( 'agent' => 'nothing', 'regex' => false, 'url_from' => '/from', 'url_notfrom' => '/notfrom' ) );
		$this->assertEquals( '/from', $match->get_target( 'a', 'b', false ) );
	}

	public function testRegexTarget() {
		$_SERVER['HTTP_USER_AGENT'] = 'nothing|other|cat';

		$match = new Agent_Match( array( 'agent' => 'cat', 'regex' => true, 'url_from' => '/from', 'url_notfrom' => '/notfrom' ) );
		$this->assertEquals( '/from', $match->get_target( 'a', 'b', false ) );
	}

	public function testRegexUrl() {
		$_SERVER['HTTP_USER_AGENT'] = 'cat';

		$match = new Agent_Match( array( 'agent' => 'cat', 'regex' => false, 'url_from' => '/other/$1', 'url_notfrom' => '/notfrom' ) );
		$this->assertEquals( '/other/1', $match->get_target( '/category/1', '/category/(.*?)', true ) );
	}
}
