<?php

// get_filtered is handled by api/test-redirect.php
class RedirectTest extends WP_UnitTestCase {
	public function setUp(): void {
		global $wpdb;

		$this->group = Red_Group::create( 'group', 1 );
		$wpdb->query( "TRUNCATE {$wpdb->prefix}redirection_items" );

		red_set_options( [ 'database' => REDIRECTION_DB_VERSION ] );
	}

	private function getRedirectData( $params = [] ) {
		return array_merge( [
			'url' => 'url',
			'match_type' => 'url',
			'action_type' => 'url',
			'group_id' => $this->group->get_id(),
		], $params );
	}

	private function createRedirect( $params = [] ) {
		return Red_Item::create( $this->getRedirectData( $params ) );
	}

	private function capturedRedirect() {
		add_filter( 'wp_redirect', array( $this, 'captureRedirectResult' ) );
	}

	public function captureRedirectResult( $url ) {
		$this->captured_url = $url;
		return false;
	}

	private function resetCaptured() {
		$this->captured_url = false;
		remove_filter( 'wp_redirect', array( $this, 'captureRedirectResult' ) );
	}

	private function get_item_data() {
		return [
			'id' => 1,
			'url' => 'url',
			'match_url' => 'url',
			'regex' => 1,
			'action_data' => 'data',
			'action_code' => 301,
			'action_type' => 'url',
			'match_type' => 'url',
			'title' => 'title',
			'last_access' => '2017-01-02 01:02:03',
			'last_count' => 5,
			'tracking' => 1,
			'status' => 'enabled',
			'position' => 5,
			'group_id' => 2,
		];
	}

	public function testConstruct() {
		$data = $this->get_item_data();

		$item = new Red_Item( (object) $data );

		$this->assertEquals( 'url', $item->get_url() );
		$this->assertEquals( 'url', $item->get_match_url() );
		$this->assertEquals( 1, $item->get_id() );
		$this->assertTrue( $item->is_regex() );
		$this->assertEquals( 'data', $item->get_action_data() );
		$this->assertEquals( 'url', $item->get_match_type() );
		$this->assertEquals( 'url', $item->get_action_type() );
		$this->assertEquals( 'title', $item->get_title() );
		$this->assertEquals( 301, $item->get_action_code() );
		$this->assertTrue( $item->is_enabled() );
		$this->assertEquals( 2, $item->get_group_id() );
		$this->assertEquals( 5, $item->get_hits() );
		$this->assertEquals( 5, $item->get_position() );
		$this->assertEquals( mysql2date( 'U', '2017-01-02 01:02:03' ), $item->get_last_hit() );

		// The old regex column should be copied to the source flags
		$this->assertTrue( $item->source_flags->is_regex() );
	}

	public function testConstructNoSourceDefaults() {
		// Test that with no flag data at all we still get the defaults
		red_set_options( [ 'flag_case' => true, 'flag_query' => 'ignore', 'flag_trailing' => true, 'flag_regex' => false ] );

		$data = $this->get_item_data();
		$data['regex'] = false;

		$item = new Red_Item( (object) $data );

		$this->assertTrue( $item->source_flags->is_query_ignore() );
		$this->assertTrue( $item->source_flags->is_ignore_case() );
		$this->assertTrue( $item->source_flags->is_ignore_trailing() );
	}

	public function testConstructOverride() {
		// Check that with some flags we get defaults
		red_set_options( [ 'flag_case' => true, 'flag_query' => 'ignore', 'flag_trailing' => true ] );

		$data = $this->get_item_data();
		$data['match_data'] = json_encode( [ 'source' => [ 'flag_case' => false ] ] );

		$item = new Red_Item( (object) $data );

		$this->assertTrue( $item->source_flags->is_query_ignore() );
		$this->assertFalse( $item->source_flags->is_ignore_case() );
		$this->assertTrue( $item->source_flags->is_ignore_trailing() );
	}

	public function testBadAllForModule() {
		$items = Red_Item::get_all_for_module( 'cats' );
		$this->assertEquals( [], $items );
	}

