<?php

require_once PLUGIN_PATH . '/includes/cache/class-provider.php';
require_once PLUGIN_PATH . '/includes/cache/class-registry.php';
require_once PLUGIN_PATH . '/includes/cache/class-result.php';
require_once PLUGIN_PATH . '/includes/cache/class-generic.php';
require_once PLUGIN_PATH . '/includes/cache/class-invalidator.php';
require_once __DIR__ . '/class-fake-provider.php';

use Redirection\Cache\Invalidator;
use Redirection\Cache\Registry;
use Redirection\Cache\Result;
use Brain\Monkey\Functions;

class CacheInvalidatorTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		Invalidator::reset();

		Functions\when( 'home_url' )->alias(
			function ( $path = '' ) {
				return 'https://example.com' . $path;
			}
		);
		Functions\when( 'update_option' )->justReturn( true );
		Functions\when( 'do_action' )->justReturn( null );
	}

	protected function tearDown(): void {
		Invalidator::reset();
		Registry::set_providers( null );

		parent::tearDown();
	}

	private function get_invalidator( array $providers = [], bool $enabled = true ) {
		Registry::set_providers( $providers );

		$invalidator = Invalidator::init();
		$invalidator->set_enabled( $enabled );

		return $invalidator;
	}

	public function testQueuedUrlPurgesBothTrailingSlashVariants() {
		$provider = new Redirection_Cache_Fake_Provider( 'fake' );
		$invalidator = $this->get_invalidator( [ $provider ] );

		$invalidator->queue_url( '/old-page' );
		$invalidator->flush();

		$this->assertSame(
			[ 'https://example.com/old-page', 'https://example.com/old-page/' ],
			$provider->purged_urls
		);
		$this->assertFalse( $provider->purged_all );
	}

	public function testAlreadySlashedUrlPurgesBothVariants() {
		$provider = new Redirection_Cache_Fake_Provider( 'fake' );
		$invalidator = $this->get_invalidator( [ $provider ] );

		$invalidator->queue_url( '/old-page/' );
		$invalidator->flush();

		$this->assertSame(
			[ 'https://example.com/old-page/', 'https://example.com/old-page' ],
			$provider->purged_urls
		);
	}

	public function testAbsoluteSourceUrlIsUsedAsIs() {
		$provider = new Redirection_Cache_Fake_Provider( 'fake' );
		$invalidator = $this->get_invalidator( [ $provider ] );

		$invalidator->queue_url( 'https://example.com/old-page' );
		$invalidator->flush();

		$this->assertSame(
			[ 'https://example.com/old-page', 'https://example.com/old-page/' ],
			$provider->purged_urls
		);
	}

	public function testSourceWithoutLeadingSlashIsMadeAbsolute() {
		$provider = new Redirection_Cache_Fake_Provider( 'fake' );
		$invalidator = $this->get_invalidator( [ $provider ] );

		$invalidator->queue_url( 'old-page' );
		$invalidator->flush();

		$this->assertContains( 'https://example.com/old-page', $provider->purged_urls );
	}

	public function testEmptySourceForcesFullPurge() {
		$provider = new Redirection_Cache_Fake_Provider( 'fake' );
		$invalidator = $this->get_invalidator( [ $provider ] );

		$invalidator->queue_url( '' );
		$invalidator->flush();

		$this->assertTrue( $provider->purged_all );
		$this->assertNull( $provider->purged_urls );
	}

	public function testManyUrlsEscalateToFullPurge() {
		$provider = new Redirection_Cache_Fake_Provider( 'fake' );
		$invalidator = $this->get_invalidator( [ $provider ] );

		// 11 sources create 22 variants.
		for ( $i = 0; $i < 11; $i++ ) {
			$invalidator->queue_url( '/page-' . $i );
		}

		$invalidator->flush();

		$this->assertTrue( $provider->purged_all );
		$this->assertNull( $provider->purged_urls );
	}

	public function testExactlyMaxUrlsStaysTargeted() {
		$provider = new Redirection_Cache_Fake_Provider( 'fake' );
		$invalidator = $this->get_invalidator( [ $provider ] );

		// 10 sources create 20 variants.
		for ( $i = 0; $i < 10; $i++ ) {
			$invalidator->queue_url( '/page-' . $i );
		}

		$invalidator->flush();

		$this->assertCount( 20, $provider->purged_urls );
		$this->assertFalse( $provider->purged_all );
	}

	public function testProviderWithoutUrlSupportIsEscalatedToFullPurge() {
		$provider = new Redirection_Cache_Fake_Provider( 'fake', true, false );
		$invalidator = $this->get_invalidator( [ $provider ] );

		$invalidator->queue_url( '/old-page' );
		$invalidator->flush();

		$this->assertTrue( $provider->purged_all );
		$this->assertNull( $provider->purged_urls );
	}

	public function testQueueFullPurgesEverything() {
		$provider = new Redirection_Cache_Fake_Provider( 'fake' );
		$invalidator = $this->get_invalidator( [ $provider ] );

		$invalidator->queue_full();
		$invalidator->flush();

		$this->assertTrue( $provider->purged_all );
	}

	public function testFlushIsIdempotent() {
		$provider = new Redirection_Cache_Fake_Provider( 'fake' );
		$invalidator = $this->get_invalidator( [ $provider ] );

		$invalidator->queue_url( '/old-page' );
		$invalidator->flush();

		$provider->purged_urls = null;
		$second = $invalidator->flush();

		$this->assertSame( [], $second );
		$this->assertNull( $provider->purged_urls );
	}

	public function testDuplicateUrlsArePurgedOnce() {
		$provider = new Redirection_Cache_Fake_Provider( 'fake' );
		$invalidator = $this->get_invalidator( [ $provider ] );

		$invalidator->queue_url( '/old-page' );
		$invalidator->queue_url( '/old-page' );
		$invalidator->flush();

		$this->assertSame(
			[ 'https://example.com/old-page', 'https://example.com/old-page/' ],
			$provider->purged_urls
		);
	}

	public function testThrowingProviderIsRecordedAndOthersStillRun() {
		$broken = new Redirection_Cache_Fake_Provider( 'broken', true, true, true );
		$working = new Redirection_Cache_Fake_Provider( 'working' );
		$invalidator = $this->get_invalidator( [ $broken, $working ] );

		$invalidator->queue_url( '/old-page' );
		$results = $invalidator->flush();

		$this->assertCount( 2, $results );
		$this->assertSame( Result::FAILED, $results[0]->get_status() );
		$this->assertSame( Result::PURGED_URLS, $results[1]->get_status() );
		$this->assertNotNull( $working->purged_urls );
	}

	public function testProviderReturningFalseIsRecordedAsFailed() {
		$provider = new Redirection_Cache_Fake_Provider( 'fake', true, true, false, false );
		$invalidator = $this->get_invalidator( [ $provider ] );

		$invalidator->queue_url( '/old-page' );
		$results = $invalidator->flush();

		$this->assertSame( Result::FAILED, $results[0]->get_status() );
	}

	public function testInactiveProvidersAreNotPurged() {
		$provider = new Redirection_Cache_Fake_Provider( 'fake', false );
		$invalidator = $this->get_invalidator( [ $provider ] );

		$invalidator->queue_url( '/old-page' );

		$this->assertSame( [], $invalidator->flush() );
		$this->assertNull( $provider->purged_urls );
	}

	public function testDisabledSettingSuppressesEverything() {
		$provider = new Redirection_Cache_Fake_Provider( 'fake' );
		$invalidator = $this->get_invalidator( [ $provider ], false );

		$invalidator->queue_url( '/old-page' );

		$this->assertSame( [], $invalidator->flush() );
		$this->assertNull( $provider->purged_urls );
		$this->assertFalse( $provider->purged_all );
	}

	public function testDisabledSettingSuppressesGenericSignal() {
		$called = false;

		Functions\when( 'update_option' )->alias(
			function () use ( &$called ) {
				$called = true;

				return true;
			}
		);

		$provider = new Redirection_Cache_Fake_Provider( 'fake' );
		$invalidator = $this->get_invalidator( [ $provider ], false );

		$invalidator->queue_url( '/old-page' );
		$invalidator->flush();

		$this->assertFalse( $called );
	}

	public function testFlushWithNothingQueuedDoesNothing() {
		$provider = new Redirection_Cache_Fake_Provider( 'fake' );
		$invalidator = $this->get_invalidator( [ $provider ] );

		$this->assertSame( [], $invalidator->flush() );
		$this->assertFalse( $provider->purged_all );
	}

	public function testThrowingFlushHookDoesNotEscape() {
		Functions\when( 'do_action' )->alias(
			function () {
				throw new \RuntimeException( 'hook exploded' );
			}
		);

		$invalidator = $this->get_invalidator();
		$invalidator->queue_url( '/old-page' );

		$this->assertSame( [], $invalidator->flush() );
	}

	public function testFlushStillWritesGenericOptionWhenProviderFilterReturnsJunk() {
		Functions\when( 'apply_filters' )->alias(
			function ( $name, $value ) {
				return $name === 'redirection_cache_providers' ? 'not-an-array' : $value;
			}
		);

		$written = false;

		Functions\when( 'update_option' )->alias(
			function () use ( &$written ) {
				$written = true;

				return true;
			}
		);

		Registry::set_providers( null );

		$invalidator = Invalidator::init();
		$invalidator->set_enabled( true );
		$invalidator->queue_url( '/old-page' );
		$invalidator->flush();

		$this->assertTrue( $written );
	}

	public function testUnlocatableChangeAmongOtherUrlsForcesFullPurge() {
		$provider = new Redirection_Cache_Fake_Provider( 'fake' );
		$invalidator = $this->get_invalidator( [ $provider ] );

		$invalidator->queue_url( '' );
		$invalidator->queue_url( '/old-page' );
		$invalidator->flush();

		$this->assertTrue( $provider->purged_all );
		$this->assertNull( $provider->purged_urls );
	}

	public function testFlushWritesGenericOptionWithNoProvidersRegistered() {
		$written = null;

		Functions\when( 'update_option' )->alias(
			function ( $name, $value ) use ( &$written ) {
				$written = $name;

				return true;
			}
		);

		$invalidator = $this->get_invalidator( [] );

		$invalidator->queue_url( '/old-page' );
		$invalidator->flush();

		$this->assertSame( 'redirection_flush', $written );
	}

	public function testQueryStringSourceHasNoTrailingSlashVariant() {
		$provider = new Redirection_Cache_Fake_Provider( 'fake' );
		$invalidator = $this->get_invalidator( [ $provider ] );

		$invalidator->queue_url( '/page?a=b' );
		$invalidator->flush();

		$this->assertSame( [ 'https://example.com/page?a=b' ], $provider->purged_urls );
	}

	public function testFragmentSourceHasNoTrailingSlashVariant() {
		$provider = new Redirection_Cache_Fake_Provider( 'fake' );
		$invalidator = $this->get_invalidator( [ $provider ] );

		$invalidator->queue_url( '/page#section' );
		$invalidator->flush();

		$this->assertSame( [ 'https://example.com/page#section' ], $provider->purged_urls );
	}
}
