<?php

class RedirectionApiTest extends WP_UnitTestCase {
	public function testLoadSettings() {
		$redirection = Redirection_Admin::init();

		$result = json_decode( $redirection->ajax_load_settings() );

		$this->assertTrue( is_object( $result->settings ) );
		$this->assertTrue( is_array( $result->groups ) );
	}

	public function testSaveEmptySettingsChangesNothing() {
		$redirection = Redirection_Admin::init();

		$before = json_decode( $redirection->ajax_load_settings() );
		update_option( 'redirection_options', (array)$before->settings );

		$after = json_decode( $redirection->ajax_save_settings( array() ) );

		$this->assertEquals( $before, $after );
	}

	public function testSaveAutoTargetQuotes() {
		$quoted = "this's";
		$redirection = Redirection_Admin::init();

		$result = json_decode( $redirection->ajax_save_settings( array( 'auto_target' => $quoted ) ) );
		$this->assertEquals( $quoted, $result->settings->auto_target );
	}

	public function testSaveInvalidMonitorPost() {
		$data = "monkey";
		$redirection = Redirection_Admin::init();

		$result = json_decode( $redirection->ajax_save_settings( array( 'monitor_post' => $data ) ) );
		$this->assertEquals( 0, $result->settings->monitor_post );
	}

	public function testSaveValidMonitorPost() {
		$data = "5";
		$redirection = Redirection_Admin::init();

		$result = json_decode( $redirection->ajax_save_settings( array( 'monitor_post' => $data ) ) );
		$this->assertEquals( 5, $result->settings->monitor_post );
	}

	public function testSaveSupport() {
		$data = 'true';
		$redirection = Redirection_Admin::init();

		$result = json_decode( $redirection->ajax_save_settings( array( 'support' => $data ) ) );
		$this->assertEquals( true, $result->settings->support );
	}

	public function testSaveToken() {
		$data = '1234X';
		$redirection = Redirection_Admin::init();

		$result = json_decode( $redirection->ajax_save_settings( array( 'token' => $data ) ) );
		$this->assertEquals( $data, $result->settings->token );
	}

	public function testSaveRandomToken() {
		$data = '';
		$redirection = Redirection_Admin::init();

		$result = json_decode( $redirection->ajax_save_settings( array( 'token' => $data ) ) );
		$this->assertNotEquals( '', $result->settings->token );
	}

	public function testSaveBadExpiry() {
		$data = 'monkey';
		$redirection = Redirection_Admin::init();

		$result = json_decode( $redirection->ajax_save_settings( array( 'expire_redirect' => $data ) ) );
		$this->assertEquals( 0, $result->settings->expire_redirect );
	}

	public function testSaveExpiry() {
		$data = '30';
		$redirection = Redirection_Admin::init();

		$result = json_decode( $redirection->ajax_save_settings( array( 'expire_redirect' => $data ) ) );
		$this->assertEquals( 30, $result->settings->expire_redirect );
	}
}
