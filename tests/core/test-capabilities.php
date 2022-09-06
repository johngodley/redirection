<?php

use Redirection\Plugin;

class CapabilitiesTest extends WP_UnitTestCase {
	public function setUp() : void {
		parent::setUp();

		$this->editor_user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		$this->admin_user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
	}

	public function tearDown() : void {
		parent::tearDown();

		wp_set_current_user( 0 );
	}

	public function testViewerEmptyCaps() {
		$all = Plugin\Capabilities::get_all_capabilities();
		$this->assertTrue( is_array( $all ) );
		$this->assertTrue( empty( $all ) );
	}

	public function testViewerEmptyPages() {
		$all = Plugin\Capabilities::get_available_pages();
		$this->assertTrue( is_array( $all ) );
		$this->assertTrue( empty( $all ) );
	}

	public function testViewerHasAccess() {
		$this->assertFalse( Plugin\Capabilities::has_access( Plugin\Capabilities::CAP_REDIRECT_MANAGE ) );
	}

	public function testAdminAllCaps() {
		wp_set_current_user( $this->admin_user_id );

		$all = Plugin\Capabilities::get_all_capabilities();
		$this->assertTrue( is_array( $all ) );
		$this->assertTrue( ! empty( $all ) );
	}

	public function testAdminAllPages() {
		wp_set_current_user( $this->admin_user_id );

		$all = Plugin\Capabilities::get_available_pages();
		$this->assertTrue( is_array( $all ) );
		$this->assertTrue( ! empty( $all ) );
	}

	public function testAdminHasAccess() {
		wp_set_current_user( $this->admin_user_id );
		$this->assertTrue( Plugin\Capabilities::has_access( Plugin\Capabilities::CAP_REDIRECT_MANAGE ) );
	}

	public function testDefaultCapability() {
		add_filter( Plugin\Capabilities::CAP_PLUGIN, [ $this, 'custom_role' ] );

		$this->assertEquals( 'custom', Plugin\Capabilities::get_plugin_access() );

		remove_filter( Plugin\Capabilities::CAP_PLUGIN, [ $this, 'custom_role' ] );
	}

	public function testEditorHasNoAccess() {
		wp_set_current_user( $this->editor_user_id );
		$this->assertFalse( Plugin\Capabilities::has_access( Plugin\Capabilities::CAP_REDIRECT_MANAGE ) );
	}

	public function testEditorHasAccess() {
		add_filter( Plugin\Capabilities::FILTER_CAPABILITY, [ $this, 'editor_cap' ], 10, 2 );

		wp_set_current_user( $this->editor_user_id );

		// Check editor has access to manage redirects, but not add them
		$this->assertTrue( Plugin\Capabilities::has_access( Plugin\Capabilities::CAP_REDIRECT_MANAGE ) );
		$this->assertFalse( Plugin\Capabilities::has_access( Plugin\Capabilities::CAP_REDIRECT_ADD ) );

		remove_filter( Plugin\Capabilities::FILTER_CAPABILITY, [ $this, 'editor_cap' ], 10, 2 );
	}

	public function editor_cap( $cap, $name ) {
		if ( $name === Plugin\Capabilities::CAP_REDIRECT_MANAGE ) {
			return 'editor';
		}

		return 'manage_options';
	}

	public function custom_role() {
		return 'custom';
	}
}
