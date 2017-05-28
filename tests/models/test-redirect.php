<?php

class RedirectTest extends WP_UnitTestCase {
	public function testRemoveHttp() {
		$this->assertEquals( Red_Item::sanitize_url( 'http://domain.com/some/url' ), '/some/url' );
		$this->assertEquals( Red_Item::sanitize_url( 'https://domain.com/some/url' ), '/some/url' );
	}

	public function testRemoveHash() {
		$this->assertEquals( Red_Item::sanitize_url( '/some/url#thing' ), '/some/url' );
	}

	public function testRemoveNewline() {
		$this->assertEquals( Red_Item::sanitize_url( "/some/url\nsomethingelse1" ), '/some/url' );
		$this->assertEquals( Red_Item::sanitize_url( "/some/url\rsomethingelse2" ), '/some/url' );
		$this->assertEquals( Red_Item::sanitize_url( "/some/url\r\nsomethingelse3" ), '/some/url' );
	}

	public function testAddLeadingSlash() {
		$this->assertEquals( Red_Item::sanitize_url( 'some/url' ), '/some/url' );
	}

	public function testNoMatch() {
		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source', 'action_data' => '/target', 'action_code' => 301 ) );
		$this->assertFalse( $item->matches( '/source2' ) );
	}

	public function testRegexNoMatch() {
		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '/source.*', 'action_data' => '/target', 'action_code' => 301 ) );
		$this->assertFalse( $item->matches( '/cat' ) );
	}

	public function testMatchDisabled() {
		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source', 'action_data' => '/target', 'action_code' => 301, 'status' => 'disabled' ) );
		$this->assertFalse( $item->matches( '/source' ) );
	}

	public function testMatch() {
		global $wpdb;

		$this->capturedRedirect();
		$action = new MockAction();

		add_action( 'redirection_url_target', array( $action, 'action' ), 10, 2 );

		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source', 'action_data' => '/target', 'action_code' => 301, 'status' => 'enabled' ) );
		$item->matches( '/source' );

		$this->assertEquals( '/target', $this->capturedUrl );   // URL is redirected
		$this->assertEquals( 1, $action->get_call_count() );
		$this->assertEquals( '/target', $action->get_args()[0][0] );
		$this->assertEquals( '/source', $action->get_args()[0][1] );
		$this->assertEquals( 1, $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_logs" ) );

		$this->resetCaptured();
	}

	public function testRegexMatch() {
		$this->capturedRedirect();

		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '/source(.*)', 'action_data' => '/target$1', 'action_code' => 301, 'status' => 'enabled' ) );

		$item->matches( '/source45' );
		$this->assertEquals( '/target45', $this->capturedUrl );   // URL is redirected
		$this->resetCaptured();
	}

	private function capturedRedirect() {
		add_filter( 'wp_redirect', array( $this, 'captureRedirectResult' ) );
	}

	public function captureRedirectResult( $url ) {
		$this->capturedUrl = $url;
		return false;
	}

	private function resetCaptured() {
		$this->capturedUrl = false;
		remove_filter( 'wp_redirect', array( $this, 'captureRedirectResult' ) );
	}
}
