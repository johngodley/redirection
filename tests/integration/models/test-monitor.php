<?php

class MonitorTest extends WP_UnitTestCase {
	/**
	 * @var Red_Group
	 */
	private $group;

	/**
	 * @var int
	 */
	private $post_id;

	public function setUp(): void {
		parent::setUp();

		$this->group = Red_Group::create( 'group', 1 );

		update_option( 'siteurl', 'http://example.com' );
		update_option( 'home', 'http://example.com' );

		$this->set_permalink_structure( '/%year%/%monthnum%/%day%/%postname%/' );
		$this->post_id = $this->factory->post->create( array( 'page_slug' => 'thing', 'post_title' => 'Thing' ) );
	}

	private function getPost( $status, $type ) {
		return new WP_Post(
			(object) [
				'post_status' => $status,
				'post_type' => $type,
				'ID' => 1,
			]
		);
	}

	private function getActiveOptions( $group_id = 1, $types = 'post', $associated = '', $monitor_children = false ) {
		return array(
			'monitor_post' => $group_id,
			'monitor_types' => array( $types ),
			'associated_redirect' => $associated,
			'monitor_children' => $monitor_children,
		);
	}

	private function getDraftPost( $type = 'post' ) {
		return $this->getPost( 'draft', $type );
	}

	private function getPublishedPost( $type = 'post' ) {
		return $this->getPost( 'publish', $type );
	}

	public function testDraftToPublish() {
		$monitor = new Red_Monitor( $this->getActiveOptions() );

		$monitor->pre_post_update( 1, [] );
		$this->assertFalse( $monitor->can_monitor_post( $this->getDraftPost(), $this->getPublishedPost() ) );
	}

	public function testPublishToDraft() {
		$monitor = new Red_Monitor( $this->getActiveOptions() );

		$monitor->pre_post_update( 1, [] );
		$this->assertFalse( $monitor->can_monitor_post( $this->getPublishedPost(), $this->getDraftPost() ) );
	}

	public function testHierarchical() {
		$monitor = new Red_Monitor( $this->getActiveOptions() );

		$monitor->pre_post_update( 1, [] );
		$this->assertFalse( $monitor->can_monitor_post( $this->getPublishedPost( 'page' ), $this->getPublishedPost() ) );
	}

	public function testPostUpdatedButNoRedirection() {
		$monitor = new Red_Monitor( $this->getActiveOptions() );

		$this->assertFalse( $monitor->can_monitor_post( $this->getPublishedPost(), $this->getPublishedPost(), array() ) );
	}

	public function testPostUpdated() {
		$monitor = new Red_Monitor( $this->getActiveOptions() );

		$post = $this->factory->post->create_and_get();
		$monitor->pre_post_update( $post->ID, [] );
		$this->assertTrue( $monitor->can_monitor_post( $post, $post ) );
	}

	public function testPostNotUpdated() {
		$monitor = new Red_Monitor( $this->getActiveOptions( 1, 'page' ) );

		$post = $this->factory->post->create_and_get();
		$monitor->pre_post_update( $post->ID, [] );
		$this->assertFalse( $monitor->can_monitor_post( $post, $post ) );
	}

	public function testPageUpdated() {
		$monitor = new Red_Monitor( $this->getActiveOptions( 1, 'page' ) );

		$post = $this->factory->post->create_and_get( array( 'post_type' => 'page' ) );
		$monitor->pre_post_update( $post->ID, [] );
		$this->assertTrue( $monitor->can_monitor_post( $post, $post ) );
	}

	public function testPageNotUpdated() {
		$monitor = new Red_Monitor( $this->getActiveOptions() );

		$post = $this->factory->post->create_and_get( array( 'post_type' => 'page' ) );
		$monitor->pre_post_update( $post->ID, [] );
		$this->assertFalse( $monitor->can_monitor_post( $post, $post ) );
	}

	public function testOtherNotUpdated() {
		$monitor = new Red_Monitor( $this->getActiveOptions() );

		$post = $this->factory->post->create_and_get( array( 'post_type' => 'product' ) );
		$monitor->pre_post_update( $post->ID, [] );
		$this->assertFalse( $monitor->can_monitor_post( $post, $post ) );
	}

	public function testNoAssociated() {
		global $wpdb;

		$monitor = new Red_Monitor( $this->getActiveOptions() );
		$total = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" ), 10 );
		$post = $this->factory->post->create();
		$before = parse_url( get_permalink( $post ), PHP_URL_PATH );
		$this->factory->post->update_object( $post, array( 'post_name' => 'something' ) );

		$after = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" ), 10 );

		// Should not trigger another
		$this->assertEquals( $total + 1, $after );
	}

