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

	public function testGetPlainUrlAnsoluteDouble() {
		$url = new Red_Url_Match( 'http://test.com//test//' );
		$this->assertEquals( '//test/', $url->get_url() );
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

	public function testNotIsMatchPlain() {
		$url = new Red_Url( '/cats' );
		$this->assertFalse( $url->is_match( '/bats', new Red_Source_Flags() ) );
	}

	public function testIsMatchPlain() {
		$url = new Red_Url( '/cats' );
		$this->assertTrue( $url->is_match( '/cats', new Red_Source_Flags() ) );
	}

	public function testIsMatchQuery() {
		$url = new Red_Url( '/cats?dogs=1' );
		$this->assertTrue( $url->is_match( '/cats?dogs=1', new Red_Source_Flags() ) );

		$url = new Red_Url( '/cats?dogs[]=1&dogs[]=2' );
		$this->assertTrue( $url->is_match( '/cats?dogs[]=1&dogs[]=2', new Red_Source_Flags() ) );
	}

	public function testIsNotMatchQuery() {
		$url = new Red_Url( '/cats?dogs[]=1&dogs[]=2' );
		$this->assertFalse( $url->is_match( '/cats?dogs[]=3&dogs[]=2', new Red_Source_Flags() ) );

		$url = new Red_Url( '/?order=wc_order_nq123124sdf0' );
		$this->assertTrue( $url->is_match( '/?download_file=123&order=wc_order_nq123124sdf0&email=test%40example.com&key=5237f783-054ae-40db-aced-69bd011cdb3c', new Red_Source_Flags( [ 'flag_query' => 'ignore' ] ) ) );
	}

	public function testIsMatchDouble() {
		$url = new Red_Url( '//' );
		$this->assertTrue( $url->is_match( '//', new Red_Source_Flags() ) );
	}

	public function testIsMatchAbsolute() {
		$url = new Red_Url( 'http://domain.com/cat/' );
		$this->assertTrue( $url->is_match( '/cat/', new Red_Source_Flags() ) );
	}

	public function testIsMatchRegex() {
		$url = new Red_Url( '/cat/\d' );
		$this->assertTrue( $url->is_match( '/cat/1', new Red_Source_Flags( [ 'flag_regex' => true ] ) ) );
	}

	public function testIsMatchIgnoreQueryx() {
		$url = new Red_Url( '/cat' );
		$this->assertTrue( $url->is_match( '/cat?things', new Red_Source_Flags( [ 'flag_query' => 'ignore' ] ) ) );
	}
}
