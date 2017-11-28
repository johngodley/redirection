<?php

class RedirectionApiSettingsTest extends WP_Ajax_UnitTestCase {
	public static $redirection;

	public static function setupBeforeClass() {
		self::$redirection = Redirection_Admin::init()->api;
	}

	private function setNonce() {
		$this->_setRole( 'administrator' );
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'wp_rest' );
	}

	public function testLoadSettings() {
		$this->setNonce();
		$result = json_decode( self::$redirection->ajax_load_settings() );

		$this->assertTrue( is_object( $result->settings ) );
		$this->assertTrue( is_array( $result->groups ) );
		$this->assertTrue( ! empty( $result->installed ) );
	}

	public function testSaveEmptySettingsChangesNothing() {
		$this->setNonce();

		$before = json_decode( self::$redirection->ajax_load_settings() );
		update_option( 'redirection_options', (array)$before->settings );

		$after = json_decode( self::$redirection->ajax_save_settings( array() ) );

		unset( $before->settings->token );
		unset( $after->settings->token );
		unset( $before->settings->modules );
		unset( $after->settings->modules );
		unset( $before->installed );
		unset( $after->installed );

		$this->assertEquals( $before, $after );
	}

	public function testSaveAutoTargetQuotes() {
		$quoted = "this's";
		$this->setNonce();

		$result = json_decode( self::$redirection->ajax_save_settings( array( 'auto_target' => $quoted ) ) );
		$this->assertEquals( $quoted, $result->settings->auto_target );
	}

	public function testSaveInvalidMonitorPost() {
		$data = "monkey";
		$this->setNonce();

		$result = json_decode( self::$redirection->ajax_save_settings( array( 'monitor_post' => $data ) ) );
		$this->assertEquals( 0, $result->settings->monitor_post );
	}

	public function testSaveValidMonitorPost() {
		$data = "1";
		$this->setNonce();

		$result = json_decode( self::$redirection->ajax_save_settings( array( 'monitor_post' => $data, 'monitor_type_post' => true ) ) );
		$this->assertEquals( 1, $result->settings->monitor_post );
		$this->assertEquals( array( 'post' ), $result->settings->monitor_types );
	}

	public function testNoMonitorTypes() {
		$this->setNonce();
		$result = json_decode( self::$redirection->ajax_save_settings( array( 'monitor_post' => '1', 'associated_redirect' => '/test' ) ) );
		$this->assertEquals( 0, $result->settings->monitor_post );
		$this->assertEquals( '', $result->settings->associated_redirect );
	}

	public function testMonitorTypes() {
		$this->setNonce();
		$result = json_decode( self::$redirection->ajax_save_settings( array( 'monitor_post' => '1', 'monitor_type_post' => true, 'monitor_type_page' => true, 'monitor_type_trash' => true ) ) );
		$this->assertEquals( array( 'post', 'page', 'trash' ), $result->settings->monitor_types );
	}

	public function testAssociatedRedirect() {
		$this->setNonce();
		$result = json_decode( self::$redirection->ajax_save_settings( array( 'monitor_post' => '1', 'monitor_type_post' => true, 'associated_redirect' => '/amp/' ) ) );
		$this->assertEquals( '/amp/', $result->settings->associated_redirect );
	}

	public function testSaveMonitorRegex() {
		$data = 'true';
		$this->setNonce();

		$result = json_decode( self::$redirection->ajax_save_settings( array( 'monitor_regex' => $data ) ) );
		$this->assertEquals( true, $result->settings->monitor_regex );
	}

	public function testSaveSupport() {
		$data = true;
		$this->setNonce();

		$result = json_decode( self::$redirection->ajax_save_settings( array( 'support' => $data ) ) );
		$this->assertEquals( true, $result->settings->support );
	}

	public function testSaveToken() {
		$data = '1234X';
		$this->setNonce();

		$result = json_decode( self::$redirection->ajax_save_settings( array( 'token' => $data ) ) );
		$this->assertEquals( $data, $result->settings->token );
	}

	public function testSaveRandomToken() {
		$data = '';
		$this->setNonce();

		$result = json_decode( self::$redirection->ajax_save_settings( array( 'token' => $data ) ) );
		$this->assertNotEquals( '', $result->settings->token );
	}

	public function testSaveBadExpiry() {
		$data = 'monkey';
		$this->setNonce();

		$result = json_decode( self::$redirection->ajax_save_settings( array( 'expire_redirect' => $data ) ) );
		$this->assertEquals( 0, $result->settings->expire_redirect );
	}

	public function testSaveExpiry() {
		$data = '30';
		$this->setNonce();

		$result = json_decode( self::$redirection->ajax_save_settings( array( 'expire_redirect' => $data ) ) );
		$this->assertEquals( 30, $result->settings->expire_redirect );
	}

	public function testSaveApacheConfig() {
		$data = '30';
		$this->setNonce();

		$result = json_decode( self::$redirection->ajax_save_settings( array( 'location' => 'location' ) ) );

		$this->assertEquals( 'location', $result->settings->modules->{ '2' }->location );

		unlink( 'location' );
	}

	public function testBadCacheClear() {
		$this->setNonce();

		$result = json_decode( self::$redirection->ajax_save_settings( array( 'redirect_cache' => -10 ) ) );
		$this->assertEquals( 1, $result->settings->redirect_cache );
	}

	public function testGoodCacheClear() {
		$this->setNonce();

		$result = json_decode( self::$redirection->ajax_save_settings( array( 'redirect_cache' => 24 ) ) );
		$this->assertEquals( 24, $result->settings->redirect_cache );
	}
}
