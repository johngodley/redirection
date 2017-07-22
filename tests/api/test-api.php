<?php

class ApiTest extends WP_UnitTestCase {
	private function expectDie( $action ) {
		if ( method_exists( $this, 'expectException' ) ) {
			$this->expectException( 'WPDieException' );
			$this->expectExceptionMessageRegExp( '/^\{"error":"Unable to perform action - bad nonce.*/' );
			do_action( 'wp_ajax_red_'.$action );
		}
	}

	private function expectPermission( $action ) {
		if ( method_exists( $this, 'expectException' ) ) {
			$_REQUEST['_wpnonce'] = wp_create_nonce( 'wp_rest' );
			$this->expectException( 'WPDieException' );
			$this->expectExceptionMessageRegExp( '/^\{"error":"No permissions to perform action.*/' );
			do_action( 'wp_ajax_red_'.$action );
		}
	}

	private function setNonce() {
		$this->_setRole( 'administrator' );
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'wp_rest' );
	}

	// Sadly these only work in individual tests - in a loop it checks one and then finishes that test
	public function testNonceLoadSettings() {
		$this->expectDie( 'load_settings' );
	}

	public function testNonceSaveSettings() {
		$this->expectDie( 'save_settings' );
	}

	public function testNonceGetLog() {
		$this->expectDie( 'get_logs' );
	}

	public function testNonceLogAction() {
		$this->expectDie( 'log_action' );
	}

	public function testNonceDeleteAll() {
		$this->expectDie( 'delete_all' );
	}

	public function testNonceDeletePlugin() {
		$this->expectDie( 'delete_plugin' );
	}

	public function testNonceGetModule() {
		$this->expectDie( 'get_module' );
	}

	public function testNonceSetModule() {
		$this->expectDie( 'set_module' );
	}

	public function testNonceGetRedirect() {
		$this->expectDie( 'get_redirect' );
	}

	public function testNonceSetRedirect() {
		$this->expectDie( 'set_redirect' );
	}

	public function testNonceRedirectAction() {
		$this->expectDie( 'redirect_action' );
	}

	public function testNonceGetGroup() {
		$this->expectDie( 'get_group' );
	}

	public function testNonceSetGroup() {
		$this->expectDie( 'set_group' );
	}

	public function testNonceGroupAction() {
		$this->expectDie( 'group_action' );
	}

	public function testPermissionLoadSetting() {
		$this->expectPermission( 'load_settings' );
	}

	public function testPermissionSaveSetting() {
		$this->expectPermission( 'save_settings' );
	}

	public function testPermissionGetLogs() {
		$this->expectPermission( 'get_logs' );
	}

	public function testPermissionLogAction() {
		$this->expectPermission( 'log_action' );
	}

	public function testPermissionDeleteAll() {
		$this->expectPermission( 'delete_all' );
	}

	public function testPermissionDeletePlugin() {
		$this->expectPermission( 'delete_plugin' );
	}

	public function testPermissionGetModule() {
		$this->expectPermission( 'get_module' );
	}

	public function testPermissionSetModule() {
		$this->expectPermission( 'set_module' );
	}

	public function testPermissionGetRedirect() {
		$this->expectPermission( 'get_redirect' );
	}

	public function testPermissionSetRedirect() {
		$this->expectPermission( 'set_redirect' );
	}

	public function testPermissionRedirectAction() {
		$this->expectPermission( 'redirect_action' );
	}

	public function testPermissionGetGroup() {
		$this->expectPermission( 'get_group' );
	}

	public function testPermissionSetGroup() {
		$this->expectPermission( 'set_group' );
	}

	public function testPermissionGroupAction() {
		$this->expectPermission( 'group_action' );
	}
}
