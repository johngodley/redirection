<?php

use Redirection\Site;

class Canonical_Test extends WP_UnitTestCase {
	public function testNoCanonical() {
		$canonical = new Site\Canonical( false, '', [], 'http://example.org' );
		$this->assertFalse( $canonical->get_redirect( 'http://example.org', '/request?arg=1' ) );
	}

	public function testHttps() {
		$canonical = new Site\Canonical( true, '', [], 'https://example.org' );
		$this->assertEquals( 'https://example.org/request?arg=1', $canonical->get_redirect( 'http://example.org', '/request?arg=1' ) );
		$this->assertEquals( 'https://example.org/request?arg=1', $canonical->get_redirect( 'https://example.org', '/request?arg=1' ) );
		$this->assertEquals( 'https://example.org/request?arg=1&arg2[]=2', $canonical->get_redirect( 'http://example.org', '/request?arg=1&arg2[]=2' ) );
		$this->assertEquals( 'https://example.org/request?arg=1&arg2[]=中国', $canonical->get_redirect( 'http://example.org', '/request?arg=1&arg2[]=中国' ) );
	}

	public function testPreferredWWW() {
		$canonical = new Site\Canonical( true, 'www', [], 'https://www.example.org' );
		$this->assertEquals( 'https://www.example.org/request?arg=1', $canonical->get_redirect( 'http://example.org', '/request?arg=1' ) );
		$this->assertEquals( 'https://www.example.org/request?arg=1', $canonical->get_redirect( 'http://www.example.org', '/request?arg=1' ) );
		$this->assertEquals( 'https://www.example.org/request?arg=1', $canonical->get_redirect( 'http://example.org', '/request?arg=1' ) );
		$this->assertEquals( 'https://www.example.org/request?arg=1&arg2[]=2', $canonical->get_redirect( 'http://example.org', '/request?arg=1&arg2[]=2' ) );
		$this->assertEquals( 'https://www.example.org/request?arg=1&arg2[]=中国', $canonical->get_redirect( 'http://example.org', '/request?arg=1&arg2[]=中国' ) );
	}

	public function testPreferredRemoveWWW() {
		$canonical = new Site\Canonical( true, 'nowww', [], 'https://example.org' );
		$this->assertEquals( 'https://example.org/request?arg=1', $canonical->get_redirect( 'http://www.example.org', '/request?arg=1' ) );
		$this->assertEquals( 'https://example.org/request?arg=1', $canonical->get_redirect( 'http://example.org', '/request?arg=1' ) );
		$this->assertEquals( 'https://example.org/request?arg=1', $canonical->get_redirect( 'http://www.example.org', '/request?arg=1' ) );
		$this->assertEquals( 'https://example.org/request?arg=1&arg2[]=2', $canonical->get_redirect( 'http://www.example.org', '/request?arg=1&arg2[]=2' ) );
		$this->assertEquals( 'https://example.org/request?arg=1&arg2[]=中国', $canonical->get_redirect( 'http://www.example.org', '/request?arg=1&arg2[]=中国' ) );
	}

	public function testAlias() {
		$canonical = new Site\Canonical( true, '', [ 'cat.com', 'dog.com' ], 'https://example.org' );
		$this->assertEquals( 'https://example.org/request?arg=1', $canonical->get_redirect( 'http://cat.com', '/request?arg=1' ) );
		$this->assertEquals( 'https://example.org/request?arg=1', $canonical->get_redirect( 'http://dog.com', '/request?arg=1' ) );
		$this->assertEquals( 'https://example.org/request?arg=1', $canonical->get_redirect( 'http://example.org', '/request?arg=1' ) );
		$this->assertEquals( 'https://example.org/request?arg=1&arg2[]=2', $canonical->get_redirect( 'http://example.org', '/request?arg=1&arg2[]=2' ) );
		$this->assertEquals( 'https://example.org/request?arg=1&arg2[]=中国', $canonical->get_redirect( 'http://example.org', '/request?arg=1&arg2[]=中国' ) );
	}

	public function testRelocate() {
		$canonical = new Site\Canonical( true, '', [], 'https://relocate.org' );
		$this->assertEquals( 'https://relocate.org/request?arg=1', $canonical->relocate_request( 'https://relocate.org', 'example.org', '/request?arg=1' ) );
		$this->assertEquals( 'https://relocate.org/request?arg=1', $canonical->relocate_request( 'https://relocate.org/', 'example.org', '/request?arg=1' ) );
		$this->assertEquals( 'https://relocate.org/request?arg[]=1', $canonical->relocate_request( 'https://relocate.org/', 'example.org', '/request?arg[]=1' ) );
		$this->assertEquals( 'https://relocate.org/request?arg[]=中国', $canonical->relocate_request( 'https://relocate.org/', 'example.org', '/request?arg[]=中国' ) );
	}

	public function testRelocateIgnore() {
		$canonical = new Site\Canonical( true, '', [], 'http://example.org' );
		$this->assertFalse( $canonical->relocate_request( 'https://relocate.org', 'example.org', '/wp-json/redirection' ) );
		$this->assertFalse( $canonical->relocate_request( 'https://relocate.org', 'example.org', '/wp-admin/index.php' ) );
		$this->assertFalse( $canonical->relocate_request( 'https://relocate.org', 'example.org', '/wp-login.php' ) );
	}

	public function testPreferredInvalidSite() {
		$canonical = new Site\Canonical( true, 'www', [], 'https://example.org' );
		$this->assertFalse( $canonical->get_redirect( 'http://example.org', '/request?arg=1' ) );

		$canonical = new Site\Canonical( true, 'nowww', [], 'https://www.example.org' );
		$this->assertFalse( $canonical->get_redirect( 'http://www.example.org', '/request?arg=1' ) );
	}

	public function testHttpsInvalidSite() {
		$canonical = new Site\Canonical( true, '', [], 'http://example.org' );
		$this->assertFalse( $canonical->get_redirect( 'http://example.org', '/request?arg=1' ) );
	}
}
