<?php

require dirname( __FILE__ ) . '/../../matches/login.php';

class LoginMatchTest extends WP_UnitTestCase {
	public function testTargetSanitized() {
		$match = new Login_Match();
		$saved = array(
			'logged_in' => '/some/url',
			'logged_out' => '/some/url',
		);
		$source = array(
			'action_data_logged_in' => "/some/url\nsomethingelse1",
			'action_data_logged_out' => "/some/url\nsomethingelse2",
		);

		$this->assertEquals( $saved, $match->save( $source ) );
	}
}
