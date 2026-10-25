<?php

require_once PLUGIN_PATH . '/includes/cache/class-provider.php';
require_once PLUGIN_PATH . '/includes/cache/class-registry.php';
require_once __DIR__ . '/class-fake-provider.php';

use Redirection\Cache\Registry;
use Brain\Monkey\Functions;

class CacheRegistryTest extends TestCase {
	protected function tearDown(): void {
		Registry::set_providers( null );

		parent::tearDown();
	}

	public function testGetActiveExcludesInactiveProviders() {
		Registry::set_providers(
			[
				new Redirection_Cache_Fake_Provider( 'on', true ),
				new Redirection_Cache_Fake_Provider( 'off', false ),
			]
		);

		$active = Registry::get_active();

		$this->assertCount( 1, $active );
		$this->assertSame( 'on', $active[0]->get_id() );
	}

	public function testGetActiveReturnsAListWithContiguousKeys() {
		Registry::set_providers(
			[
				new Redirection_Cache_Fake_Provider( 'off', false ),
				new Redirection_Cache_Fake_Provider( 'on', true ),
			]
		);

		$this->assertSame( [ 0 ], array_keys( Registry::get_active() ) );
	}

	public function testThrowingProviderDoesNotStopOtherProvidersBeingDetected() {
		$broken = new class( 'broken' ) extends Redirection_Cache_Fake_Provider {
			public function is_active(): bool {
				throw new RuntimeException( 'boom' );
			}
		};
		$working = new Redirection_Cache_Fake_Provider( 'working', true );

		Registry::set_providers( [ $broken, $working ] );

		$this->assertSame( [ $working ], Registry::get_active() );
	}

	public function testProvidersComeFromTheFilter() {
		$provider = new Redirection_Cache_Fake_Provider( 'filtered', true );

		Functions\when( 'apply_filters' )->alias(
			function ( $name, $value ) use ( $provider ) {
				return $name === 'redirection_cache_providers' ? [ $provider ] : $value;
			}
		);

		$this->assertSame( [ $provider ], Registry::get_all() );
	}

	public function testProvidersAreResolvedOnlyOnce() {
		$calls = 0;

		Functions\when( 'apply_filters' )->alias(
			function ( $name, $value ) use ( &$calls ) {
				if ( $name === 'redirection_cache_providers' ) {
					$calls++;
				}

				return $value;
			}
		);

		Registry::get_all();
		Registry::get_all();

		$this->assertSame( 1, $calls );
	}

	public function testNonArrayFilterResultYieldsEmptyArray() {
		Functions\when( 'apply_filters' )->alias(
			function ( $name, $value ) {
				return $name === 'redirection_cache_providers' ? 'not-an-array' : $value;
			}
		);

		$this->assertSame( [], Registry::get_all() );
	}

	public function testMixedArrayFilterResultKeepsOnlyProviders() {
		$provider = new Redirection_Cache_Fake_Provider( 'valid', true );

		Functions\when( 'apply_filters' )->alias(
			function ( $name, $value ) use ( $provider ) {
				return $name === 'redirection_cache_providers' ? [ $provider, new stdClass(), 'a string', 123 ] : $value;
			}
		);

		$this->assertSame( [ $provider ], Registry::get_all() );
	}
}
