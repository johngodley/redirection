<?php

use Redirection\Url;

class UrlPathTest extends WP_UnitTestCase {
	public function testPathMatch() {
		$url = new Url\Path( '/test' );
		$this->assertTrue( $url->is_match( '/test', new Url\Source_Flags() ) );
	}

	public function testPathNotMatch() {
		$url = new Url\Path( '/test1' );
		$this->assertFalse( $url->is_match( '/test2', new Url\Source_Flags() ) );
		$this->assertFalse( $url->is_match( '/tEst1', new Url\Source_Flags() ) );
		$this->assertFalse( $url->is_match( '/test1/', new Url\Source_Flags() ) );
	}

	public function testPathMatchCase() {
		$url = new Url\Path( '/test1' );
		$this->assertTrue( $url->is_match( '/teSt1', new Url\Source_Flags( [ 'flag_case' => true ] ) ) );
	}

	public function testPathMatchTrailing() {
		$url = new Url\Path( '/test1/' );
		$this->assertTrue( $url->is_match( '/test1', new Url\Source_Flags( [ 'flag_trailing' => true ] ) ) );
	}

	public function testPathRelative() {
		$url = new Url\Path( '/cats?this=query&more' );
		$this->assertEquals( '/cats', $url->get() );
	}

	public function testPathAbsolute() {
		$url = new Url\Path( 'http://domain.com:303/cats?this=query&more' );
		$this->assertEquals( '/cats', $url->get() );
	}

	public function testPathDoubleSlash() {
		$url = new Url\Path( '//cats//?this=query&more' );
		$this->assertEquals( '//cats//', $url->get() );
	}

	public function testRootQuery() {
		$url = new Url\Path( '/?this=query&more' );
		$this->assertEquals( '/', $url->get() );
	}

	public function testRegexPath() {
		$url = new Url\Path( '/thing\\?this=query&more' );
		$this->assertEquals( '/thing', $url->get() );
	}

	public function testEscapedQuery() {
		$url = new Url\Path( '/thing\\?this=query&more?another' );
		$this->assertEquals( '/thing', $url->get() );
	}

	public function testRootQueryDouble() {
		$url = new Url\Path( '//?this=query&more' );
		$this->assertEquals( '//', $url->get() );
	}

	public function testPathAbsoluteQueryParam() {
		$url = new Url\Path( '/page.html?page=http://domain.com:303/cats?this=query&more' );
		$this->assertEquals( '/page.html', $url->get() );
	}

	public function testTrailingSlash() {
		$url = new Url\Path( '/' );
		$this->assertEquals( '/', $url->get_without_trailing_slash() );
	}

	public function testDoubleTrailingSlash() {
		$url = new Url\Path( '//' );
		$this->assertEquals( '/', $url->get_without_trailing_slash() );
	}

	public function testTrailingSlashLonger() {
		$url = new Url\Path( '/something/' );
		$this->assertEquals( '/something', $url->get_without_trailing_slash() );
	}

	public function testTrailingSlashWithPath() {
		$url = new Url\Path( '/something//' );
		$this->assertEquals( '/something/', $url->get_without_trailing_slash() );
	}
}