	public function testAssociated() {
		global $wpdb;

		$monitor = new Red_Monitor( $this->getActiveOptions( 1, 'post', '/amp/' ) );
		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );
		$post = $this->factory->post->create();
		$before = parse_url( get_permalink( $post ), PHP_URL_PATH );
		$this->factory->post->update_object( $post, array( 'post_name' => 'something' ) );

		$after = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );
		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		// Should not trigger another
		$this->assertEquals( $total + 2, $after );
		$this->assertEquals( $before . 'amp/', $redirect->url );
	}

	public function testTrashUpdated() {
		global $wpdb;

		// Trash monitoring requires both 'trash' (to enable the hook) and the post type to monitor
		$monitor = new Red_Monitor( [
			'monitor_post' => 1,
			'monitor_types' => [ 'post', 'trash' ],
			'associated_redirect' => '',
		] );
		$post = $this->factory->post->create( array( 'post_title' => 'trash me' ) );
		$url = parse_url( get_permalink( $post ), PHP_URL_PATH );

		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );

		wp_trash_post( $post );

		$after = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );
		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		$this->assertEquals( $total + 1, $after );
		$this->assertEquals( $url, $redirect->url );
		$this->assertEquals( 'disabled', $redirect->status );
	}

	public function testTrashNotUpdatedForUnmonitoredType() {
		global $wpdb;

		// Trash is enabled but only 'post' is monitored, so trashing a 'page' should not create a redirect
		$monitor = new Red_Monitor( [
			'monitor_post' => 1,
			'monitor_types' => [ 'post', 'trash' ],
			'associated_redirect' => '',
		] );

		$page = $this->factory->post->create( [ 'post_type' => 'page', 'post_title' => 'unmonitored page' ] );
		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );

		wp_trash_post( $page );

		// Verify the page was actually trashed (not deleted)
		$this->assertEquals( 'trash', get_post_status( $page ) );

		$after = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );

		$this->assertEquals( $total, $after );
	}

	public function testTrashNotUpdated() {
		global $wpdb;

		$monitor = new Red_Monitor( $this->getActiveOptions() );
		$post = $this->factory->post->create( array( 'post_title' => 'trash me' ) );
		$url = parse_url( get_permalink( $post ), PHP_URL_PATH );

		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );

		wp_trash_post( $post );

		$after = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );

		$this->assertEquals( $total, $after );
	}

	public function testPostUpdatedWithNullPostExitsCleanly() {
		global $wpdb;

		$monitor = new Red_Monitor( $this->getActiveOptions() );
		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );

		$monitor->post_updated( $this->post_id, null, null );
		$monitor->post_updated( $this->post_id, $this->getPublishedPost(), null );
		$monitor->post_updated( $this->post_id, null, $this->getPublishedPost() );

		$after = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );
		$this->assertEquals( $total, $after );
	}

	public function testNoHooks() {
		$monitor = new Red_Monitor( array( 'monitor_post' => 0, 'monitor_types' => array() ) );

		$this->assertFalse( has_action( 'post_updated', array( $monitor, 'post_updated' ) ) );
		$this->assertFalse( has_action( 'edit_form_advanced', array( $monitor, 'insert_old_post' ) ) );
		$this->assertFalse( has_action( 'edit_page_form', array( $monitor, 'insert_old_post' ) ) );
	}

	public function testHasHooks() {
		$monitor = new Red_Monitor( $this->getActiveOptions() );

		$this->assertEquals( 11, has_action( 'post_updated', array( $monitor, 'post_updated' ) ) );
		$this->assertEquals( 10, has_action( 'pre_post_update', array( $monitor, 'pre_post_update' ) ) );
	}

	public function testPermalinkNotChanged() {
		$monitor = new Red_Monitor( $this->getActiveOptions() );

		$this->assertFalse( $monitor->has_permalink_changed( false, 'before', 'before' ) );
	}

	public function testPermalinkChanged() {
		$monitor = new Red_Monitor( $this->getActiveOptions() );

		$this->assertTrue( $monitor->has_permalink_changed( false, 'before', 'after' ) );
	}

	public function testPermalinkChangedButPreviousIsSite() {
		$monitor = new Red_Monitor( $this->getActiveOptions() );

		$this->assertFalse( $monitor->has_permalink_changed( false, '/', '/after' ) );
	}

	public function testRedirectCreated() {
		global $wpdb;

		$monitor = new Red_Monitor( $this->getActiveOptions( $this->group->get_id() ) );
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
		$this->assertEquals( 301, $redirect->action_code );

		$this->assertEquals( 1, $action->get_call_count() );
		$this->assertEquals( $after, $data[0][0] );
		$this->assertEquals( $this->post_id, $data[0][1] );
	}

	public function testMultipleRedirectsNotCreated() {
		$monitor = new Red_Monitor( $this->getActiveOptions() );
		$before = parse_url( get_permalink( $this->post_id ), PHP_URL_PATH );

		// Should trigger one redirection
		$this->factory->post->update_object( $this->post_id, array( 'post_name' => 'something' ) );
		$monitor->check_for_modified_slug( $this->post_id, $before );
		$before = parse_url( get_permalink( $this->post_id ), PHP_URL_PATH );

		// Should not trigger another
		$this->assertFalse( $monitor->check_for_modified_slug( $this->post_id, $before ) );
	}

	public function testTrashedDataFilterCanModifyRedirect() {
		global $wpdb;

		// Trash monitoring requires both 'trash' (to enable the hook) and the post type to monitor
		$monitor = new Red_Monitor( [
			'monitor_post' => 1,
			'monitor_types' => [ 'post', 'trash' ],
			'associated_redirect' => '',
		] );
		$post = $this->factory->post->create( array( 'post_title' => 'filter test' ) );

		// Add filter to modify the redirect data
		add_filter( 'redirection_monitor_trashed_data', function( $data, $post_id ) {
			$data['action_code'] = 302;
			$data['status'] = 'enabled';
			return $data;
		}, 10, 2 );

		wp_trash_post( $post );

		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		// Verify the filter modified the redirect data
		$this->assertEquals( 302, $redirect->action_code );
		$this->assertEquals( 'enabled', $redirect->status );
	}

	public function testTrashedDataFilterCanSuppressRedirect() {
		global $wpdb;

		// Trash monitoring requires both 'trash' (to enable the hook) and the post type to monitor
		$monitor = new Red_Monitor( [
			'monitor_post' => 1,
			'monitor_types' => [ 'post', 'trash' ],
			'associated_redirect' => '',
		] );
		$post = $this->factory->post->create( array( 'post_title' => 'suppress test' ) );

		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );

		// Add filter to suppress redirect creation by setting url to null
		add_filter( 'redirection_monitor_trashed_data', function( $data, $post_id ) {
			$data['url'] = null;
			return $data;
		}, 10, 2 );

		wp_trash_post( $post );

		$after = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );

		// Verify no redirect was created
		$this->assertEquals( $total, $after );
	}

	public function testMonitorCreatedActionFiresOnTrash() {
		// Trash monitoring requires both 'trash' (to enable the hook) and the post type to monitor
		$monitor = new Red_Monitor( [
			'monitor_post' => 1,
			'monitor_types' => [ 'post', 'trash' ],
			'associated_redirect' => '',
		] );
		$post = $this->factory->post->create( array( 'post_title' => 'action test' ) );
		$url = parse_url( get_permalink( $post ), PHP_URL_PATH );
		$action = new MockAction();

		add_action( 'redirection_monitor_created', array( $action, 'action' ), 10, 3 );

		wp_trash_post( $post );

		// Verify the action was called once
		$this->assertEquals( 1, $action->get_call_count() );

		$args = $action->get_args();

		// Verify the action was called with correct arguments (Red_Item, url, post_id)
		$this->assertInstanceOf( 'Red_Item', $args[0][0] );
		$this->assertEquals( $url, $args[0][1] );
		$this->assertEquals( $post, $args[0][2] );
	}

	public function testChildRedirectsCreatedWhenEnabled() {
		global $wpdb;

		$monitor = new Red_Monitor( $this->getActiveOptions( $this->group->get_id(), 'page', '', true ) );
		$parent = $this->factory->post->create( array( 'post_type' => 'page', 'post_title' => 'Parent', 'post_name' => 'parent' ) );
		$child = $this->factory->post->create( array( 'post_type' => 'page', 'post_title' => 'Child', 'post_name' => 'child', 'post_parent' => $parent ) );
		$before_parent = parse_url( get_permalink( $parent ), PHP_URL_PATH );
		$before_child = parse_url( get_permalink( $child ), PHP_URL_PATH );
		$total = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" ), 10 );

		$this->factory->post->update_object( $parent, array( 'post_name' => 'renamed' ) );
		$after_child = parse_url( get_permalink( $child ), PHP_URL_PATH );

		$after = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" ), 10 );
		$this->assertEquals( $total + 2, $after );

		$redirects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 2" );
		$parent_redirect = $redirects[1];
		$child_redirect = $redirects[0];

		$this->assertEquals( $before_parent, $parent_redirect->url );
		$this->assertEquals( '/renamed/', $parent_redirect->action_data );
		$this->assertEquals( $before_child, $child_redirect->url );
		$this->assertEquals( $after_child, $child_redirect->action_data );
	}

	public function testChildRedirectsNotCreatedWhenDisabled() {
		global $wpdb;

		$monitor = new Red_Monitor( $this->getActiveOptions( $this->group->get_id(), 'page', '', false ) );
		$parent = $this->factory->post->create( array( 'post_type' => 'page', 'post_title' => 'Parent', 'post_name' => 'parent' ) );
		$child = $this->factory->post->create( array( 'post_type' => 'page', 'post_title' => 'Child', 'post_name' => 'child', 'post_parent' => $parent ) );
		$total = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" ), 10 );

		$this->factory->post->update_object( $parent, array( 'post_name' => 'renamed' ) );

		$after = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" ), 10 );
		$this->assertEquals( $total + 1, $after );
	}

	public function testNonHierarchicalChildrenIgnored() {
		global $wpdb;

		$monitor = new Red_Monitor( $this->getActiveOptions( $this->group->get_id(), 'post', '', true ) );
		$post = $this->factory->post->create( array( 'post_title' => 'Post', 'post_name' => 'post' ) );
		$total = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" ), 10 );

		$this->factory->post->update_object( $post, array( 'post_name' => 'renamed' ) );

		$after = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" ), 10 );
		$this->assertEquals( $total + 1, $after );
	}

	public function testGrandchildRedirectsCreated() {
		global $wpdb;

		$monitor = new Red_Monitor( $this->getActiveOptions( $this->group->get_id(), 'page', '', true ) );
		$parent = $this->factory->post->create( array( 'post_type' => 'page', 'post_title' => 'Parent', 'post_name' => 'parent' ) );
		$child = $this->factory->post->create( array( 'post_type' => 'page', 'post_title' => 'Child', 'post_name' => 'child', 'post_parent' => $parent ) );
		$grandchild = $this->factory->post->create( array( 'post_type' => 'page', 'post_title' => 'Grandchild', 'post_name' => 'grandchild', 'post_parent' => $child ) );
		$before_parent = parse_url( get_permalink( $parent ), PHP_URL_PATH );
		$before_child = parse_url( get_permalink( $child ), PHP_URL_PATH );
		$before_grandchild = parse_url( get_permalink( $grandchild ), PHP_URL_PATH );
		$total = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" ), 10 );

		$this->factory->post->update_object( $parent, array( 'post_name' => 'renamed' ) );
		$after_child = parse_url( get_permalink( $child ), PHP_URL_PATH );
		$after_grandchild = parse_url( get_permalink( $grandchild ), PHP_URL_PATH );

		$after = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" ), 10 );
		$this->assertEquals( $total + 3, $after );

		$redirects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 3" );
		$parent_redirect = $redirects[2];
		$child_redirect = $redirects[1];
		$grandchild_redirect = $redirects[0];

		$this->assertEquals( $before_parent, $parent_redirect->url );
		$this->assertEquals( '/renamed/', $parent_redirect->action_data );
		$this->assertEquals( $before_child, $child_redirect->url );
		$this->assertEquals( $after_child, $child_redirect->action_data );
		$this->assertEquals( $before_grandchild, $grandchild_redirect->url );
		$this->assertEquals( $after_grandchild, $grandchild_redirect->action_data );
	}

	public function testChildExistingRedirectDisabled() {
		global $wpdb;

		$monitor = new Red_Monitor( $this->getActiveOptions( $this->group->get_id(), 'page', '', true ) );
		$parent = $this->factory->post->create( array( 'post_type' => 'page', 'post_title' => 'Parent', 'post_name' => 'parent' ) );
		$child = $this->factory->post->create( array( 'post_type' => 'page', 'post_title' => 'Child', 'post_name' => 'child', 'post_parent' => $parent ) );
		$total = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" ), 10 );

		Red_Item::create( array(
			'url' => '/renamed/child/',
			'action_data' => array( 'url' => '/somewhere/' ),
			'match_type' => 'url',
			'action_type' => 'url',
			'action_code' => 301,
			'group_id' => $this->group->get_id(),
		) );

		$this->factory->post->update_object( $parent, array( 'post_name' => 'renamed' ) );

		$after = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" ), 10 );
		$this->assertEquals( $total + 3, $after );

		$existing = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items WHERE url = '/renamed/child/' ORDER BY id ASC LIMIT 1" );
		$this->assertEquals( 'disabled', $existing->status );
	}
}
