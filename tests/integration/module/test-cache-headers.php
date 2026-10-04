<?php

use Redirection\Module\Plugin;

class CacheHeadersTest extends WP_UnitTestCase {
	private function get_module() {
		return new Plugin();
	}

	public function testDefaultOneHourSetsMaxAgeAndForbidsSharedCaches() {
		$headers = $this->get_module()->get_cache_headers( 301, 1 );

		$this->assertSame( 'max-age=3600, s-maxage=0', $headers['Cache-Control'] );
		$this->assertArrayHasKey( 'Expires', $headers );
	}

	public function testWeekLongCacheStillForbidsSharedCaches() {
		$headers = $this->get_module()->get_cache_headers( 301, 24 * 7 );

		$this->assertSame( 'max-age=604800, s-maxage=0', $headers['Cache-Control'] );
	}

	public function testNoCacheSettingReturnsNoHeadersSoWordPressHandlesIt() {
		$this->assertSame( [], $this->get_module()->get_cache_headers( 301, -1 ) );
	}

	public function testDisabledSettingReturnsNoHeaders() {
		$this->assertSame( [], $this->get_module()->get_cache_headers( 301, 0 ) );
	}

	public function testNon301StatusesAreLeftAlone() {
		$this->assertSame( [], $this->get_module()->get_cache_headers( 302, 1 ) );
		$this->assertSame( [], $this->get_module()->get_cache_headers( 307, 1 ) );
		$this->assertSame( [], $this->get_module()->get_cache_headers( 410, 1 ) );
	}
}
