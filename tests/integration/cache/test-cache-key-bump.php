<?php

class CacheKeyBumpTest extends WP_UnitTestCase {
	private $group;

	public function setUp(): void {
		if ( ! red_is_refactor_enabled() ) {
			$this->markTestSkipped( 'Cache invalidation is implemented on the refactor path only.' );
		}

		parent::setUp();

		Redirection\Plugin\Admin::reset();
		Redirection\Plugin\Admin::init();

		$this->group = Red_Group::create( 'cache key test', 1 );
	}

	public function tearDown(): void {
		\Redirection\Plugin\Admin::reset();

		parent::tearDown();
	}

	private function make_redirect() {
		return Red_Item::create(
			[
				'url' => '/cache-key-test',
				'action_type' => 'url',
				'match_type' => 'url',
				'group_id' => $this->group->get_id(),
			]
		);
	}

	private function enable_cache_with_key( $key ) {
		red_set_options( [ 'cache_key' => $key ] );
	}

	private function get_cache_key() {
		$options = red_get_options();

		return $options['cache_key'];
	}

	public function testUpdateBumpsKey() {
		$this->enable_cache_with_key( 1 );
		$redirect = $this->make_redirect();

		$redirect->update(
			[
				'url' => '/cache-key-test-changed',
				'action_type' => 'url',
				'match_type' => 'url',
				'group_id' => $this->group->get_id(),
			]
		);

		$this->assertGreaterThan( 1, $this->get_cache_key() );
	}

	public function testDeleteBumpsKey() {
		$this->enable_cache_with_key( 1 );
		$redirect = $this->make_redirect();

		$redirect->delete();

		$this->assertGreaterThan( 1, $this->get_cache_key() );
	}

	public function testDisableBumpsKey() {
		$this->enable_cache_with_key( 1 );
		$redirect = $this->make_redirect();

		$redirect->disable();

		$this->assertGreaterThan( 1, $this->get_cache_key() );
	}

	public function testEnableBumpsKey() {
		$this->enable_cache_with_key( 1 );
		$redirect = $this->make_redirect();
		$redirect->disable();
		red_set_options( [ 'cache_key' => 1 ] );

		$redirect->enable();

		$this->assertGreaterThan( 1, $this->get_cache_key() );
	}

	public function testDeleteDoesNotEnableADisabledCache() {
		$this->enable_cache_with_key( 0 );
		$redirect = $this->make_redirect();

		$redirect->delete();

		$this->assertSame( 0, $this->get_cache_key() );
	}
}
