<?php

class UrlTest extends WP_UnitTestCase {
	public function testGetUrl() {
		$url = new Red_Url( '/test' );
		$this->assertEquals( '/test', $url->get_url() );
	}

	public function testGetPlainUrlFromPath() {
		$url = new Red_Url_Match( '/test' );
		$this->assertEquals( '/test', $url->get_url() );
	}

	public function testGetPlainUrlAnsolute() {
		$url = new Red_Url_Match( 'http://test.com/test' );
		$this->assertEquals( '/test', $url->get_url() );
	}

	public function testGetPlainUrlEmpty() {
		$url = new Red_Url_Match( '' );
		$this->assertEquals( '/', $url->get_url() );
	}

	public function testGetLowercasePlainUrl() {
		$url = new Red_Url_Match( '/TEsT' );
		$this->assertEquals( '/test', $url->get_url() );
	}

	public function testGetNoTrailingSlashPlainUrl() {
		$url = new Red_Url_Match( '/test/' );
		$this->assertEquals( '/test', $url->get_url() );
	}

	public function testNotIsMatch() {
		$url = new Red_Url( '/cats' );
		$this->assertFalse( $url->is_match( '/bats', new Red_Source_Flags() ) );
	}

	public function testIsMatch() {
		$url = new Red_Url( '/cats' );
		$this->assertTrue( $url->is_match( '/cats', new Red_Source_Flags() ) );
	}
}
