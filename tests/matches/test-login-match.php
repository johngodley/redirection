<?php

require dirname( __FILE__ ) . '/../../matches/login.php';

class LoginMatchTest extends WP_UnitTestCase {
	public function testTargetSanitized() {
		$match = new Login_Match();
		$saved = array(
			'url_loggedin' => '/some/url',
			'url_loggedout' => '/some/url',
		);
		$source = array(
			'url_loggedin' => "/some/url\nsomethingelse1",
			'url_loggedout' => "/some/url\nsomethingelse2",
		);

		$this->assertEquals( $match->save( $source ), $saved );
	}
}
