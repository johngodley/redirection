<?php

class RedirectCacheTest extends WP_UnitTestCase {
	private function get_cache( $onoff = true ) {
		red_set_options( [ 'cache_key' => $onoff ? 1 : 0 ] );
		$cache = Redirect_Cache::init();
		$cache->reset();
		return $cache;
	}

	public function testCanCache() {
		$cache = $this->get_cache();

		$this->assertTrue( $cache->can_cache() );
	}

	public function testCannotCache() {
		$cache = $this->get_cache( false );

		$this->assertFalse( $cache->can_cache() );
	}

	public function testNoCache() {
		$cache = $this->get_cache();

		$this->assertTrue( $cache->can_cache() );
	}

	public function testEmptyCache() {
		$cache = $this->get_cache();

		$this->assertFalse( $cache->get( '/cat' ) );
	}

	public function testSetCache() {
		red_set_options( [ 'cache_key' => 1 ] );

		$item = new Red_Item( [ 'match_type' => 'url' ] );

		$cache = $this->get_cache();

		$this->assertTrue( $cache->set( '/cat', $item, [ $item ] ) );
		$this->assertEquals( [ $item->to_sql() ], $cache->get( '/cat' ) );
	}

	public function testCannotDoubleSetCache() {
		red_set_options( [ 'cache_key' => 1 ] );

		$item = new Red_Item( [ 'match_type' => 'url' ] );

		$cache = $this->get_cache();

		$this->assertTrue( $cache->set( '/cat', $item, [ $item ] ) );
		$cache->get( '/cat' );
		$this->assertFalse( $cache->set( '/cat', $item, [ $item ] ) );
	}

	public function testDynamicMatch() {
		$item1 = new Red_Item( [ 'match_type' => 'agent' ] );
		$item2 = new Red_Item( [ 'match_type' => 'agent' ] );

		$cache = $this->get_cache();
		$cache->set( '/cat', $item2, [ $item1, $item2 ] );

		$this->assertEquals( [ $item1->to_sql(), $item2->to_sql() ], $cache->get( '/cat' ) );
	}

	public function testStaticMatchWithDynamic() {
		$item1 = new Red_Item( [ 'match_type' => 'agent' ] );
		$item2 = new Red_Item( [ 'match_type' => 'url' ] );

		$cache = $this->get_cache();
		$cache->set( '/cat', $item2, [ $item1, $item2 ] );

		$this->assertEquals( [ $item1->to_sql(), $item2->to_sql() ], $cache->get( '/cat' ) );
	}

	// Check that when a redirect is updated and the cache key is changed then the previous cache entry is ignored
	public function testCacheReset() {
		$item = new Red_Item( [ 'match_type' => 'url' ] );

		$cache = $this->get_cache();
		$cache->set( '/cat', $item, [ $item ] );
		$this->assertEquals( [ $item->to_sql() ], $cache->get( '/cat' ) );

		red_set_options( [ 'cache_key' => 2 ] );
		$cache->reset();

		$this->assertFalse( $cache->get( '/cat' ) );
	}
}
