<?php

class MonitorTest extends WP_UnitTestCase {
	public function setUp(): void {
		parent::setUp();

		$this->group = Group\Group::create( 'group', 1 );

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

	private function getActiveOptions( $group_id = 1, $types = 'post', $associated = '' ) {
		return array(
			'monitor_post' => $group_id,
			'monitor_types' => array( $types ),
			'associated_redirect' => $associated,
		);
	}

	private function getDraftPost( $type = 'post' ) {
		return $this->getPost( 'draft', $type );
	}

	private function getPublishedPost( $type = 'post' ) {
		return $this->getPost( 'publish', $type );
	}

	public function testDraftToPublish() {
		$monitor = new Plugin\Monitor( $this->getActiveOptions() );

		$monitor->pre_post_update( 1, false );
		$this->assertFalse( $monitor->can_monitor_post( $this->getDraftPost(), $this->getPublishedPost() ) );
	}

	public function testPublishToDraft() {
		$monitor = new Plugin\Monitor( $this->getActiveOptions() );

		$monitor->pre_post_update( 1, false );
		$this->assertFalse( $monitor->can_monitor_post( $this->getPublishedPost(), $this->getDraftPost() ) );
	}

	public function testHierarchical() {
		$monitor = new Plugin\Monitor( $this->getActiveOptions() );

		$monitor->pre_post_update( 1, false );
		$this->assertFalse( $monitor->can_monitor_post( $this->getPublishedPost( 'page' ), $this->getPublishedPost() ) );
	}

	public function testPostUpdatedButNoRedirection() {
		$monitor = new Plugin\Monitor( $this->getActiveOptions() );

		$this->assertFalse( $monitor->can_monitor_post( $this->getPublishedPost(), $this->getPublishedPost(), array() ) );
	}

	public function testPostUpdated() {
		$monitor = new Plugin\Monitor( $this->getActiveOptions() );

		$post = $this->factory->post->create_and_get();
		$monitor->pre_post_update( $post->ID, false );
		$this->assertTrue( $monitor->can_monitor_post( $post, $post ) );
	}

	public function testPostNotUpdated() {
		$monitor = new Plugin\Monitor( $this->getActiveOptions( 1, 'page' ) );

		$post = $this->factory->post->create_and_get();
		$monitor->pre_post_update( $post->ID, false );
		$this->assertFalse( $monitor->can_monitor_post( $post, $post ) );
	}

	public function testPageUpdated() {
		$monitor = new Plugin\Monitor( $this->getActiveOptions( 1, 'page' ) );

		$post = $this->factory->post->create_and_get( array( 'post_type' => 'page' ) );
		$monitor->pre_post_update( $post->ID, false );
		$this->assertTrue( $monitor->can_monitor_post( $post, $post ) );
	}

	public function testPageNotUpdated() {
		$monitor = new Plugin\Monitor( $this->getActiveOptions() );

		$post = $this->factory->post->create_and_get( array( 'post_type' => 'page' ) );
		$monitor->pre_post_update( $post->ID, false );
		$this->assertFalse( $monitor->can_monitor_post( $post, $post ) );
	}

	public function testOtherNotUpdated() {
		$monitor = new Plugin\Monitor( $this->getActiveOptions() );

		$post = $this->factory->post->create_and_get( array( 'post_type' => 'product' ) );
		$monitor->pre_post_update( $post->ID, false );
		$this->assertFalse( $monitor->can_monitor_post( $post, $post ) );
	}

	public function testNoAssociated() {
		global $wpdb;

		$monitor = new Plugin\Monitor( $this->getActiveOptions() );
		$total = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" ), 10 );
		$post = $this->factory->post->create();
		$before = parse_url( get_permalink( $post ), PHP_Path );
		$this->factory->post->update_object( $post, array( 'post_name' => 'something' ) );

		$after = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" ), 10 );

		// Should not trigger another
		$this->assertEquals( $total + 1, $after );
	}

	public function testAssociated() {
		global $wpdb;

		$monitor = new Plugin\Monitor( $this->getActiveOptions( 1, 'post', '/amp/' ) );
		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );
		$post = $this->factory->post->create();
		$before = parse_url( get_permalink( $post ), PHP_Path );
		$this->factory->post->update_object( $post, array( 'post_name' => 'something' ) );

		$after = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );
		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		// Should not trigger another
		$this->assertEquals( $total + 2, $after );
		$this->assertEquals( $before.'amp/', $redirect->url );
	}

	public function testTrashUpdated() {
		global $wpdb;

		$monitor = new Plugin\Monitor( $this->getActiveOptions( 1, 'trash' ) );
		$post = $this->factory->post->create( array( 'post_title' => 'trash me' ) );
		$url = parse_url( get_permalink( $post ), PHP_Path );

		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );

		wp_trash_post( $post );

		$after = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );
		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		$this->assertEquals( $total + 1, $after );
		$this->assertEquals( $url, $redirect->url );
		$this->assertEquals( 'disabled', $redirect->status );
	}

	public function testTrashNotUpdated() {
		global $wpdb;

		$monitor = new Plugin\Monitor( $this->getActiveOptions() );
		$post = $this->factory->post->create( array( 'post_title' => 'trash me' ) );
		$url = parse_url( get_permalink( $post ), PHP_Path );

		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );

		wp_trash_post( $post );

		$after = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );

		$this->assertEquals( $total, $after );
	}

	public function testNoHooks() {
		$monitor = new Plugin\Monitor( array( 'monitor_post' => 0, 'monitor_types' => array() ) );

		$this->assertFalse( has_action( 'post_updated', array( $monitor, 'post_updated' ) ) );
		$this->assertFalse( has_action( 'edit_form_advanced', array( $monitor, 'insert_old_post' ) ) );
		$this->assertFalse( has_action( 'edit_page_form', array( $monitor, 'insert_old_post' ) ) );
	}

	public function testHasHooks() {
		$monitor = new Plugin\Monitor( $this->getActiveOptions() );

		$this->assertEquals( 11, has_action( 'post_updated', array( $monitor, 'post_updated' ) ) );
		$this->assertEquals( 10, has_action( 'pre_post_update', array( $monitor, 'pre_post_update' ) ) );
	}

	public function testPermalinkNotChanged() {
		$monitor = new Plugin\Monitor( $this->getActiveOptions() );

		$this->assertFalse( $monitor->has_permalink_changed( false, 'before', 'before' ) );
	}

	public function testPermalinkChanged() {
		$monitor = new Plugin\Monitor( $this->getActiveOptions() );

		$this->assertTrue( $monitor->has_permalink_changed( false, 'before', 'after' ) );
	}

	public function testPermalinkChangedButPreviousIsSite() {
		$monitor = new Plugin\Monitor( $this->getActiveOptions() );

		$this->assertFalse( $monitor->has_permalink_changed( false, '/', '/after' ) );
	}

	public function testRedirectCreated() {
		global $wpdb;

		$monitor = new Plugin\Monitor( $this->getActiveOptions( $this->group->get_id() ) );
		$action = new MockAction();
		$before = parse_url( get_permalink( $this->post_id ), PHP_Path );
		$this->factory->post->update_object( $this->post_id, array( 'post_name' => 'something' ) );
		$after = parse_url( get_permalink( $this->post_id ), PHP_Path );

		add_action( 'redirection_remove_existing', array( $action, 'action' ), 10, 2 );

		// Test a redirect is created
		$this->assertTrue( $monitor->check_for_modified_slug( $this->post_id, $before ) );

		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		$data = $action->get_args();

		$this->assertEquals( $before, $redirect->url );
		$this->assertEquals( $after, $redirect->action_data );
		$this->assertEquals( 301, $redirect->action_code );

		$this->assertEquals( 1, $action->get_call_count() );
		$this->assertEquals( $after, $data[0][0] );
		$this->assertEquals( $this->post_id, $data[0][1] );
	}

	public function testMultipleRedirectsNotCreated() {
		$monitor = new Plugin\Monitor( $this->getActiveOptions() );
		$before = parse_url( get_permalink( $this->post_id ), PHP_Path );

		// Should trigger one redirection
		$this->factory->post->update_object( $this->post_id, array( 'post_name' => 'something' ) );
		$monitor->check_for_modified_slug( $this->post_id, $before );
		$before = parse_url( get_permalink( $this->post_id ), PHP_Path );

		// Should not trigger another
		$this->assertFalse( $monitor->check_for_modified_slug( $this->post_id, $before ) );
	}
}
