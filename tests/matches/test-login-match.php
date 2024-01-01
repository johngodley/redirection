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
			'logged_in' => "/some/url\nsomethingelse1",
			'logged_out' => "/some/url\nsomethingelse2",
		);

		$this->assertEquals( $saved, $match->save( $source ) );
	}

	public function testBadData() {
		$match = new Login_Match();
		$saved = array(
			'logged_in' => '',
			'logged_out' => '',
		);
		$this->assertEquals( $saved, $match->save( array( 'bad' => 'thing' ) ) );
	}

	public function testLoadBad() {
		$match = new Login_Match();
		$match->load( serialize( array( 'logged_in' => 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', 'logged_out' => 'yes' ) ) );
		$this->assertEquals( 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', $match->logged_in );
	}

	public function testNoMatch() {
		wp_set_current_user( 0 );

		$match = new Login_Match( serialize( array( 'logged_in' => '', 'logged_out' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
	}

	public function testMatch() {
		wp_set_current_user( 1 );

		$match = new Login_Match( serialize( array( 'logged_in' => '', 'logged_out' => '' ) ) );
		$this->assertTrue( $match->is_match( '' ) );
	}

	public function testLoggedInTarget() {
		// Test a match to from
		$match = new Login_Match( serialize( [ 'logged_in' => '/from', 'logged_out' => '/notfrom' ] ) );
		$this->assertEquals( '/from', $match->get_target_url( '', '', new Red_Source_Flags(), true ) );
	}

	public function testLoggedOutTarget() {
		// Test no match to notfrom
		$match = new Login_Match( serialize( [ 'logged_in' => '/from', 'logged_out' => '/notfrom' ] ) );
		$this->assertEquals( '/notfrom', $match->get_target_url( '', '', new Red_Source_Flags(), false ) );
	}

	public function testNoLoggedInTarget() {
		// Test a match with no from
		$match = new Login_Match( serialize( [] ) );
		$this->assertEquals( '', $match->get_target_url( '', '', new Red_Source_Flags(), true ) );
	}

	public function testNoLoggedOutTarget() {
		// Test no match with no notfrom
		$match = new Login_Match( serialize( [] ) );
		$this->assertEquals( '', $match->get_target_url( '', '', new Red_Source_Flags(), false ) );
	}

	public function testRegexLoggedInTarget() {
		// Test a match with regex from
		$match = new Login_Match( serialize( [ 'logged_in' => '/from/$1', 'logged_out' => '/notfrom/$1' ] ) );
		$this->assertEquals( '/from/1', $match->get_target_url( '/category/1', '/category/(.*?)', new Red_Source_Flags( [ 'flag_regex' => true ] ), true ) );
	}

	public function testRegexLoggedOutTarget() {
		// Test no match with regex notfrom
		$match = new Login_Match( serialize( [ 'logged_in' => '/from/$1', 'logged_out' => '/notfrom/$1' ] ) );
		$this->assertEquals( '/notfrom/1', $match->get_target_url( '/category/1', '/category/(.*?)', new Red_Source_Flags( [ 'flag_regex' => true ] ), false ) );
	}
}
