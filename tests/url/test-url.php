<?php

use Redirection\Url;

class UrlTest extends WP_UnitTestCase {
	public function testGetUrl() {
		$url = new Url\Url( '/test' );
		$this->assertEquals( '/test', $url->get_url() );
	}

	public function testGetPlainUrlFromPath() {
		$url = new Url\Match( '/test' );
		$this->assertEquals( '/test', $url->get_url() );
	}

	public function testGetPlainUrlAnsolute() {
		$url = new Url\Match( 'http://test.com/test' );
		$this->assertEquals( '/test', $url->get_url() );
	}

	public function testGetPlainUrlAnsoluteDouble() {
		$url = new Url\Match( 'http://test.com//test//' );
		$this->assertEquals( '//test/', $url->get_url() );
	}

	public function testGetPlainUrlEmpty() {
		$url = new Url\Match( '' );
		$this->assertEquals( '/', $url->get_url() );
	}

	public function testGetLowercasePlainUrl() {
		$url = new Url\Match( '/TEsT' );
		$this->assertEquals( '/test', $url->get_url() );
	}

	public function testGetNoTrailingSlashPlainUrl() {
		$url = new Url\Match( '/test/' );
		$this->assertEquals( '/test', $url->get_url() );
	}

	public function testEncode() {
		$url = new Url\Match( '/中国:again/thing?other=中国' );
		$this->assertEquals( '/%e4%b8%ad%e5%9b%bd:again/thing', $url->get_url() );
	}
	public function testNotIsMatchPlain() {
		$url = new Url\Url( '/cats' );
		$this->assertFalse( $url->is_match( '/bats', new Url\Source_Flags() ) );
	}

	public function testIsMatchPlain() {
		$url = new Url\Url( '/cats' );
		$this->assertTrue( $url->is_match( '/cats', new Url\Source_Flags() ) );
	}

	public function testIsMatchQuery() {
		$url = new Url\Url( '/cats?dogs=1' );
		$this->assertTrue( $url->is_match( '/cats?dogs=1', new Url\Source_Flags() ) );

		$url = new Url\Url( '/cats?dogs[]=1&dogs[]=2' );
		$this->assertTrue( $url->is_match( '/cats?dogs[]=1&dogs[]=2', new Url\Source_Flags() ) );
	}

	public function testIsNotMatchQuery() {
		$url = new Url\Url( '/cats?dogs[]=1&dogs[]=2' );
		$this->assertFalse( $url->is_match( '/cats?dogs[]=3&dogs[]=2', new Url\Source_Flags() ) );

		$url = new Url\Url( '/?order=wc_order_nq123124sdf0' );
		$this->assertTrue( $url->is_match( '/?download_file=123&order=wc_order_nq123124sdf0&email=test%40example.com&key=5237f783-054ae-40db-aced-69bd011cdb3c', new Url\Source_Flags( [ 'flag_query' => 'ignore' ] ) ) );
	}

	public function testIsMatchDouble() {
		$url = new Url\Url( '//' );
		$this->assertTrue( $url->is_match( '//', new Url\Source_Flags() ) );
	}

	public function testIsMatchAbsolute() {
		$url = new Url\Url( 'http://domain.com/cat/' );
		$this->assertTrue( $url->is_match( '/cat/', new Url\Source_Flags() ) );
	}

	public function testIsMatchRegex() {
		$url = new Url\Url( '/cat/\d' );
		$this->assertTrue( $url->is_match( '/cat/1', new Url\Source_Flags( [ 'flag_regex' => true ] ) ) );
	}

	public function testIsMatchEncodedRegex() {
		$url = new Url\Url( urlencode( '/für/\d' ) );
		$this->assertTrue( $url->is_match( '/für/1', new Url\Source_Flags( [ 'flag_regex' => true ] ) ) );
	}

	public function testIsMatchIgnoreQuery() {
		$url = new Url\Url( '/cat' );
		$this->assertTrue( $url->is_match( '/cat?things', new Url\Source_Flags( [ 'flag_query' => 'ignore' ] ) ) );
	}
}