	public function testAllForModule() {
		$disabled_group = Red_Group::create( 'group', 1 );
		$disabled_group->disable();

		$item1 = $this->createRedirect( array( 'url' => '/url1' ) );
		$item2 = $this->createRedirect( array( 'url' => '/url2' ) );
		$item3 = $this->createRedirect( array( 'url' => 'url3', 'group_id' => $disabled_group->get_id() ) );

		$items = Red_Item::get_all_for_module( 1 );

		$this->assertEquals( 2, count( $items ) );
		$this->assertEquals( '/url1', $items[0]->get_url() );
		$this->assertEquals( '/url2', $items[1]->get_url() );
	}

	public function testGetForUrlNone() {
		$items = Red_Item::get_for_url( '' );
		$this->assertEmpty( $items );
	}

	public function testGetForUrl() {
		$this->createRedirect( array( 'url' => '/cats' ) );
		$this->createRedirect( array( 'url' => '/dogs' ) );

		$items = Red_Item::get_for_url( '/cats' );

		$this->assertEquals( 1, count( $items ) );
		$this->assertEquals( '/cats', $items[0]->get_url() );
	}

	public function testGetForRegexOrder() {
		$this->createRedirect( array( 'url' => '/cats*', 'regex' => true ) );
		$this->createRedirect( array( 'url' => '/cats*', 'regex' => true ) );

		$items = Red_Item::get_for_url( '/cats1' );

		$this->assertEquals( 2, count( $items ) );
	}

	public function testBadGetId() {
		$item = Red_Item::get_by_id( 'cat' );
		$this->assertFalse( $item );
	}

	public function testGetId() {
		$redirect = $this->createRedirect();
		$item = Red_Item::get_by_id( $redirect->get_id() );
		$this->assertEquals( $redirect, $item );
	}

	public function testNoDisableMatches() {
		$item = $this->createRedirect( array( 'url' => 'cats' ) );
		Red_Item::disable_where_matches( '/dogs' );
		$item = Red_Item::get_by_id( $item->get_id() );
		$this->assertTrue( $item->is_enabled() );
	}

	public function testDisableMatches() {
		$item = $this->createRedirect( array( 'url' => '/cats' ) );
		Red_Item::disable_where_matches( '/cats' );
		$item = Red_Item::get_by_id( $item->get_id() );
		$this->assertFalse( $item->is_enabled() );
	}

	public function testDelete() {
		$item = $this->createRedirect( array( 'url' => 'cats' ) );
		$item->delete();
		$this->assertFalse( Red_Item::get_by_id( $item->get_id() ) );
	}

	// Good creates have already been tested with createRedirect and with test-redirect-sanitize.php
	public function testBadCreate() {
		$item = $this->createRedirect( array( 'group_id' => 'cats' ) );
		$this->assertWPError( $item );
	}

	public function testBadUpdate() {
		$item = $this->createRedirect();
		$result = $item->update( array( 'url' => '/dogs', 'group_id' => 'cat' ) );

		$this->assertWPError( $result );
	}

	public function testGoodUpdate() {
		$item = $this->createRedirect();
		$result = $item->update( array( 'url' => '/dogs', 'group_id' => $this->group->get_id(), 'match_type' => 'url', 'action_type' => 'url' ) );

		$this->assertTrue( $result );
		$this->assertEquals( '/dogs', $item->get_url() );
	}

	public function testUpdateRegexMigrate() {
		$item = $this->createRedirect();
		$result = $item->update( array( 'url' => '/dogs', 'group_id' => $this->group->get_id(), 'match_type' => 'url', 'action_type' => 'url', 'regex' => true ) );

		$this->assertTrue( $result );
		$this->assertTrue( $item->is_regex() );
		$this->assertTrue( $item->source_flags->is_regex() );
	}

	public function testVisitUnrecorded() {
		global $wpdb;

		$item = $this->createRedirect();
		$before = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_logs" );

		$options = red_get_options();
		$options['expire_redirect'] = -1;
		update_option( 'redirection_options', $options );

		$item->visit( '/blob', '/target' );
		$after = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_logs" );

		$this->assertEquals( 1, $item->get_hits() );
		$item = Red_Item::get_by_id( $item->get_id() );
		$this->assertEquals( 1, $item->get_hits() );
		$this->assertEquals( $after, $before );
	}

