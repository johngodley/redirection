<?php

use Redirection\Cache\Generic;
use Redirection\Cache\Invalidator;
use Redirection\Cache\Listener;

class CacheFlushSignalTest extends WP_UnitTestCase {
	private $group;

	public function setUp(): void {
		if ( ! red_is_refactor_enabled() ) {
			$this->markTestSkipped( 'Cache invalidation is implemented on the refactor path only.' );
		}

		parent::setUp();

		Invalidator::reset();

		$this->group = Red_Group::create( 'flush signal test', 1 );

		red_set_options( [ 'cache_purge' => true ] );

		Listener::init();

		// Preserve PHPUnit's output buffer.
		remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
	}

	public function tearDown(): void {
		remove_all_actions( 'redirection_redirect_updated' );
		remove_all_actions( 'redirection_redirect_deleted' );
		remove_all_actions( 'redirection_redirect_enabled' );
		remove_all_actions( 'redirection_redirect_disabled' );
		remove_all_actions( 'redirection_group_updated' );
		remove_all_actions( 'redirection_group_deleted' );
		remove_all_actions( 'shutdown' );

		Invalidator::reset();

		parent::tearDown();
	}

	private function make_redirect( $url ) {
		return Red_Item::create(
			[
				'url' => $url,
				'action_type' => 'url',
				'match_type' => 'url',
				'group_id' => $this->group->get_id(),
			]
		);
	}

	public function testCreatingARedirectChangesTheFlushOption() {
		$before = get_option( Generic::OPTION );

		$this->make_redirect( '/flush-signal-create' );

		do_action( 'shutdown' );

		$after = get_option( Generic::OPTION );

		$this->assertNotSame( $before, $after );
	}

	public function testUpdatingARedirectChangesTheFlushOption() {
		$redirect = $this->make_redirect( '/flush-signal-update' );

		do_action( 'shutdown' );

		$before = get_option( Generic::OPTION );

		$redirect->update(
			[
				'url' => '/flush-signal-update-changed',
				'action_type' => 'url',
				'match_type' => 'url',
				'group_id' => $this->group->get_id(),
			]
		);

		do_action( 'shutdown' );

		$after = get_option( Generic::OPTION );

		$this->assertNotSame( $before, $after );
	}
}
