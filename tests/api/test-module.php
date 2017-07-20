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

	public function setUp() {
		parent::setUp();
		$this->group = Red_Group::create( 'group', 1 );
	}

	private function createRedirect() {
		global $wpdb;

		$wpdb->query( "TRUNCATE {$wpdb->prefix}redirection_items" );

		Red_Item::create( array(
			'url'         => '/from',
			'action_data' => '/to',
			'group_id'    => $this->group->get_id(),
			'match_type'  => 'url',
			'action_type' => 'url',
		) );
	}

	private function getWP( $result ) {
		return isset( $result[ 0 ] ) ? $result[ 0 ] : false;
	}

	private function getApache( $result ) {
		return isset( $result[ 1 ] ) ? $result[ 1 ] : false;
	}

	private function getNginx( $result ) {
		return isset( $result[ 2 ] ) ? $result[ 2 ] : false;
	}

	private function hasWP( $result ) {
		return $this->getWP( $result ) ? $this->getWP( $result )->module_id === 1 : false;
	}

	private function hasApache( $result ) {
		return $this->getApache( $result ) ? $this->getApache( $result )->module_id === 2 : false;
	}

	private function hasNginx( $result ) {
		return $this->getNginx( $result ) ? $this->getNginx( $result )->module_id === 3 : false;
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

		$this->assertTrue( $this->hasWP( $result ) );
		$this->assertTrue( $this->hasApache( $result ) );
		$this->assertTrue( $this->hasNginx( $result ) );
		$this->assertEquals( 1, $this->getWP( $result )->redirects );
		$this->assertTrue( isset( $this->getApache( $result )->data->location ) );
		$this->assertTrue( isset( $this->getApache( $result )->data->installed ) );
		$this->assertTrue( isset( $this->getApache( $result )->data->canonical ) );
	}

	public function testBadModule() {
		$this->setNonce();
		$result = $this->get_module( array( 'moduleId' => 'purple' ) );

		$this->assertTrue( $this->hasWP( $result ) );
		$this->assertTrue( $this->hasApache( $result ) );
		$this->assertTrue( $this->hasNginx( $result ) );
	}

	public function testValidModule() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->get_module( array( 'moduleId' => 1 ) );

		$this->assertTrue( $this->hasWP( $result ) );
		$this->assertFalse( $this->hasApache( $result ) );
		$this->assertFalse( $this->hasNginx( $result ) );
	}

	public function testBadExport() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->get_module( array( 'moduleId' => 1, 'moduleType' => 'purple' ) );

		$this->assertTrue( $this->hasWP( $result ) );
		$this->assertEquals( 1, $this->getWP( $result )->redirects );
		$this->assertFalse( isset( $this->getWP( $result )->data ) );
	}

	public function testGoodExport() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->get_module( array( 'moduleId' => 1, 'moduleType' => 'csv' ) );

		$this->assertTrue( $this->hasWP( $result ) );
		$this->assertEquals( 1, $this->getWP( $result )->redirects );
		$this->assertTrue( isset( $this->getWP( $result )->data ) );
	}

	public function testSetBadModule() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->set_module( array( 'module' => 'purple', 'moduleData_location' => 'test' ) );

		$this->assertTrue( isset( $result->error ) );
	}

	public function testSetWPModule() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->set_module( array( 'module' => 1, 'moduleData_location' => 'test' ) );

		$this->assertTrue( $this->hasWP( $result ) );
		$this->assertFalse( isset( $this->getWP( $result )->data ) );
	}

	public function testSetNginxModule() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->set_module( array( 'module' => 3, 'moduleData_location' => 'test' ) );

		$this->assertTrue( $this->hasNginx( $result ) );
		$this->assertFalse( isset( $result->nginx->data ) );
	}

	public function testSetApacheModule() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->set_module( array( 'module' => 2, 'moduleData_location' => 'test', 'moduleData_canonical' => 'www' ) );

		$this->assertTrue( $this->hasApache( $result ) );
		$this->assertTrue( isset( $this->getApache( $result )->data ) );
		$this->assertEquals( 'test', $this->getApache( $result )->data->location );
		$this->assertEquals( 'www', $this->getApache( $result )->data->canonical );
	}

	public function testSetApacheModuleBadCanonical() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->set_module( array( 'module' => 2, 'moduleData_canonical' => 'xxx' ) );

		$this->assertEquals( '', $this->getApache( $result )->data->canonical );
	}

	public function testSetApacheModuleBadLocation() {
		$this->createRedirect();
		$this->setNonce();
		$result = $this->set_module( array( 'module' => 2, 'moduleData_location' => '/tmp' ) );

		$this->assertEquals( '', $this->getApache( $result )->data->location );
	}
}
