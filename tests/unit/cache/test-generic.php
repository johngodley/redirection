<?php

require_once PLUGIN_PATH . '/includes/cache/class-provider.php';
require_once PLUGIN_PATH . '/includes/cache/class-result.php';
require_once PLUGIN_PATH . '/includes/cache/class-generic.php';

use Redirection\Cache\Generic;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;

class CacheGenericTest extends TestCase {
	public function testEveryFlushWritesADifferentValue() {
		$written = [];

		Functions\when( 'update_option' )->alias(
			function ( $name, $value ) use ( &$written ) {
				$written[] = [ $name, $value ];

				return true;
			}
		);
		Functions\when( 'do_action' )->justReturn( null );

		$generic = new Generic();
		$generic->flush( [ '/one' ], false );
		$generic->flush( [ '/one' ], false );

		$this->assertSame( 'redirection_flush', $written[0][0] );
		$this->assertSame( 'redirection_flush', $written[1][0] );
		$this->assertNotSame( $written[0][1], $written[1][1] );
	}

	public function testOptionIsNotAutoloaded() {
		$autoload = null;

		Functions\when( 'update_option' )->alias(
			function ( $name, $value, $auto ) use ( &$autoload ) {
				$autoload = $auto;

				return true;
			}
		);
		Functions\when( 'do_action' )->justReturn( null );

		( new Generic() )->flush( [], true );

		$this->assertFalse( $autoload );
	}

	public function testPublicActionFiresWithUrlsAndFullFlag() {
		Functions\when( 'update_option' )->justReturn( true );

		Actions\expectDone( 'redirection_flush_caches' )
			->once()
			->with( [ 'https://example.com/one' ], false );

		( new Generic() )->flush( [ 'https://example.com/one' ], false );

		$this->addToAssertionCount( 1 );
	}
}
