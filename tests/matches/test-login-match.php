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

	public function testNoTargetNoUrl() {
		wp_set_current_user( 1 );

		$match = new Login_Match( array( 'logged_in' => '', 'logged_out' => '' ) );
		$this->assertEquals( false, $match->get_target( 'a', 'b', false ) );
	}

	public function testRegexNoTargetNoUrl() {
		wp_set_current_user( 1 );

		$match = new Login_Match( array( 'logged_in' => '', 'logged_out' => '' ) );
		$this->assertEquals( false, $match->get_target( 'a', 'b', true ) );
	}

	public function testNoTargetNotFrom() {
		wp_set_current_user( 0 );

		$match = new Login_Match( array( 'logged_in' => '/from', 'logged_out' => '/notfrom' ) );
		$this->assertEquals( '/notfrom', $match->get_target( 'a', 'b', false ) );
	}

	public function testNoTargetFrom() {
		wp_set_current_user( 1 );

		$match = new Login_Match( array( 'logged_in' => '/from', 'logged_out' => '/notfrom' ) );
		$this->assertEquals( '/from', $match->get_target( 'a', 'b', false ) );
	}

	public function testRegexUrl() {
		wp_set_current_user( 1 );

		$match = new Login_Match( array( 'logged_in' => '/other/$1', 'logged_out' => '/notfrom' ) );
		$this->assertEquals( '/other/1', $match->get_target( '/category/1', '/category/(.*?)', true ) );
	}
}
