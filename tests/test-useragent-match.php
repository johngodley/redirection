<?php

require dirname( __FILE__ ) . '/../matches/user-agent.php';

class UserAgentMatchTest extends WP_UnitTestCase {
	public function testTargetSanitized() {
		$match = new Agent_Match();
		$saved = array(
			'url_from' => '/some/url',
			'url_notfrom' => '/some/url',
			'user_agent' => 'user agent',
		);
		$source = array(
			'url_from' => "/some/url\nsomethingelse1",
			'url_notfrom' => "/some/url\nsomethingelse2",
			'user_agent' => "user agent\nhere",
		);

		$this->assertEquals( $match->save( $source ), $saved );
	}
}
