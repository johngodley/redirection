<?php

class UrlPathTest extends WP_UnitTestCase {
	public function testPathMatch() {
		$url = new Path( '/test' );
		$this->assertTrue( $url->is_match( '/test', new Url\Source_Flags() ) );
	}

	public function testPathNotMatch() {
		$url = new Path( '/test1' );
		$this->assertFalse( $url->is_match( '/test2', new Url\Source_Flags() ) );
		$this->assertFalse( $url->is_match( '/tEst1', new Url\Source_Flags() ) );
		$this->assertFalse( $url->is_match( '/test1/', new Url\Source_Flags() ) );
	}

	public function testPathMatchCase() {
		$url = new Path( '/test1' );
		$this->assertTrue( $url->is_match( '/teSt1', new Url\Source_Flags( [ 'flag_case' => true ] ) ) );
	}

	public function testPathMatchTrailing() {
		$url = new Path( '/test1/' );
		$this->assertTrue( $url->is_match( '/test1', new Url\Source_Flags( [ 'flag_trailing' => true ] ) ) );
	}

	public function testPathRelative() {
		$url = new Path( '/cats?this=query&more' );
		$this->assertEquals( '/cats', $url->get() );
	}

	public function testPathAbsolute() {
		$url = new Path( 'http://domain.com:303/cats?this=query&more' );
		$this->assertEquals( '/cats', $url->get() );
	}

	public function testPathDoubleSlash() {
		$url = new Path( '//cats//?this=query&more' );
		$this->assertEquals( '//cats//', $url->get() );
	}

	public function testRootQuery() {
		$url = new Path( '/?this=query&more' );
		$this->assertEquals( '/', $url->get() );
	}

	public function testRegexPath() {
		$url = new Path( '/thing\\?this=query&more' );
		$this->assertEquals( '/thing', $url->get() );
	}

	public function testEscapedQuery() {
		$url = new Path( '/thing\\?this=query&more?another' );
		$this->assertEquals( '/thing', $url->get() );
	}

	public function testRootQueryDouble() {
		$url = new Path( '//?this=query&more' );
		$this->assertEquals( '//', $url->get() );
	}

	public function testPathAbsoluteQueryParam() {
		$url = new Path( '/page.html?page=http://domain.com:303/cats?this=query&more' );
		$this->assertEquals( '/page.html', $url->get() );
	}

	public function testTrailingSlash() {
		$url = new Path( '/' );
		$this->assertEquals( '/', $url->get_without_trailing_slash() );
	}

	public function testDoubleTrailingSlash() {
		$url = new Path( '//' );
		$this->assertEquals( '/', $url->get_without_trailing_slash() );
	}

	public function testTrailingSlashLonger() {
		$url = new Path( '/something/' );
		$this->assertEquals( '/something', $url->get_without_trailing_slash() );
	}

	public function testTrailingSlashWithPath() {
		$url = new Path( '/something//' );
		$this->assertEquals( '/something/', $url->get_without_trailing_slash() );
	}
}
