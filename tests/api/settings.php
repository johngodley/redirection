<?php

class RedirectionApiSettingsTest extends WP_Ajax_UnitTestCase {
	public static $redirection;

	public static function setupBeforeClass() {
		self::$redirection = Redirection_Admin::init();
	}

	private function setNonce() {
		$this->_setRole( 'administrator' );
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'wp_rest' );
	}

	public function testNonce() {
		$result = json_decode( self::$redirection->ajax_load_settings() );
		$this->assertTrue( isset( $result->error ) );
	}

	public function testPermissions() {
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'wp_rest' );
		$result = json_decode( self::$redirection->ajax_load_settings() );
		$this->assertTrue( isset( $result->error ) );
	}

	public function testLoadSettings() {
		$this->setNonce();
		$result = json_decode( self::$redirection->ajax_load_settings() );

		$this->assertTrue( is_object( $result->settings ) );
		$this->assertTrue( is_array( $result->groups ) );
	}

	public function testSaveEmptySettingsChangesNothing() {
		$this->setNonce();

		$before = json_decode( self::$redirection->ajax_load_settings() );
		update_option( 'redirection_options', (array)$before->settings );

		$after = json_decode( self::$redirection->ajax_save_settings( array() ) );

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
		$data = "5";
		$this->setNonce();

		$result = json_decode( self::$redirection->ajax_save_settings( array( 'monitor_post' => $data ) ) );
		$this->assertEquals( 5, $result->settings->monitor_post );
	}

	public function testSaveSupport() {
		$data = 'true';
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
}
