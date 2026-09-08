<?php

class CachePurgeSettingTest extends WP_UnitTestCase {
	public function setUp(): void {
		if ( ! red_is_refactor_enabled() ) {
			$this->markTestSkipped( 'Cache invalidation is implemented on the refactor path only.' );
		}

		parent::setUp();

		delete_option( 'redirection_options' );
	}

	public function testDefaultsToOn() {
		$options = red_get_options();

		$this->assertTrue( $options['cache_purge'] );
	}

	public function testCanBeTurnedOff() {
		red_set_options( [ 'cache_purge' => false ] );

		$options = red_get_options();

		$this->assertFalse( $options['cache_purge'] );
	}

	public function testCanBeTurnedBackOn() {
		red_set_options( [ 'cache_purge' => false ] );
		red_set_options( [ 'cache_purge' => true ] );

		$options = red_get_options();

		$this->assertTrue( $options['cache_purge'] );
	}

	public function testNonBooleanIsCoercedToBoolean() {
		red_set_options( [ 'cache_purge' => '0' ] );

		$options = red_get_options();

		$this->assertFalse( $options['cache_purge'] );
	}
}
