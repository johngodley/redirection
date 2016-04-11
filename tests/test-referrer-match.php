<?php

require dirname( __FILE__ ) . '/../matches/referrer.php';

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

		$this->assertEquals( $match->save( $source ), $saved );
	}
}
