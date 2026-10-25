<?php

use Redirection\Cache\Listener;
use Redirection\Cache\Invalidator;
use Redirection\Cache\Registry;

require_once __DIR__ . '/../../unit/cache/class-fake-provider.php';

class CacheListenerTest extends WP_UnitTestCase {
	private $provider;
	private $group;

	public function setUp(): void {
		if ( ! red_is_refactor_enabled() ) {
			$this->markTestSkipped( 'Cache invalidation is implemented on the refactor path only.' );
		}

		parent::setUp();

		Invalidator::reset();

		$this->group = Red_Group::create( 'listener test', 1 );

		$this->provider = new Redirection_Cache_Fake_Provider( 'fake' );
		Registry::set_providers( [ $this->provider ] );

		red_set_options( [ 'cache_purge' => true ] );

		Listener::init();
	}

	public function tearDown(): void {
		remove_all_actions( 'redirection_redirect_updated' );
		remove_all_actions( 'redirection_redirect_deleted' );
		remove_all_actions( 'redirection_redirect_enabled' );
		remove_all_actions( 'redirection_redirect_disabled' );
		remove_all_actions( 'redirection_group_updated' );
		remove_all_actions( 'redirection_group_deleted' );
		remove_all_actions( 'shutdown' );

		Registry::set_providers( null );
		Invalidator::reset();

		parent::tearDown();
	}

	private function make_redirect( $url = '/listener-test' ) {
		return Red_Item::create(
			[
				'url' => $url,
				'action_type' => 'url',
				'match_type' => 'url',
				'group_id' => $this->group->get_id(),
			]
		);
	}

	public function testCreatingARedirectQueuesItsUrl() {
		$this->make_redirect( '/listener-test' );

		Invalidator::init()->flush();

		$this->assertContains( home_url( '/listener-test' ), $this->provider->purged_urls );
	}

	public function testUpdatingARedirectQueuesItsOldAndNewUrls() {
		$redirect = $this->make_redirect( '/listener-old' );
		Invalidator::init()->flush();
		$this->provider->purged_urls = null;

		$redirect->update(
			[
				'url' => '/listener-new',
				'action_type' => 'url',
				'match_type' => 'url',
				'group_id' => $this->group->get_id(),
			]
		);
		Invalidator::init()->flush();

		$this->assertContains( home_url( '/listener-old' ), $this->provider->purged_urls );
		$this->assertContains( home_url( '/listener-new' ), $this->provider->purged_urls );
	}

	public function testUpdatingARegexRedirectToStaticQueuesAFullPurge() {
		$redirect = $this->make_redirect( '/listener-regex-.*' );
		$redirect->update(
			[
				'url' => '/listener-regex-.*',
				'action_type' => 'url',
				'match_type' => 'url',
				'group_id' => $this->group->get_id(),
				'regex' => true,
			]
		);
		Invalidator::init()->flush();
		$this->provider->purged_all = false;
		$this->provider->purged_urls = null;

		$redirect->update(
			[
				'url' => '/listener-static',
				'action_type' => 'url',
				'match_type' => 'url',
				'group_id' => $this->group->get_id(),
			]
		);
		Invalidator::init()->flush();

		$this->assertTrue( $this->provider->purged_all );
		$this->assertNull( $this->provider->purged_urls );
	}

	public function testShutdownDispatchesQueuedChanges() {
		// Preserve PHPUnit's output buffer.
		remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );

		$this->make_redirect( '/listener-shutdown' );

		do_action( 'shutdown' );

		$this->assertContains( home_url( '/listener-shutdown' ), $this->provider->purged_urls );
	}

	public function testDeletingARedirectQueuesItsUrl() {
		$redirect = $this->make_redirect( '/listener-delete' );
		Invalidator::init()->flush();
		$this->provider->purged_urls = null;

		$redirect->delete();
		Invalidator::init()->flush();

		$this->assertContains( home_url( '/listener-delete' ), $this->provider->purged_urls );
	}

	public function testDisablingARedirectQueuesItsUrl() {
		$redirect = $this->make_redirect( '/listener-disable' );
		Invalidator::init()->flush();
		$this->provider->purged_urls = null;

		$redirect->disable();
		Invalidator::init()->flush();

		$this->assertContains( home_url( '/listener-disable' ), $this->provider->purged_urls );
	}

	public function testRegexRedirectQueuesAFullPurge() {
		Red_Item::create(
			[
				'url' => '/listener-regex-.*',
				'action_type' => 'url',
				'match_type' => 'url',
				'group_id' => $this->group->get_id(),
				'regex' => true,
			]
		);

		Invalidator::init()->flush();

		$this->assertTrue( $this->provider->purged_all );
		$this->assertNull( $this->provider->purged_urls );
	}

	public function testDynamicRedirectQueuesAFullPurge() {
		Red_Item::create(
			[
				'url' => '/listener-dynamic',
				'action_type' => 'url',
				'match_type' => 'agent',
				'group_id' => $this->group->get_id(),
				'action_data' => [ 'agent' => 'FeedBurner', 'url_from' => '/a', 'url_notfrom' => '/b' ],
			]
		);

		Invalidator::init()->flush();

		$this->assertTrue( $this->provider->purged_all );
		$this->assertNull( $this->provider->purged_urls );
	}

	public function testGroupChangeQueuesAFullPurge() {
		do_action( 'redirection_group_updated', 1 );

		Invalidator::init()->flush();

		$this->assertTrue( $this->provider->purged_all );
	}

	public function testGroupDeleteQueuesAFullPurge() {
		do_action( 'redirection_group_deleted', 1 );

		Invalidator::init()->flush();

		$this->assertTrue( $this->provider->purged_all );
	}

	public function testChangingAGlobalMatchFlagQueuesAFullPurge() {
		red_set_options( [ 'flag_trailing' => true ] );

		Invalidator::init()->flush();

		$this->assertTrue( $this->provider->purged_all );
	}

	public function testChangingAnUnrelatedSettingQueuesNothing() {
		red_set_options( [ 'expire_404' => 30 ] );

		Invalidator::init()->flush();

		$this->assertFalse( $this->provider->purged_all );
		$this->assertNull( $this->provider->purged_urls );
	}

	public function testRedirectMutationStillBumpsTheCacheKey() {
		red_set_options( [ 'cache_key' => 1 ] );

		$this->make_redirect( '/listener-cache-key' );

		$options = red_get_options();
		$this->assertGreaterThan( 1, $options['cache_key'] );
	}

	public function testChangingAGlobalMatchFlagBumpsTheCacheKey() {
		red_set_options( [ 'cache_key' => 1 ] );

		red_set_options( [ 'flag_trailing' => true ] );

		$options = red_get_options();
		$this->assertGreaterThan( 1, $options['cache_key'] );
	}
}
