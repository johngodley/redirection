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
}
