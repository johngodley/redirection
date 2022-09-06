<?php

class UrlRequestTest extends WP_UnitTestCase {
	public function testNoUrl() {
		$request = new Request( '' );

		$this->assertFalse( $request->is_valid() );
		$this->assertEquals( '', $request->get_decoded_url() );
		$this->assertEquals( '', $request->get_original_url() );
	}

	public function testPlainUrl() {
		$url = '/thing';
		$request = new Request( $url );

		$this->assertTrue( $request->is_valid() );
		$this->assertEquals( $url, $request->get_decoded_url() );
		$this->assertEquals( $url, $request->get_original_url() );
	}

	public function testPlainQueryUrl() {
		$url = '/thing?param=1';
		$request = new Request( $url );

		$this->assertTrue( $request->is_valid() );
		$this->assertEquals( $url, $request->get_decoded_url() );
		$this->assertEquals( $url, $request->get_original_url() );
	}

	public function testEncodedPathUrl() {
		$url = '/th%20ing?param=1';
		$expected = '/th ing?param=1';
		$request = new Request( $url );

		$this->assertTrue( $request->is_valid() );
		$this->assertEquals( $expected, $request->get_decoded_url() );
		$this->assertEquals( $expected, $request->get_original_url() );
	}

	public function testEncodedQueryUrl() {
		$url = '/thing?param=%2B';
		$request = new Request( $url );

		$this->assertTrue( $request->is_valid() );
		$this->assertEquals( '/thing?param=+', $request->get_decoded_url() );
		$this->assertEquals( $url, $request->get_original_url() );
	}

	public function testEmptyQueryUrl() {
		$url = '/thing?';
		$request = new Request( $url );

		$this->assertTrue( $request->is_valid() );
		$this->assertEquals( $url, $request->get_decoded_url() );
		$this->assertEquals( $url, $request->get_original_url() );
	}
}
