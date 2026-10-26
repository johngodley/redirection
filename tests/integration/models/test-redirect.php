<?php

// get_filtered is handled by api/test-redirect.php
class RedirectTest extends WP_UnitTestCase {
	/**
	 * @var Red_Group
	 */
	private $group;

	/**
	 * @var string|false
	 */
	private $captured_url;

	public function setUp(): void {
		global $wpdb;

		$this->group = Red_Group::create( 'group', 1 );
		$wpdb->query( "TRUNCATE {$wpdb->prefix}redirection_items" );

		Red_Options::save( [ 'database' => REDIRECTION_DB_VERSION ] );
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

	public function replaceRedirectTarget() {
		return 'https://plugin.example/target';
	}

	private function resetCaptured() {
		$this->captured_url = false;
		remove_filter( 'wp_redirect', array( $this, 'captureRedirectResult' ) );
	}

	private function monitorAction( $hook ) {
		$action = new MockAction();

		add_action( $hook, array( $action, 'action' ), 10, 2 );

		return $action;
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
		Red_Options::save( [ 'flag_case' => true, 'flag_query' => 'ignore', 'flag_trailing' => true, 'flag_regex' => false ] );

		$data = $this->get_item_data();
		$data['regex'] = false;

		$item = new Red_Item( (object) $data );

		$this->assertTrue( $item->source_flags->is_query_ignore() );
		$this->assertTrue( $item->source_flags->is_ignore_case() );
		$this->assertTrue( $item->source_flags->is_ignore_trailing() );
	}

	public function testConstructOverride() {
		// Check that with some flags we get defaults
		Red_Options::save( [ 'flag_case' => true, 'flag_query' => 'ignore', 'flag_trailing' => true ] );

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

	public function testCreateActionReturnsIdAndRedirect() {
		$action = $this->monitorAction( 'redirection_redirect_updated' );
		$item = $this->createRedirect();
		$args = $action->get_args();

		$this->assertInstanceOf( Red_Item::class, $item );
		$this->assertEquals( 1, $action->get_call_count() );
		$this->assertEquals( $item->get_id(), $args[0][0] );
		$this->assertInstanceOf( Red_Item::class, $args[0][1] );
		$this->assertEquals( $item->get_id(), $args[0][1]->get_id() );
	}

	public function testUpdateActionReturnsIdAndRedirect() {
		$action = $this->monitorAction( 'redirection_redirect_updated' );
		$item = $this->createRedirect();

		$action->reset();
		$result = $item->update( array( 'url' => '/dogs', 'group_id' => $this->group->get_id(), 'match_type' => 'url', 'action_type' => 'url' ) );
		$args = $action->get_args();

		$this->assertTrue( $result );
		$this->assertEquals( 1, $action->get_call_count() );
		$this->assertEquals( $item->get_id(), $args[0][0] );
		$this->assertInstanceOf( Red_Item::class, $args[0][1] );
		$this->assertEquals( $item->get_id(), $args[0][1]->get_id() );
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

		Red_Options::save( [ 'expire_redirect' => -1 ] );

		$item->visit( '/blob', '/target' );
		$after = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_logs" );

		$this->assertEquals( 1, $item->get_hits() );
		$item = Red_Item::get_by_id( $item->get_id() );

		$this->assertEquals( 1, $item->get_hits() );
		$this->assertEquals( $after, $before );
		$this->assertEquals( 0, $after );
	}

	public function testVisit() {
		global $wpdb;

		$before = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_logs" );

		Red_Options::save( [ 'expire_redirect' => 0 ] );

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

	public function testRegexMatchCannotRedirectOffsite() {
		$this->capturedRedirect();

		// An encoded slash decodes to a real slash, making the captured value start with a slash
		$request = new Red_Url_Request( '/old/%2Ftest.example/login' );
		$item = new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '^/old/(.*)$', 'match_url' => 'regex', 'action_data' => '/$1', 'action_code' => 301, 'status' => 'enabled' ] );
		$action = $item->get_match( $request->get_decoded_url(), $request->get_original_url() );
		$action->run();

		// The target stays on this site, and is not treated as a protocol relative URL
		$this->assertEquals( '/test.example/login', $this->captured_url );
		$this->resetCaptured();
	}

	public function testRegexMatchCannotRedirectOffsiteAfterSanitizing() {
		$this->capturedRedirect();

		$request = new Red_Url_Request( '/old/%22%2Ftest.example/login' );
		$item = new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '^/old/(.*)$', 'match_url' => 'regex', 'action_data' => '/$1', 'action_code' => 301, 'status' => 'enabled' ] );
		$action = $item->get_match( $request->get_decoded_url(), $request->get_original_url() );
		$action->run();

