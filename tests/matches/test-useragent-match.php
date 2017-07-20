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
}
