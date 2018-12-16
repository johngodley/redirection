<?php

class UrlQueryTest extends WP_UnitTestCase {
	public function testQueryMatchEmpty() {
		$url = new Red_Url_Query( '/test' );
		$this->assertTrue( $url->is_match( '', new Red_Source_Flags() ) );
	}

	public function testQueryNotMatch() {
		$url = new Red_Url_Query( '/test' );
		$this->assertFalse( $url->is_match( '/test?a', new Red_Source_Flags() ) );
	}

	public function testQueryNotMatchDiffValue() {
		$url = new Red_Url_Query( '/test?a=1' );
		$this->assertFalse( $url->is_match( '/test?a=2', new Red_Source_Flags() ) );
	}

	public function testQueryNotMatchDiffKeys() {
		$url = new Red_Url_Query( '/test?a=1' );
		$this->assertFalse( $url->is_match( '/test?b=1', new Red_Source_Flags() ) );
	}

	public function testQueryMatchDiffOrder() {
		$url = new Red_Url_Query( '/this?a=1&b=2' );
		$this->assertTrue( $url->is_match( '/this?b=2&a=1', new Red_Source_Flags() ) );
	}

	public function testQueryMatchIgnore() {
		$url = new Red_Url_Query( '' );
		$this->assertTrue( $url->is_match( '/this?b=2&a=1', new Red_Source_Flags( [ 'queryMatch' => 'ignore' ] ) ) );
	}

	public function testQueryMatchIgnoreDefault() {
		$url = new Red_Url_Query( 'a=1' );
		$this->assertTrue( $url->is_match( '/this?b=2&a=1', new Red_Source_Flags( [ 'queryMatch' => 'ignore' ] ) ) );
	}

	public function testQueryPassNoParams() {
		$this->assertEquals( '/cats', Red_Url_Query::add_to_target( '/cats', '/target', new Red_Source_Flags( [ 'queryPass' => true ] ) ) );
	}

	public function testQueryPassParams() {
		$this->assertEquals( '/cats?hey=there', Red_Url_Query::add_to_target( '/cats', '/target?hey=there', new Red_Source_Flags( [ 'queryPass' => true ] ) ) );
	}

	public function testQueryPassParamsEncoding() {
		$this->assertEquals( '/cats?this=a+cat', Red_Url_Query::add_to_target( '/cats', '/target?this=a%20cat', new Red_Source_Flags( [ 'queryPass' => true ] ) ) );
		$this->assertEquals( '/cats?this=a+cat', Red_Url_Query::add_to_target( '/cats', '/target?this=a+cat', new Red_Source_Flags( [ 'queryPass' => true ] ) ) );
	}

	public function testQueryPassParamsWithDefaults() {
		$this->assertEquals( '/cats?cat=a&thing=cat', Red_Url_Query::add_to_target( '/cats?cat=a', '/target?thing=cat', new Red_Source_Flags( [ 'queryPass' => true ] ) ) );
	}

	public function testQueryPassParamsNoOverride() {
		$this->assertEquals( '/cats?cat=a', Red_Url_Query::add_to_target( '/cats?cat=a', '/target?cat=b', new Red_Source_Flags( [ 'queryPass' => true ] ) ) );
	}
}
