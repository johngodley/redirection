<?php

class RedirectionApiModuleTest extends WP_Ajax_UnitTestCase {
	public static $redirection;

	private function get_module( $params = array() ) {
		return json_decode( self::$redirection->ajax_get_module( $params ) );
	}

	private function set_module( $params = array() ) {
		return json_decode( self::$redirection->ajax_set_module( $params ) );
	}

	public static function setupBeforeClass() {
		self::$redirection = Redirection_Admin::init();
	}

	private function createRedirect() {
		Red_Item::create( array(
			'source'     => '/from',
			'target'     => '/to',
			'group_id'   => 1,
			'match'      => 'url',
			'red_action' => 'url',
		) );
	}

	private function setNonce() {
		$this->_setRole( 'administrator' );
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'wp_rest' );
	}

	public function testNonce() {
		$result = $this->get_module();
		$this->assertTrue( isset( $result->error ) );
	}

	public function testPermissions() {
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'wp_rest' );
		$result = $this->get_module();
		$this->assertTrue( isset( $result->error ) );
	}

	public function testLogNoParams() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->get_module();

		$this->assertTrue( isset( $result->wordpress ) );
		$this->assertTrue( isset( $result->apache ) );
		$this->assertTrue( isset( $result->nginx ) );
		$this->assertEquals( 1, $result->wordpress->redirects );
		$this->assertTrue( isset( $result->apache->data->location ) );
		$this->assertTrue( isset( $result->apache->data->installed ) );
		$this->assertTrue( isset( $result->apache->data->canonical ) );
	}

	public function testBadModule() {
		$this->setNonce();
		$result = $this->get_module( array( 'moduleName' => 'purple' ) );

		$this->assertTrue( isset( $result->wordpress ) );
		$this->assertTrue( isset( $result->apache ) );
		$this->assertTrue( isset( $result->nginx ) );
	}

	public function testValidModule() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->get_module( array( 'moduleName' => 'wordpress' ) );

		$this->assertTrue( isset( $result->wordpress ) );
		$this->assertFalse( isset( $result->apache ) );
		$this->assertFalse( isset( $result->nginx ) );
	}

	public function testBadExport() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->get_module( array( 'moduleName' => 'wordpress', 'moduleType' => 'purple' ) );

		$this->assertTrue( isset( $result->wordpress ) );
		$this->assertEquals( 1, $result->wordpress->redirects );
		$this->assertFalse( isset( $result->wordpress->data ) );
	}

	public function testGoodExport() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->get_module( array( 'moduleName' => 'wordpress', 'moduleType' => 'csv' ) );

		$this->assertTrue( isset( $result->wordpress ) );
		$this->assertEquals( 1, $result->wordpress->redirects );
		$this->assertTrue( isset( $result->wordpress->data ) );
	}

	public function testSetBadModule() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->set_module( array( 'module' => 'purple', 'moduleData_location' => 'test' ) );

		$this->assertTrue( isset( $result->wordpress ) );
		$this->assertTrue( isset( $result->apache ) );
		$this->assertTrue( isset( $result->nginx ) );
		$this->assertEquals( '', $result->apache->data->canonical );
	}

	public function testSetWPModule() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->set_module( array( 'module' => 'wordpress', 'moduleData_location' => 'test' ) );

		$this->assertTrue( isset( $result->wordpress ) );
		$this->assertFalse( isset( $result->wordpress->data ) );
	}

	public function testSetNginxModule() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->set_module( array( 'module' => 'nginx', 'moduleData_location' => 'test' ) );

		$this->assertTrue( isset( $result->nginx ) );
		$this->assertFalse( isset( $result->nginx->data ) );
	}

	public function testSetApacheModule() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->set_module( array( 'module' => 'apache', 'moduleData_location' => 'test', 'moduleData_canonical' => 'www' ) );

		$this->assertTrue( isset( $result->apache ) );
		$this->assertTrue( isset( $result->apache->data ) );
		$this->assertEquals( 'test', $result->apache->data->location );
		$this->assertEquals( 'www', $result->apache->data->canonical );
	}

	public function testSetApacheModuleBadCanonical() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->set_module( array( 'module' => 'apache', 'moduleData_canonical' => 'xxx' ) );

		$this->assertEquals( '', $result->apache->data->canonical );
	}

	public function testSetApacheModuleBadLocation() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->set_module( array( 'module' => 'apache', 'moduleData_location' => '/tmp' ) );

		$this->assertEquals( '', $result->apache->data->location );
	}
}
