<?php

use Redirection\Log;
use Redirection\Plugin;
use Redirection\Group;

class RedirectionApiSettingsTest extends Redirection_Api_Test {
	private function get_endpoints() {
		return [
			[ 'setting', 'GET', [] ],
			[ 'setting', 'POST', [] ],
		];
	}

	public function testNoPermission() {
		$this->setUnauthorised();

		// None of these should work
		$this->check_endpoints( $this->get_endpoints() );
	}

	public function testEditorPermission() {
		// Everything else is 403
		$working = [
			Plugin\Capabilities::CAP_OPTION_MANAGE => [
				[ 'setting', 'GET' ],
				[ 'setting', 'POST' ],
			],
			Plugin\Capabilities::CAP_SITE_MANAGE => [
				[ 'setting', 'GET' ],
				[ 'setting', 'POST' ],
			],
		];

		$this->setEditor();

		foreach ( $working as $cap => $working_caps ) {
			$this->add_capability( $cap );
			$this->check_endpoints( $this->get_endpoints(), $working_caps );
			$this->clear_capability();
		}
	}

	public function testAdminPermission() {
		// All of these should work
		$this->check_endpoints( $this->get_endpoints(), $this->get_endpoints() );
	}

	public function testLoadSettings() {
		$this->setNonce();
		$result = $this->callApi( 'setting' );

		$this->assertTrue( is_array( $result->data['settings'] ) );
		$this->assertTrue( is_array( $result->data['groups'] ) );
		$this->assertTrue( ! empty( $result->data['installed'] ) );
	}

	public function testSaveEmptySettingsChangesNothing() {
		$this->setNonce();

		$before = $this->callApi( 'setting' );
		update_option( 'redirection_options', (array) $before->data['settings'] );
		$before = $before->data['settings'];

		$after = $this->callApi( 'setting', array(), 'POST' );
		$after = $after->data['settings'];

		unset( $before['token'] );
		unset( $after['token'] );
		unset( $before['modules'] );
		unset( $after['modules'] );
		unset( $before['installed'] );
		unset( $after['installed'] );

		$this->assertEquals( $before, $after );
	}

	public function testSaveAutoTargetQuotes() {
		$quoted = "this's";
		$this->setNonce();

		$result = $this->callApi( 'setting', array( 'auto_target' => $quoted ), 'POST' );
		$this->assertEquals( $quoted, $result->data['settings']['auto_target'] );
	}

	public function testSaveInvalidMonitorPost() {
		$data = 'monkey';
		$this->setNonce();

		$result = $this->callApi( 'setting', array( 'monitor_post' => $data ), 'POST' );
		$this->assertEquals( 0, $result->data['settings']['monitor_post'] );
	}

	public function testSaveValidMonitorPost() {
		$data = "1";
		$this->setNonce();

		$result = $this->callApi( 'setting', array( 'monitor_post' => $data, 'monitor_types' => array( 'post' ) ), 'POST' );
		$this->assertEquals( 1, $result->data['settings']['monitor_post'] );
		$this->assertEquals( array( 'post' ), $result->data['settings']['monitor_types'] );
	}

	public function testNoMonitorTypes() {
		$this->setNonce();
		$result = $this->callApi( 'setting', array( 'monitor_post' => '1', 'associated_redirect' => '/test' ), 'POST' );
		$this->assertEquals( 0, $result->data['settings']['monitor_post'] );
		$this->assertEquals( '', $result->data['settings']['associated_redirect'] );
	}

	public function testMonitorTypes() {
		$this->setNonce();
		$result = $this->callApi( 'setting', array( 'monitor_post' => '1', 'monitor_types' => array( 'post', 'page', 'trash' ) ), 'POST' );
		$this->assertEquals( array( 'post', 'page', 'trash' ), $result->data['settings']['monitor_types'] );
	}

	public function testAssociatedRedirect() {
		$this->setNonce();
		$result = $this->callApi( 'setting', array( 'monitor_post' => '1', 'monitor_types' => array( 'post' ), 'associated_redirect' => '/amp/' ), 'POST' );
		$this->assertEquals( '/amp/', $result->data['settings']['associated_redirect'] );
	}

	public function testSaveSupport() {
		$data = true;
		$this->setNonce();

		$result = $this->callApi( 'setting', array( 'support' => $data ), 'POST' );
		$this->assertEquals( true, $result->data['settings']['support'] );
	}

	public function testSaveToken() {
		$data = '1234X';
		$this->setNonce();

		$result = $this->callApi( 'setting', array( 'token' => $data ), 'POST' );
		$this->assertEquals( $data, $result->data['settings']['token'] );
	}