	public function testVisit() {
		global $wpdb;

		$before = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_logs" );

		$options = red_get_options();
		$options['expire_redirect'] = 0;
		update_option( 'redirection_options', $options );

		$item = $this->createRedirect();
		$item->visit( '/blob', '/target' );

		$after = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_logs" );

		$this->assertEquals( 1, $item->get_hits() );
		$item = Red_Item::get_by_id( $item->get_id() );
		$this->assertEquals( 1, $item->get_hits() );
		$this->assertEquals( $after, $before + 1 );
	}

	public function testReset() {
		$item = $this->createRedirect();
		$item->visit( '/blob', '/target' );
		$item->reset();

		$this->assertEquals( 0, $item->get_hits() );
		$item = Red_Item::get_by_id( $item->get_id() );
		$this->assertEquals( 0, $item->get_hits() );
	}

	public function testEnable() {
		$item = $this->createRedirect();
		$item->disable();
		$item->enable();

		$this->assertTrue( $item->is_enabled() );
		$item = Red_Item::get_by_id( $item->get_id() );
		$this->assertTrue( $item->is_enabled() );
	}

	public function testDisable() {
		$item = $this->createRedirect();
		$item->disable();

		$this->assertFalse( $item->is_enabled() );
		$item = Red_Item::get_by_id( $item->get_id() );
		$this->assertFalse( $item->is_enabled() );
	}

	public function testNoMatch() {
		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source', 'action_data' => '/target', 'action_code' => 301 ) );
		$this->assertFalse( $item->get_match( '/source2' ) );
	}

