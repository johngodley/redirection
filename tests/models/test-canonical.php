<?php

class Canonical_Test extends WP_UnitTestCase {
	public function testNoCanonical() {
		$canonical = new Redirection_Canonical( false, '', [] );
		$this->assertFalse( $canonical->get_redirect( 'http://example.org', '/request?arg=1' ) );
	}

	public function testHttps() {
		$canonical = new Redirection_Canonical( true, '', [] );
		$this->assertEquals( 'https://example.org/request?arg=1', $canonical->get_redirect( 'http://example.org', '/request?arg=1' ) );
		$this->assertEquals( 'https://example.org/request?arg=1', $canonical->get_redirect( 'https://example.org', '/request?arg=1' ) );
	}

	public function testPreferredWWW() {
		$canonical = new Redirection_Canonical( true, 'www', [] );
		$this->assertEquals( 'https://www.example.org/request?arg=1', $canonical->get_redirect( 'http://example.org', '/request?arg=1' ) );
		$this->assertEquals( 'https://www.example.org/request?arg=1', $canonical->get_redirect( 'http://www.example.org', '/request?arg=1' ) );
	}

	public function testPreferredRemoveWWW() {
		$canonical = new Redirection_Canonical( true, 'nowww', [] );
		$this->assertEquals( 'https://example.org/request?arg=1', $canonical->get_redirect( 'http://www.example.org', '/request?arg=1' ) );
		$this->assertEquals( 'https://example.org/request?arg=1', $canonical->get_redirect( 'http://example.org', '/request?arg=1' ) );
	}

	public function testAlias() {
		$canonical = new Redirection_Canonical( true, '', [ 'cat.com', 'dog.com' ] );
		$this->assertEquals( 'https://example.org/request?arg=1', $canonical->get_redirect( 'http://cat.com', '/request?arg=1' ) );
		$this->assertEquals( 'https://example.org/request?arg=1', $canonical->get_redirect( 'http://dog.com', '/request?arg=1' ) );
		$this->assertEquals( 'https://example.org/request?arg=1', $canonical->get_redirect( 'http://example.org', '/request?arg=1' ) );
	}

	public function testRelocate() {
		$canonical = new Redirection_Canonical( true, '', [] );
		$this->assertEquals( 'https://relocate.org/request?arg=1', $canonical->relocate_request( 'https://relocate.org', 'example.org', '/request?arg=1' ) );
	}

	public function testRelocateIgnore() {
		$canonical = new Redirection_Canonical( true, '', [] );
		$this->assertFalse( $canonical->relocate_request( 'https://relocate.org', 'example.org', '/wp-json/redirection' ) );
		$this->assertFalse( $canonical->relocate_request( 'https://relocate.org', 'example.org', '/wp-admin/index.php' ) );
		$this->assertFalse( $canonical->relocate_request( 'https://relocate.org', 'example.org', '/wp-login.php' ) );
	}
}