	public function testSaveRandomToken() {
		$data = '';
		$this->setNonce();

		$result = $this->callApi( 'setting', array( 'token' => $data ), 'POST' );
		$this->assertNotEquals( '', $result->data['settings']['token'] );
	}

	public function testSaveBadExpiry() {
		$data = 'monkey';
		$this->setNonce();

		$result = $this->callApi( 'setting', array( 'expire_redirect' => $data ), 'POST' );
		$this->assertEquals( 0, $result->data['settings']['expire_redirect'] );
	}

	public function testSaveExpiry() {
		$data = '30';
		$this->setNonce();

		$result = $this->callApi( 'setting', array( 'expire_redirect' => $data ), 'POST' );
		$this->assertEquals( 30, $result->data['settings']['expire_redirect'] );
	}

	public function testSaveApacheConfig() {
		$data = '30';
		$this->setNonce();

		$result = $this->callApi( 'setting', array( 'location' => '/tmp/' ), 'POST' );
		$this->assertEquals( '/tmp/.htaccess', $result->data['settings']['modules']['2']['location'] );

		unlink( 'location' );
	}

	public function testBadCacheClear() {
		$this->setNonce();

		$result = $this->callApi( 'setting', array( 'redirect_cache' => -10 ), 'POST' );
		$this->assertEquals( 1, $result->data['settings']['redirect_cache'] );
	}

	public function testGoodCacheClear() {
		$this->setNonce();

		$result = $this->callApi( 'setting', array( 'redirect_cache' => 24 ), 'POST' );
		$this->assertEquals( 24, $result->data['settings']['redirect_cache'] );
	}

	public function testDefaultGroup() {
		$this->setNonce();

		$groups = Group\Group::get_all();

		$this->callApi( 'setting', array( 'monitor_post' => $groups[0]['id'], 'monitor_types' => array( 'post' ) ), 'POST' );
		$result = $this->callApi( 'setting', array( 'last_group_id' => 1 ), 'POST' );
		$this->assertEquals( 1, $result->data['settings']['monitor_post'] );
	}

	public function testHeader() {
		$this->setNonce();

		$data = [ [ 'headerName' => 'Good', 'location' => 'redirect', 'headerValue' => 'value', 'headerSettings' => [], 'type' => 'Good' ] ];

		$result = $this->callApi( 'setting', [ 'headers' => $data ], 'POST' );
		$this->assertEquals( $data, $result->data['settings']['headers'] );
	}

	public function testAliases() {
		$this->setNonce();

		$data = [ 'good.com', '', 'http://domain.com', 'bad com' ];
		$expected = [ 'good.com', 'domain.com', 'badcom' ];

		$result = $this->callApi( 'setting', [ 'aliases' => $data ], 'POST' );
		$this->assertEquals( $expected, $result->data['settings']['aliases'] );

		$result = $this->callApi( 'setting', [ 'aliases' => [] ], 'POST' );
		$this->assertEquals( [], $result->data['settings']['aliases'] );
	}

	public function testPreferredDomain() {
		$this->setNonce();

		$result = $this->callApi( 'setting', [ 'preferred_domain' => 'bad' ], 'POST' );
		$this->assertEquals( '', $result->data['settings']['preferred_domain'] );

		$result = $this->callApi( 'setting', [ 'preferred_domain' => 'www' ], 'POST' );
		$this->assertEquals( 'www', $result->data['settings']['preferred_domain'] );

		$result = $this->callApi( 'setting', [ 'preferred_domain' => 'nowww' ], 'POST' );
		$this->assertEquals( 'nowww', $result->data['settings']['preferred_domain'] );

		$result = $this->callApi( 'setting', array( 'preferred_domain' => '' ), 'POST' );
		$this->assertEquals( '', $result->data['settings']['preferred_domain'] );
	}

	public function testRelocate() {
		$this->setNonce();

		$result = $this->callApi( 'setting', [ 'relocate' => 'domain.com', 'https' => true, 'preferred_domain' => 'www', 'aliases' => [ 'test.com' ] ], 'POST' );
		$this->assertEquals( 'http://domain.com', $result->data['settings']['relocate'] );
		$this->assertEquals( [], $result->data['settings']['aliases'] );
		$this->assertEquals( '', $result->data['settings']['preferred_domain'] );
		$this->assertEquals( false, $result->data['settings']['https'] );

		$result = $this->callApi( 'setting', [ 'relocate' => 'https://domain.com/path' ], 'POST' );
		$this->assertEquals( 'https://domain.com/path', $result->data['settings']['relocate'] );

		$result = $this->callApi( 'setting', [ 'relocate' => '' ], 'POST' );
		$this->assertEquals( '', $result->data['settings']['relocate'] );
	}
}