	public function testRegexNoMatch() {
		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '/source.*', 'action_data' => '/target', 'action_code' => 301 ) );
		$this->assertFalse( $item->get_match( '/cat' ) );
	}

	public function testMatchDisabled() {
		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source', 'action_data' => '/target', 'action_code' => 301, 'status' => 'disabled' ) );
		$this->assertFalse( $item->get_match( '/source' ) );
	}

	public function testMatchWithoutTargetMiss() {
		// A match with an extra check (useragent) that doesn't hit, and an action with no target, should return false
		$item = new Red_Item( [ 'match_type' => 'agent', 'id' => 1, 'action_data' => serialize( [ 'agent' => 'agent' ] ), 'action_type' => 'error', 'url' => '/cat', 'action_code' => 301 ] );
		$this->assertFalse( $item->get_match( '/cat' ) );
	}

	public function testMatchWithoutTargetHit() {
		// A match with an extra check (useragent) that does hit, and an action with no target, should return an action
		$item = new Red_Item( [ 'match_type' => 'agent', 'id' => 1, 'action_data' => serialize( [ 'agent' => 'agent' ] ), 'action_type' => 'error', 'url' => '/cat', 'action_code' => 301 ] );
		$_SERVER['HTTP_USER_AGENT'] = 'agent';
		$this->assertTrue( $item->get_match( '/cat' ) !== false );
	}

	// Test an action with an empty match target returns false
	public function testMatchWithEmptyTarget() {
		$item = new Red_Item( [ 'match_type' => 'agent', 'id' => 1, 'action_data' => serialize( [ 'agent' => 'agent', 'url_notfrom' => '' ] ), 'action_type' => 'url', 'url' => '/cat', 'action_code' => 301 ] );
		$this->assertFalse( $item->get_match( '/cat' ) );
	}

	public function testMatch() {
		global $wpdb;

		Red_Redirect_Log::delete_all();
		$this->capturedRedirect();
		$action = new MockAction();

		add_action( 'redirection_url_target', array( $action, 'action' ), 10, 2 );

		$item = new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source', 'action_data' => '/target', 'action_code' => 301, 'status' => 'enabled' ] );
		$real_action = $item->get_match( '/source' );
		$real_action->run();

		$data = $action->get_args();

		$this->assertEquals( '/target', $this->captured_url );   // URL is redirected
		$this->assertEquals( 1, $action->get_call_count() );
		$this->assertEquals( '/target', $data[0][0] );
		$this->assertEquals( '/source', $data[0][1] );
		$this->assertEquals( 1, $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_logs" ) );

		$this->resetCaptured();
	}

	public function testRegexMatch() {
		$this->capturedRedirect();

		$item = new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '/source(.*)', 'match_url' => 'regex', 'action_data' => '/target$1', 'action_code' => 301, 'status' => 'enabled' ] );
		$action = $item->get_match( '/source45' );
		$action->run();

		$this->assertEquals( '/target45', $this->captured_url );   // URL is redirected
		$this->resetCaptured();
	}

	public function testMatchQueryIgnore() {
		$this->capturedRedirect();
		$item = $this->getRedirectData( [
			'id' => 1,
			'regex' => false,
			'url' => '/source',
			'match_url' => '/source',
			'match_data' => wp_json_encode( [ 'source' => [ 'flag_query' => 'ignore' ] ] ),
			'action_data' => '/target',
			'action_code' => 301,
			'status' => 'enabled',
		] );

		$item = new Red_Item( (object) $item );

		$action = $item->get_match( '/source?thing=yes' );
		$action->run();
		$this->assertEquals( '/target', $this->captured_url );   // URL is redirected
		$this->resetCaptured();
	}

	public function testMatchQueryUrl() {
		$this->capturedRedirect();
		$item = $this->getRedirectData( [
			'id' => 1,
			'regex' => false,
			'url' => '/TEST/?cat=1',
			'match_url' => '/test',
			'match_data' => wp_json_encode( [ 'source' => [ 'flag_case' => true, 'flag_trailing' => true ] ] ),
			'action_data' => '/target',
			'action_code' => 301,
			'status' => 'enabled',
		] );

		$item = new Red_Item( (object) $item );

		$action = $item->get_match( '/TEST/?cat=1' );
		$action->run();
		$this->assertEquals( '/target', $this->captured_url );   // URL is redirected
		$this->resetCaptured();
	}

	public function testDoesntMatchQueryUrl() {
		$this->capturedRedirect();
		$item = $this->getRedirectData( [
			'id' => 1,
			'regex' => false,
			'url' => '/TEST/?cat=1',
			'match_url' => '/test',
			'match_data' => wp_json_encode( [ 'source' => [ 'flag_case' => true, 'flag_trailing' => true ] ] ),
			'action_data' => '/target',
			'action_code' => 301,
			'status' => 'enabled',
		] );

		$item = new Red_Item( (object) $item );

		$this->assertFalse( $item->get_match( '/test' ) );
		$this->resetCaptured();
	}

	public function testMatchAddQuery() {
		$this->capturedRedirect();
		$item = $this->getRedirectData( [
			'id' => 1,
			'regex' => false,
			'url' => '/source',
			'match_url' => '/source',
			'match_data' => wp_json_encode( [ 'source' => [ 'flag_query' => 'pass' ] ] ),
			'action_data' => '/target',
			'action_code' => 301,
			'status' => 'enabled',
		] );

		$item = new Red_Item( (object) $item );

		$action = $item->get_match( '/source?thing=yes' );
		$action->run();

		$this->assertEquals( '/target?thing=yes', $this->captured_url );   // URL is redirected
		$this->resetCaptured();
	}

	public function testDisableWhereMatches() {
		global $wpdb;

		$item = Red_Item::create( [
			'url'         => '/from',
			'action_data' => array( 'url' => '/to' ),
			'group_id'    => $this->group->get_id(),
			'match_type'  => 'url',
			'action_type' => 'url',
		] );

		$before = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		Red_Item::disable_where_matches( '/from' );

		$after = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		$this->assertEquals( 'enabled', $before->status );
		$this->assertEquals( 'disabled', $after->status );
	}

	public function testLoadMatcherNoType() {
		$item = new Red_Item( (object) [ 'match_type' => 'url' ] );
		$this->assertEquals( 'url', $item->match->get_type() );
	}

	public function testLoadMatcherType() {
		$item = new Red_Item( (object) [ 'match_type' => 'login' ] );
		$this->assertEquals( 'login', $item->match->get_type() );
	}

	public function testLoadActionNoType() {
		$item = new Red_Item( (object) [
			'match_type' => 'url',
			'action_code' => 6,
		] );
		$this->assertEquals( 'nothing', $item->action->get_type() );
		$this->assertEquals( 6, $item->action->get_code() );
	}

	public function testLoadActionType() {
		$item = new Red_Item( (object) [
			'match_type' => 'url',
			'action_type' => 'pass',
			'action_code' => 6,
		] );
		$this->assertEquals( 'pass', $item->action->get_type() );
		$this->assertEquals( 6, $item->action->get_code() );
	}

	public function testLoadFlagsDefaultRegex() {
		$item = new Red_Item( (object) [
			'regex' => true,
			'match_type' => 'url',
		] );
		$this->assertTrue( $item->source_flags->is_regex() );
	}

	public function testLoadFlags() {
		$flags = [ 'source' => [ 'flag_case' => true ] ];
		$item = new Red_Item( (object) [
			'match_type' => 'url',
			'match_data' => wp_json_encode( $flags ),
		] );
		$this->assertTrue( $item->source_flags->is_ignore_case() );
	}

	public function testLoadUrlPre4() {
		global $wpdb;

		// Insert directly so we have full control over the version support
		$wpdb->insert( $wpdb->prefix . 'redirection_items', [
			'url' => 'pre4',
			'group_id' => $this->group->get_id(),
		] );

		// With 4.0 support (default) this wont match anything as no match_url
		$this->assertEquals( 0, count( Red_Item::get_for_url( 'pre4' ) ) );

		red_set_options( [ 'database' => '3.9' ] );

		// Switch to <4.0 and we will match
		$this->assertEquals( 1, count( Red_Item::get_for_url( 'pre4' ) ) );
	}

	public function testLoadUrlPost4() {
		global $wpdb;

		// Insert directly so we have full control over the version support
		$wpdb->insert( $wpdb->prefix . 'redirection_items', [
			'match_url' => 'post4',
			'group_id' => $this->group->get_id(),
		] );

		// With 4.0 support (default) this will match
		$this->assertEquals( 1, count( Red_Item::get_for_url( 'post4' ) ) );

		red_set_options( [ 'database' => '3.9' ] );

		// Switch to <4.0 and we won't match
		$this->assertEquals( 0, count( Red_Item::get_for_url( 'post4' ) ) );
	}

	public function testLoadUrlRegexPost4() {
		$this->createRedirect( [
			'url' => '/cat.*',
			'regex' => true,
		] );

		$items = Red_Item::get_for_url( '/cat4' );
		$this->assertEquals( 1, count( $items ) );
	}

	public function testCreateSavesJson() {
		global $wpdb;

		red_set_options( [ 'flag_case' => false, 'flag_regex' => false, 'flag_query' => 'ignore', 'flag_trailing' => true ] );
		$expected = [
			'source' => [
				'flag_case' => true,
				'flag_query' => 'exact',
				'flag_trailing' => false,
			],
		];
		$item = $this->createRedirect( [
			'url' => '/test',
			'match_data' => $expected,
		] );

		$json = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}redirection_items WHERE id=%d", $item->get_id() ) );
		$this->assertEquals( $expected, json_decode( $json->match_data, true ) );
	}

	public function testMatchData() {
		// This returns with defaults
		red_set_options( [ 'flag_case' => false, 'flag_regex' => false, 'flag_query' => 'ignore', 'flag_trailing' => true ] );

		$item = new Red_Item( (object) [ 'match_data' => wp_json_encode( [ 'source' => [ 'flag_case' => true ] ] ) ] );
		$data = $item->get_match_data();

		$this->assertEquals( [ 'source' => [ 'flag_case' => true, 'flag_regex' => false, 'flag_query' => 'ignore', 'flag_trailing' => true ] ], $data );
	}

	public function testMatchDataSaved() {
		global $wpdb;

		// This returns without defaults
		red_set_options( [ 'flag_case' => false, 'flag_regex' => false, 'flag_query' => 'exact', 'flag_trailing' => true ] );

		$item = $this->createRedirect( [
			'url' => '/cat.*',
			'regex' => true,
			'match_data' => [ 'source' => [ 'flag_case' => true, 'flag_query' => 'exact', 'flag_trailing' => true ] ],
		] );

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT match_data FROM {$wpdb->prefix}redirection_items WHERE id=%d", $item->get_id() ) );
		$expected = wp_json_encode( [ 'source' => [ 'flag_case' => true ] ] );

		$this->assertEquals( $expected, $row->match_data );
	}
}
