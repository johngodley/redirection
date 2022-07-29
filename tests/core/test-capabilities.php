<?php

class CapabilitiesTest extends WP_UnitTestCase {
	public function setUp(): void {
		parent::setUp();

		$this->editor_user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		$this->admin_user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
	}

	public function tearDown(): void {
		parent::tearDown();

		wp_set_current_user( 0 );
	}

	public function testViewerEmptyCaps() {
		$all = Redirection_Capabilities::get_all_capabilities();
		$this->assertTrue( is_array( $all ) );
		$this->assertTrue( empty( $all ) );
	}

	public function testViewerEmptyPages() {
		$all = Redirection_Capabilities::get_available_pages();
		$this->assertTrue( is_array( $all ) );
		$this->assertTrue( empty( $all ) );
	}

	public function testViewerHasAccess() {
		$this->assertFalse( Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_REDIRECT_MANAGE ) );
	}

	public function testAdminAllCaps() {
		wp_set_current_user( $this->admin_user_id );

		$all = Redirection_Capabilities::get_all_capabilities();
		$this->assertTrue( is_array( $all ) );
		$this->assertTrue( ! empty( $all ) );
	}

	public function testAdminAllPages() {
		wp_set_current_user( $this->admin_user_id );

		$all = Redirection_Capabilities::get_available_pages();
		$this->assertTrue( is_array( $all ) );
		$this->assertTrue( ! empty( $all ) );
	}

	public function testAdminHasAccess() {
		wp_set_current_user( $this->admin_user_id );
		$this->assertTrue( Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_REDIRECT_MANAGE ) );
	}

	public function testDefaultCapability() {
		add_filter( Redirection_Capabilities::CAP_PLUGIN, [ $this, 'custom_role' ] );

		$this->assertEquals( 'custom', Redirection_Capabilities::get_plugin_access() );

		remove_filter( Redirection_Capabilities::CAP_PLUGIN, [ $this, 'custom_role' ] );
	}

	public function testEditorHasNoAccess() {
		wp_set_current_user( $this->editor_user_id );
		$this->assertFalse( Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_REDIRECT_MANAGE ) );
	}

	public function testEditorHasAccess() {
		add_filter( Redirection_Capabilities::FILTER_CAPABILITY, [ $this, 'editor_cap' ], 10, 2 );

		wp_set_current_user( $this->editor_user_id );

		// Check editor has access to manage redirects, but not add them
		$this->assertTrue( Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_REDIRECT_MANAGE ) );
		$this->assertFalse( Redirection_Capabilities::has_access( Redirection_Capabilities::CAP_REDIRECT_ADD ) );

		remove_filter( Redirection_Capabilities::FILTER_CAPABILITY, [ $this, 'editor_cap' ], 10, 2 );
	}

	public function editor_cap( $cap, $name ) {
		if ( $name === Redirection_Capabilities::CAP_REDIRECT_MANAGE ) {
			return 'editor';
		}

		return 'manage_options';
	}

	public function custom_role() {
		return 'custom';
	}
}
