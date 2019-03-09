<?php

class UrlPathTest extends WP_UnitTestCase {
	public function testPathMatch() {
		$url = new Red_Url_Path( '/test' );
		$this->assertTrue( $url->is_match( '/test', new Red_Source_Flags() ) );
	}

	public function testPathNotMatch() {
		$url = new Red_Url_Path( '/test1' );
		$this->assertFalse( $url->is_match( '/test2', new Red_Source_Flags() ) );
		$this->assertFalse( $url->is_match( '/tEst1', new Red_Source_Flags() ) );
		$this->assertFalse( $url->is_match( '/test1/', new Red_Source_Flags() ) );
	}

	public function testPathMatchCase() {
		$url = new Red_Url_Path( '/test1' );
		$this->assertTrue( $url->is_match( '/teSt1', new Red_Source_Flags( [ 'flag_case' => true ] ) ) );
	}

	public function testPathMatchTrailing() {
		$url = new Red_Url_Path( '/test1/' );
		$this->assertTrue( $url->is_match( '/test1', new Red_Source_Flags( [ 'flag_trailing' => true ] ) ) );
	}

	public function testPathRelative() {
		$url = new Red_Url_Path( '/cats?this=query&more' );
		$this->assertEquals( '/cats', $url->get() );
	}

	public function testPathAbsolute() {
		$url = new Red_Url_Path( 'http://domain.com:303/cats?this=query&more' );
		$this->assertEquals( '/cats', $url->get() );
	}

	public function testPathDoubleSlash() {
		$url = new Red_Url_Path( '//cats//?this=query&more' );
		$this->assertEquals( '//cats//', $url->get() );
	}

	public function testRootQuery() {
		$url = new Red_Url_Path( '/?this=query&more' );
		$this->assertEquals( '/', $url->get() );
	}

	public function testRegexPath() {
		$url = new Red_Url_Path( '/thing\\?this=query&more' );
		$this->assertEquals( '/thing', $url->get() );
	}

	public function testRootQueryDouble() {
		$url = new Red_Url_Path( '//?this=query&more' );
		$this->assertEquals( '//', $url->get() );
	}

	public function testPathAbsoluteQueryParam() {
		$url = new Red_Url_Path( '/page.html?page=http://domain.com:303/cats?this=query&more' );
		$this->assertEquals( '/page.html', $url->get() );
	}

	public function testTrailingSlash() {
		$url = new Red_Url_Path( '/' );
		$this->assertEquals( '/', $url->get_without_trailing_slash() );
	}

	public function testDoubleTrailingSlash() {
		$url = new Red_Url_Path( '//' );
		$this->assertEquals( '//', $url->get_without_trailing_slash() );
	}

	public function testTrailingSlashLonger() {
		$url = new Red_Url_Path( '/something/' );
		$this->assertEquals( '/something', $url->get_without_trailing_slash() );
	}

	public function testTrailingSlashWithPath() {
		$url = new Red_Url_Path( '/something//' );
		$this->assertEquals( '/something/', $url->get_without_trailing_slash() );
	}
}