		$this->assertEquals( '/test.example/login', wp_sanitize_redirect( $this->captured_url ) );
		$this->resetCaptured();
	}

	public function testCapturedRegexMatchCannotRedirectOffsite() {
		$this->capturedRedirect();

		$request = new Red_Url_Request( '/old/%2F%2Ftest.example/login' );
		$item = new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '^/old/(.*)$', 'match_url' => 'regex', 'action_data' => '$1', 'action_code' => 301, 'status' => 'enabled' ] );
		$action = $item->get_match( $request->get_decoded_url(), $request->get_original_url() );
		$action->run();

		$this->assertEquals( '/test.example/login', $this->captured_url );
		$this->resetCaptured();
	}

	public function testCapturedRegexMatchFromStartOfPathCannotRedirectOffsite() {
		$this->capturedRedirect();

		$request = new Red_Url_Request( '/%2Ftest.example/old' );
		$item = new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '^(/.*)/old$', 'match_url' => 'regex', 'action_data' => '$1', 'action_code' => 301, 'status' => 'enabled' ] );
		$action = $item->get_match( $request->get_decoded_url(), $request->get_original_url() );
		$action->run();

		$this->assertEquals( '/test.example', wp_sanitize_redirect( $this->captured_url ) );
		$this->resetCaptured();
	}

	public function testRegexMatchWithEncodedNewlineCannotRedirectOffsite() {
		$this->capturedRedirect();

		$request = new Red_Url_Request( '/old/%250a%2Ftest.example/login' );
		$item = new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '^/old/(.*)$', 'match_url' => 'regex', 'action_data' => '/$1', 'action_code' => 301, 'status' => 'enabled' ] );
		$action = $item->get_match( $request->get_decoded_url(), $request->get_original_url() );
		$action->run();

		$this->assertEquals( '/test.example/login', wp_sanitize_redirect( $this->captured_url ) );
		$this->resetCaptured();
	}

	/**
	 * @param string $request_url Request URL, encoded.
	 * @param string $target Target URL.
	 * @param string $action_type Action type.
	 * @return Red_Action|false
	 */
	private function getRegexAction( $request_url, $target, $action_type = 'url' ) {
		$request = new Red_Url_Request( $request_url );
		$item = new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => $action_type, 'url' => '^/old/(.*)$', 'match_url' => 'regex', 'action_data' => $target, 'action_code' => 301, 'status' => 'enabled' ] );

		return $item->get_match( $request->get_decoded_url(), $request->get_original_url() );
	}

	public function testRegexMatchWithDoubleEncodedSlashStaysRelative() {
		$action = $this->getRegexAction( '/old/%252F%252Ftest.example/login', '/$1' );

		$this->assertEquals( '/%2F%2Ftest.example/login', wp_sanitize_redirect( $action->get_target() ) );
	}

	public function testCapturedShortcodeIsNotTransformed() {
		$requests = [
			'/old/%5Bupper%5D%5B%2Fupper%5D%2Ftest.example/login' => '/%5Bupper%5D%5B/upper%5D/test.example/login',
			'/old/%5Blower%5D%2F%5B%2Flower%5D%2Ftest.example/login' => '/%5Blower%5D/%5B/lower%5D/test.example/login',
			'/old/%5Buserlogin%5D%2Ftest.example/login' => '/%5Buserlogin%5D/test.example/login',
			'/old/%25userlogin%25%2Ftest.example/login' => '/%25userlogin%25/test.example/login',
		];

		foreach ( $requests as $request => $expected ) {
			$action = $this->getRegexAction( $request, '/$1' );

			$this->assertEquals( $expected, $action->get_target(), $request );
		}
	}

	public function testShortcodeAroundCaptureIsTransformed() {
		$action = $this->getRegexAction( '/old/My-Page', '/[lower]$1[/lower]' );

		$this->assertEquals( '/my-page', $action->get_target() );
	}

	public function testShortcodeAroundCaptureCannotRedirectOffsite() {
		$action = $this->getRegexAction( '/old/%2F%2FTEST.example/login', '[lower]$1[/lower]' );

		$this->assertEquals( '/test.example/login', $action->get_target() );
	}

	public function testCapturedCharactersRemovedByRedirectSanitizingDoNotCreateTransform() {
		$user = self::factory()->user->create_and_get();
		wp_set_current_user( $user->ID );

		$action = $this->getRegexAction( '/old/%25user%7Clogin%25', '/$1' );

		$this->assertStringNotContainsString( $user->user_login, $action->get_target() );
	}

	public function testCapturedCharactersRemovedFromRelativeTargetDoNotCreateTransform() {
		wp_set_current_user( self::factory()->user->create() );

		$requests = [
			[ '/old/%2F%2Ftest.example%2F%25user%7Clogin%25', '/$1', '/test.example/%25userlogin%25' ],
			[ '/old/https:%2F%2Ftest.example%2F%25user%7Clogin%25', '$1', '/https://test.example/%25userlogin%25' ],
		];

		foreach ( $requests as [ $request, $target, $expected ] ) {
			$this->assertEquals( $expected, $this->getRegexAction( $request, $target )->get_target(), $request );
		}
	}

	public function testCapturedSchemeWithRemovedCharactersCannotRedirectOffsite() {
		$action = $this->getRegexAction( '/old/h%5Cttps://test.example/login', '$1' );

		$this->assertEquals( '/https://test.example/login', wp_sanitize_redirect( $action->get_target() ) );
	}

	public function testCapturedPostIdIsTransformed() {
		$post_id = self::factory()->post->create();

		$this->assertEquals( get_permalink( $post_id ), $this->getRegexAction( '/old/' . $post_id, '$1' )->get_target() );
	}

	public function testRegexCaptureAfterHostStartsPath() {
		$requests = [
			'/old/page' => 'https://good.example/page',
			'/old@test.example/login' => 'https://good.example/@test.example/login',
			'/old.test.example' => 'https://good.example/.test.example',
			'/old:8080' => 'https://good.example/:8080',
			'/old?page=1' => 'https://good.example?page=1',
			'/old' => 'https://good.example',
		];

		$item = new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '^/old(.*)$', 'match_url' => 'regex', 'action_data' => 'https://good.example$1', 'action_code' => 301, 'status' => 'enabled' ] );

		foreach ( $requests as $request => $expected ) {
			$this->assertEquals( $expected, $item->get_match( $request )->get_target(), $request );
		}
	}

	public function testUnanchoredRegexCannotPutRequestBeforeHost() {
		$targets = [
			[ '/old/(.*)', 'https://good.example/$1', 'https://good.example/page' ],
			[ '/old(.*)', 'https://good.example$1', 'https://good.example/page' ],
			[ '/old/', 'https://good.example/', 'https://good.example/page' ],
			[ '/old/(.*)', '/new/$1', '/test.example/x/new/page' ],
		];

		foreach ( $targets as [ $source, $target, $expected ] ) {
			$item = new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => $source, 'match_url' => 'regex', 'action_data' => $target, 'action_code' => 301, 'status' => 'enabled' ] );

			$this->assertEquals( $expected, wp_sanitize_redirect( $item->get_match( '//test.example/x/old/page' )->get_target() ), $source . ' ' . $target );
		}
	}

	public function testRegexTextAfterMatchStartsPath() {
		$requests = [
			'/shop@test.example' => 'https://good.example/@test.example',
			'/shop.test.example' => 'https://good.example/.test.example',
			'/shop/page' => 'https://good.example/page',
			'/shop?page=1' => 'https://good.example?page=1',
			'/shop' => 'https://good.example',
		];

		$item = new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '^/shop', 'match_url' => 'regex', 'action_data' => 'https://good.example', 'action_code' => 301, 'status' => 'enabled' ] );

		foreach ( $requests as $request => $expected ) {
			$this->assertEquals( $expected, $item->get_match( $request )->get_target(), $request );
		}
	}

	public function testSavedRegexCaptureAfterHostKeepsSlash() {
		$item = $this->createRedirect( [ 'url' => '^/old(/.*)$', 'regex' => true, 'action_code' => 301, 'action_data' => [ 'url' => 'https://good.example$1' ] ] );

		$this->assertEquals( 'https://good.example/page', $item->get_match( '/old/page' )->get_target() );
	}

	public function testShortcodeAroundCaptureIsGivenCapturedCharacters() {
		$action = $this->getRegexAction( '/old/a%7Cb', '/[md5]$1[/md5]' );

		$this->assertEquals( '/' . md5( 'a|b' ), $action->get_target() );
	}

	public function testPassRegexMatchCannotFetchExternalUrl() {
		$requests = [
			'/old/https%3A%2F%2Ftest.example/login',
			'/old/%5Blower%5DHTTPS%3A%2F%2Ftest.example%2Flogin%5B%2Flower%5D',
		];

		foreach ( $requests as $request ) {
			$action = $this->getRegexAction( $request, '$1', 'pass' );

			$this->assertInstanceOf( 'Redirection\Action\Pass', $action );
			$this->assertFalse( $action->is_external( $action->get_target() ), $request );
		}
	}

	public function testLoginRegexMatchCannotRedirectOffsite() {
		$request = new Red_Url_Request( '/old/%2F%2Ftest.example/login' );
		$item = new Red_Item( (object) [ 'match_type' => 'login', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '^/old/(.*)$', 'match_url' => 'regex', 'action_data' => serialize( [ 'logged_in' => '$1', 'logged_out' => '$1' ] ), 'action_code' => 301, 'status' => 'enabled' ] );
		$action = $item->get_match( $request->get_decoded_url(), $request->get_original_url() );

		$this->assertEquals( '/test.example/login', $action->get_target() );
	}

	public function testTransformedRegexMatchCannotRedirectOffsite() {
		$this->capturedRedirect();

		$request = new Red_Url_Request( '/old/%5Bupper%5Dhttps%3A%2F%2Ftest.example%2Flogin%5B%2Fupper%5D' );
		$item = new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '^/old/(.*)$', 'match_url' => 'regex', 'action_data' => '$1', 'action_code' => 301, 'status' => 'enabled' ] );
		$action = $item->get_match( $request->get_decoded_url(), $request->get_original_url() );
		$action->run();

		$this->assertEquals( '%5Bupper%5Dhttps://test.example/login%5B/upper%5D', $this->captured_url );
		$this->resetCaptured();
	}

	public function testRegexCaptureAfterHostRedirects() {
		$this->capturedRedirect();

		$item = new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '^/old(/.*)$', 'match_url' => 'regex', 'action_data' => 'https://good.example$1', 'action_code' => 301, 'status' => 'enabled' ] );
		$action = $item->get_match( '/old/page' );
		$action->run();

		$this->assertEquals( 'https://good.example/page', $this->captured_url );
		$this->resetCaptured();
	}

	public function testConfiguredRegexHostCanBeTransformed() {
		$this->capturedRedirect();

		$item = new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '^/old/(.*)$', 'match_url' => 'regex', 'action_data' => 'https://[lower]GOOD.EXAMPLE[/lower]/$1', 'action_code' => 301, 'status' => 'enabled' ] );
		$action = $item->get_match( '/old/page' );
		$action->run();

		$this->assertEquals( 'https://good.example/page', $this->captured_url );
		$this->resetCaptured();
	}

	public function testConfiguredRegexUrlCanBeTransformed() {
		$this->capturedRedirect();

		$item = new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '^/old/(.*)$', 'match_url' => 'regex', 'action_data' => '[upper]https://good.example/$1[/upper]', 'action_code' => 301, 'status' => 'enabled' ] );
		$action = $item->get_match( '/old/page' );
		$action->run();

		$this->assertEquals( 'HTTPS://GOOD.EXAMPLE/PAGE', $this->captured_url );
		$this->resetCaptured();
	}

	public function testConfiguredRegexPostCanBeTransformed() {
		$post_id = self::factory()->post->create();
		$this->capturedRedirect();

		$item = new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '^/old/(.*)$', 'match_url' => 'regex', 'action_data' => (string) $post_id, 'action_code' => 301, 'status' => 'enabled' ] );
		$action = $item->get_match( '/old/page' );
		$action->run();

		$this->assertEquals( get_permalink( $post_id ), $this->captured_url );
		$this->resetCaptured();
	}

	public function testEarlierRegexTargetFilterCanSetExplicitHost() {
		add_filter( 'redirection_url_target', array( $this, 'replaceRedirectTarget' ), 5 );
		$this->capturedRedirect();

		$item = new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '^/old/(.*)$', 'match_url' => 'regex', 'action_data' => '/$1', 'action_code' => 301, 'status' => 'enabled' ] );
		$action = $item->get_match( '/old/page' );
		$action->run();

		$this->assertEquals( 'https://plugin.example/target', $this->captured_url );
		$this->resetCaptured();
		remove_filter( 'redirection_url_target', array( $this, 'replaceRedirectTarget' ), 5 );
	}

	public function testRegexTargetFilterCanSetExplicitHost() {
		add_filter( 'redirection_url_target', array( $this, 'replaceRedirectTarget' ), 20 );
		$this->capturedRedirect();

		$item = new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '^/old/(.*)$', 'match_url' => 'regex', 'action_data' => '/$1', 'action_code' => 301, 'status' => 'enabled' ] );
		$action = $item->get_match( '/old/page' );
		$action->run();

		$this->assertEquals( 'https://plugin.example/target', $this->captured_url );
		$this->resetCaptured();
		remove_filter( 'redirection_url_target', array( $this, 'replaceRedirectTarget' ), 20 );
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

		Red_Options::save( [ 'database' => '3.9' ] );

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

		Red_Options::save( [ 'database' => '3.9' ] );

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

		Red_Options::save( [ 'flag_case' => false, 'flag_regex' => false, 'flag_query' => 'ignore', 'flag_trailing' => true ] );
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
		Red_Options::save( [ 'flag_case' => false, 'flag_regex' => false, 'flag_query' => 'ignore', 'flag_trailing' => true ] );

		$item = new Red_Item( (object) [ 'match_data' => wp_json_encode( [ 'source' => [ 'flag_case' => true ] ] ) ] );
		$data = $item->get_match_data();

		$this->assertEquals( [ 'source' => [ 'flag_case' => true, 'flag_regex' => false, 'flag_query' => 'ignore', 'flag_trailing' => true ] ], $data );
	}

	public function testMatchDataSaved() {
		global $wpdb;

		// This returns without defaults
		Red_Options::save( [ 'flag_case' => false, 'flag_regex' => false, 'flag_query' => 'exact', 'flag_trailing' => true ] );

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
