<?php

class Module\WordPress_Test extends WP_UnitTestCase {
	private function get_404_count() {
		global $wpdb;

		return intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_404" ), 10 );
	}

	private function get_log_count() {
		global $wpdb;

		return intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_logs" ), 10 );
	}

	public function testLogBackCompat() {
		$wp = new Module\WordPress();
		$insert = [
			'request_data' => 'data',
			'request_method' => 'method',
			'http_code' => 'code',
			'domain' => 'domain',
			'url' => 'url',
			'redirect_by' => 'redirect_by',
		];

		update_option( REDIRECTION_OPTION, array( 'database' => 4.0 ) );

		$old = $wp->log_back_compat( $insert );

		update_option( REDIRECTION_OPTION, array( 'database' => REDIRECTION_DB_VERSION ) );

		$new = $wp->log_back_compat( $insert );

		$this->assertEquals( $insert, $new );
		$this->assertEquals( [ 'url' => 'url' ], $old );
	}

	public function testCanonicalWP() {
		// Normal canonical redirect
		$wp = new Module\WordPress();
		$this->assertEquals( 'url', $wp->redirect_canonical( 'url', 'target' ) );

		// Canonical redirect when we've already matched this URL
		$wp->reset( new Redirect\Redirect() );
		$this->assertFalse( $wp->redirect_canonical( 'url', 'target' ) );
	}

	public function testNo404Log() {
		$wp = new Module\WordPress();
		$start = $this->get_404_count();

		// No log if not a 404 page
		global $wp_query;

		$wp_query->is_404 = false;
		$wp->template_redirect();
		$this->assertEquals( $start, $this->get_404_count() );

		// No log if already matched a URL
		$wp_query->is_404 = true;
		$wp->reset( new Redirect\Redirect() );
		$wp->template_redirect();
		$this->assertEquals( $start, $this->get_404_count() );

		// No log if 404 log option is not enabled
		update_option( REDIRECTION_OPTION, array( 'expire_404' => -1 ) );
		$wp->reset();
		$wp->template_redirect();
		$this->assertEquals( $start, $this->get_404_count() );

		$wp_query->is_404 = false;
	}

	public function test404Log() {
		global $wp_query;

		$wp_query->is_404 = true;
		update_option( REDIRECTION_OPTION, array( 'expire_404' => 7 ) );

		// Log if it is a 404 page and not matched and option is enabled
		$wp = new Module\WordPress();
		$start = $this->get_404_count();
		$wp->template_redirect();
		$this->assertEquals( $start + 1, $this->get_404_count() );

		$wp_query->is_404 = false;
	}

	public function testDoNothing() {
		$wp = new Module\WordPress();
		$this->assertTrue( $wp->can_log() );

		$wp->redirection_do_nothing();
		$this->assertFalse( $wp->can_log() );
	}

	public function testNoExternalIfRedirection() {
		$_SERVER['REQUEST_URI'] = '/test';
		update_option( REDIRECTION_OPTION, array( 'log_external' => true ) );
		$start = $this->get_log_count();

		$wp = new Module\WordPress();
		$wp->record_redirect_by( 'redirection' );

		$this->assertEquals( $start, $this->get_log_count() );
	}

	public function testNoExternalIfNotEnabled() {
		$_SERVER['REQUEST_URI'] = '/test';
		update_option( REDIRECTION_OPTION, array( 'log_external' => false ) );
		$start = $this->get_log_count();

		$wp = new Module\WordPress();
		$wp->record_redirect_by( 'wordpress' );

		$this->assertEquals( $start, $this->get_log_count() );
	}

	public function testExternal() {
		$_SERVER['REQUEST_URI'] = '/test';
		update_option( REDIRECTION_OPTION, array( 'log_external' => true ) );
		$start = $this->get_log_count();

		$wp = new Module\WordPress();
		$wp->record_redirect_by( 'wordpress' );

		$this->assertEquals( $start + 1, $this->get_log_count() );
	}
}
