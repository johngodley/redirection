<?php

class MonitorTest extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();

		update_option( 'siteurl', 'http://example.com' );
		update_option( 'home', 'http://example.com' );

		$this->set_permalink_structure( '/%year%/%monthnum%/%day%/%postname%/' );
		$this->post_id = $this->factory->post->create( array( 'page_slug' => 'thing', 'post_title' => 'Thing' ) );
	}

	private function getPost( $status, $type ) {
		return (object) array(
			'post_status' => $status,
			'post_type' => $type,
			'ID' => 1,
		);
	}

	private function getDraftPost( $type = 'post' ) {
		return $this->getPost( 'draft', $type );
	}

	private function getPublishedPost( $type = 'post' ) {
		return $this->getPost( 'publish', $type );
	}

	private function getFormData() {
		return array( 'redirection_slug' => true, 'ID' => 1 );
	}

	public function testDraftToPublish() {
		$monitor = new Red_Monitor( array( 'monitor_post' => 1 ) );

		$this->assertFalse( $monitor->can_monitor_post( $this->getDraftPost(), $this->getPublishedPost(), $this->getFormData() ) );
	}

	public function testPublishToDraft() {
		$monitor = new Red_Monitor( array( 'monitor_post' => 1 ) );

		$this->assertFalse( $monitor->can_monitor_post( $this->getPublishedPost(), $this->getDraftPost(), $this->getFormData() ) );
	}

	public function testHierarchical() {
		$monitor = new Red_Monitor( array( 'monitor_post' => 1 ) );

		$this->assertFalse( $monitor->can_monitor_post( $this->getPublishedPost( 'page' ), $this->getPublishedPost(), $this->getFormData() ) );
	}

	public function testPostUpdatedButNoRedirection() {
		$monitor = new Red_Monitor( array( 'monitor_post' => 1 ) );

		$this->assertFalse( $monitor->can_monitor_post( $this->getPublishedPost(), $this->getPublishedPost(), array() ) );
	}

	public function testPostUpdated() {
		$monitor = new Red_Monitor( array( 'monitor_post' => 1 ) );

		$this->assertTrue( $monitor->can_monitor_post( $this->getPublishedPost(), $this->getPublishedPost(), $this->getFormData() ) );
	}

	public function testNoHooks() {
		$monitor = new Red_Monitor( array( 'monitor_post' => 0 ) );

		$this->assertFalse( has_action( 'post_updated', array( $monitor, 'post_updated' ) ) );
		$this->assertFalse( has_action( 'edit_form_advanced', array( $monitor, 'insert_old_post' ) ) );
		$this->assertFalse( has_action( 'edit_page_form', array( $monitor, 'insert_old_post' ) ) );
	}

	public function testHasHooks() {
		$monitor = new Red_Monitor( array( 'monitor_post' => 99 ) );

		$this->assertEquals( 11, has_action( 'post_updated', array( $monitor, 'post_updated' ) ) );
		$this->assertEquals( 10, has_action( 'edit_form_advanced', array( $monitor, 'insert_old_post' ) ) );
		$this->assertEquals( 10, has_action( 'edit_page_form', array( $monitor, 'insert_old_post' ) ) );
	}

	public function testPermalinkNotChanged() {
		$monitor = new Red_Monitor( array( 'monitor_post' => 1 ) );

		$this->assertFalse( $monitor->has_permalink_changed( false, 'before', 'before' ) );
	}

	public function testPermalinkChanged() {
		$monitor = new Red_Monitor( array( 'monitor_post' => 1 ) );

		$this->assertTrue( $monitor->has_permalink_changed( false, 'before', 'after' ) );
	}

	public function testPermalinkChangedButPreviousIsSite() {
		$monitor = new Red_Monitor( array( 'monitor_post' => 1 ) );

		$this->assertFalse( $monitor->has_permalink_changed( false, '/', '/' ) );
	}

	public function testRedirectCreated() {
		global $wpdb;

		$monitor = new Red_Monitor( array( 'monitor_post' => 1 ) );
		$action = new MockAction();
		$before = parse_url( get_permalink( $this->post_id ), PHP_URL_PATH );
		$this->factory->post->update_object( $this->post_id, array( 'post_name' => 'something' ) );
		$after = parse_url( get_permalink( $this->post_id ), PHP_URL_PATH );

		add_action( 'redirection_remove_existing', array( $action, 'action' ), 10, 2 );

		// Test a redirect is created
		$this->assertTrue( $monitor->check_for_modified_slug( $this->post_id, $before ) );

		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		$data = $action->get_args();

		$this->assertEquals( $before, $redirect->url );
		$this->assertEquals( $after, $redirect->action_data );

		$this->assertEquals( 1, $action->get_call_count() );
		$this->assertEquals( $after, $data[0][0] );
		$this->assertEquals( $this->post_id, $data[0][1] );
	}

	public function testMultipleRedirectsNotCreated() {
		$monitor = new Red_Monitor( array( 'monitor_post' => 1 ) );
		$before = parse_url( get_permalink( $this->post_id ), PHP_URL_PATH );

		// Should trigger one redirection
		$this->factory->post->update_object( $this->post_id, array( 'post_name' => 'something' ) );
		$monitor->check_for_modified_slug( $this->post_id, $before );
		$before = parse_url( get_permalink( $this->post_id ), PHP_URL_PATH );

		// Should not trigger another
		$this->assertFalse( $monitor->check_for_modified_slug( $this->post_id, $before ) );
	}
}
