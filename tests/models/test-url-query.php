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

	public function testQueryMatchInvalidParams() {
		$url = new Red_Url_Query( '/this?=2' );
		$this->assertTrue( $url->is_match( '/this?=2', new Red_Source_Flags() ) );
	}

	public function testQueryNotMatchInvalidParams() {
		$url = new Red_Url_Query( '/this?=2' );
		$this->assertFalse( $url->is_match( '/this?=1', new Red_Source_Flags() ) );
	}

	public function testQueryMatchIgnore() {
		$url = new Red_Url_Query( '' );
		$this->assertTrue( $url->is_match( '/this?b=2&a=1', new Red_Source_Flags( [ 'flag_query' => 'ignore' ] ) ) );
	}

	public function testQueryMatchIgnoreDefault() {
		$url = new Red_Url_Query( '/?a=1' );
		$this->assertTrue( $url->is_match( '/this?b=2&a=1', new Red_Source_Flags( [ 'flag_query' => 'ignore' ] ) ) );
	}

	public function testQueryMatchIgnorePass() {
		$url = new Red_Url_Query( '/?a=1' );
		$this->assertTrue( $url->is_match( '/this?b=2&a=1', new Red_Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryMatchArray() {
		$url = new Red_Url_Query( '/?arrs1[]=1&arrs1[]=2&arrs1[]=3&arrs2[]=1&arrs2[]=2&arrs2[]=3' );
		$this->assertTrue( $url->is_match( '/this?arrs2[]=1&arrs2[]=2&arrs2[]=3&arrs1[]=1&arrs1[]=2&arrs1[]=3', new Red_Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryNotMatchArray() {
		$url = new Red_Url_Query( '/?arrs1[]=1&arrs1[]=2&arrs1[]=3&arrs2[]=1&arrs2[]=2&arrs2[]=5' );
		$this->assertFalse( $url->is_match( '/this?arrs2[]=1&arrs2[]=2&arrs2[]=3&arrs1[]=1&arrs1[]=2&arrs1[]=3', new Red_Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryNotMatchArrayExact() {
		$url = new Red_Url_Query( '/?arrs1[]=1&arrs1[]=2&arrs1[]=3' );
		$this->assertFalse( $url->is_match( '/this?arrs2[]=1&arrs2[]=2&arrs2[]=3&arrs1[]=1&arrs1[]=2&arrs1[]=3', new Red_Source_Flags() ) );

		$url = new Red_Url_Query( '/?arrs1[]=1&arrs1[]=2&arrs1[]=3&arrs2=5' );
		$this->assertFalse( $url->is_match( '/this?arrs2[]=1&arrs2[]=2&arrs2[]=3&arrs1[]=1&arrs1[]=2&arrs1[]=3', new Red_Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryPassNoParams() {
		$this->assertEquals( '/cats', Red_Url_Query::add_to_target( '/cats', '/target', new Red_Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryPassParams() {
		$this->assertEquals( '/cats?hey=there', Red_Url_Query::add_to_target( '/cats', '/target?hey=there', new Red_Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryPassParamsInt() {
		$this->assertEquals( '/cats?1=there', Red_Url_Query::add_to_target( '/cats', '/target?1=there', new Red_Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryPassParamsEncoding() {
		$this->assertEquals( '/cats?this=a+cat', Red_Url_Query::add_to_target( '/cats', '/target?this=a%20cat', new Red_Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
		$this->assertEquals( '/cats?this=a+cat', Red_Url_Query::add_to_target( '/cats', '/target?this=a+cat', new Red_Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryPassParamsWithDefaults() {
		$this->assertEquals( '/cats?cat=a&thing=cat', Red_Url_Query::add_to_target( '/cats?cat=a', '/target?thing=cat', new Red_Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryPassParamsNoOverride() {
		$this->assertEquals( '/cats?cat=a', Red_Url_Query::add_to_target( '/cats?cat=a', '/target?cat=b', new Red_Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryPassArray() {
		$this->assertEquals( '/?arrs1[]=1&arrs1[]=2&arrs1[]=3&arrs2[]=1&arrs2[]=2&arrs2[]=3', Red_Url_Query::add_to_target( '/', '/?arrs1[]=1&arrs1[]=2&arrs1[]=3&arrs2[]=1&arrs2[]=2&arrs2[]=3', new Red_Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryDoubleSlash() {
		$url = new Red_Url_Query( '//?this=query&more' );
		$this->assertEquals( [
			'this' => 'query',
			'more' => '',
		], $url->get() );
	}

	public function testQueryUrl() {
		$url = new Red_Url_Query( '//page.html?page=http://domain.com:303/cats?this=query&more' );
		$this->assertEquals( [
			'page' => 'http://domain.com:303/cats?this=query',
			'more' => '',
		], $url->get() );
	}

	public function testEscapedQueryUrlBefore() {
		$url = new Red_Url_Query( '/page.html\\?page=http://domain.com:303/cats?this=query&more' );
		$this->assertEquals( [
			'page' => 'http://domain.com:303/cats?this=query',
			'more' => '',
		], $url->get() );
	}

	public function testEscapedQueryUrlAfter() {
		$url = new Red_Url_Query( '/page.html?page=http://domain.com:303/cats\\?this=query&more' );
		$this->assertEquals( [
			'page' => 'http://domain.com:303/cats\\?this=query',
			'more' => '',
		], $url->get() );
	}

	public function testQueryPhpArray() {
		$url = new Red_Url_Query( '/?thing=save&arrs1[]=1&arrs1[]=2&arrs1[]=3&arrs2[]=1&arrs2[]=2&arrs2[]=3' );
		$this->assertEquals( [
			'thing' => 'save',
			'arrs1' => [ 1, 2, 3 ],
			'arrs2' => [ 1, 2, 3 ],
		], $url->get() );
	}

	public function testArraySame() {
		$url = new Red_Url_Query( '' );
		$this->assertEquals( [], $url->get_query_same( [], [] ) );
		$this->assertEquals( [ 'a' => 'a' ], $url->get_query_same( [ 'a' => 'a' ], [ 'a' => 'a' ] ) );
		$this->assertEquals( [ 'a' => '' ], $url->get_query_same( [ 'a' => '' ], [ 'a' => '' ] ) );
		$this->assertEquals( [ 'a' => 'a' ], $url->get_query_same( [ 'a' => 'a', 'b' => 'b' ], [ 'a' => 'a' ] ) );
		$this->assertEquals( [ 'a' => 'a' ], $url->get_query_same( [ 'a' => 'a' ], [ 'a' => 'a', 'b' => 'b' ] ) );
	}

	public function testArraySameDeep() {
		$url = new Red_Url_Query( '' );
		$this->assertEquals( [ 'a' => [ '1' => '1', '2' => '2' ] ], $url->get_query_same( [ 'a' => [ '1' => '1', '2' => '2' ] ], [ 'a' => [ '1' => '1', '2' => '2' ] ] ) );
		$this->assertEquals( [], $url->get_query_same( [ 'a' => [ '1' => '1', '2' => '2' ] ], [ 'a' => [ '1' => '1' ] ] ) );
	}

	public function testArrayDiff() {
		$url = new Red_Url_Query( '' );
		$this->assertEquals( [], $url->get_query_diff( [], [] ) );
		$this->assertEquals( [], $url->get_query_diff( [ 'a' => 'a' ], [ 'a' => 'a' ] ) );
		$this->assertEquals( [ 'a' => 'a' ], $url->get_query_diff( [ 'a' => 'a' ], [] ) );
		$this->assertEquals( [ 'a' => 'a' ], $url->get_query_diff( [ 'a' => 'a' ], [ 'b' => 'b' ] ) );
	}

	public function testArrayDiffDeep() {
		$url = new Red_Url_Query( '' );
		$this->assertEquals( [ 'a' => [ 'a' => 'a' ] ], $url->get_query_diff( [ 'a' => [ 'a' => 'a' ] ], [] ) );
		$this->assertEquals( [], $url->get_query_diff( [ 'a' => [ 'a' => 'a' ] ], [ 'a' => [ 'a' => 'a' ] ] ) );
		$this->assertEquals( [ 'a' => [ 'a' => 'a' ] ], $url->get_query_diff( [ 'a' => [ 'a' => 'a' ] ], [ 'a' => [ 'a' => 'b' ] ] ) );
	}
}
