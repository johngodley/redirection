<?php

class UrlQueryTest extends WP_UnitTestCase {
	public function testQueryMatchEmpty() {
		$url = new Red_Url_Query( '/test', new Red_Source_Flags() );
		$this->assertTrue( $url->is_match( '', new Red_Source_Flags() ) );
	}

	public function testQueryNotMatch() {
		$url = new Red_Url_Query( '/test', new Red_Source_Flags() );
		$this->assertFalse( $url->is_match( '/test?a', new Red_Source_Flags() ) );
	}

	public function testQueryNotMatchDiffValue() {
		$url = new Red_Url_Query( '/test?a=1', new Red_Source_Flags() );
		$this->assertFalse( $url->is_match( '/test?a=2', new Red_Source_Flags() ) );
	}

	public function testQueryNotMatchDiffKeys() {
		$url = new Red_Url_Query( '/test?a=1', new Red_Source_Flags() );
		$this->assertFalse( $url->is_match( '/test?b=1', new Red_Source_Flags() ) );
	}

	public function testQueryMatchDiffOrder() {
		$url = new Red_Url_Query( '/this?a=1&b=2', new Red_Source_Flags() );
		$this->assertTrue( $url->is_match( '/this?b=2&a=1', new Red_Source_Flags() ) );
	}

	public function testQueryMatchInvalidParams() {
		$url = new Red_Url_Query( '/this?=2', new Red_Source_Flags() );
		$this->assertTrue( $url->is_match( '/this?=2', new Red_Source_Flags() ) );
	}

	public function testQueryNotMatchInvalidParams() {
		$url = new Red_Url_Query( '/this?=2', new Red_Source_Flags() );
		$this->assertFalse( $url->is_match( '/this?=1', new Red_Source_Flags() ) );
	}

	public function testQueryMatchZeroParam() {
		$url = new Red_Url_Query( '/this?cat=0', new Red_Source_Flags() );
		$this->assertTrue( $url->is_match( '/this?cat=0', new Red_Source_Flags() ) );
	}

	public function testQueryMatchIgnoreCase() {
		$url = new Red_Url_Query( '/this?cats=2', new Red_Source_Flags() );
		$this->assertTrue( $url->is_match( '/this?CATS=2', new Red_Source_Flags( [ 'flag_case' => true ] ) ) );
	}

	public function testQueryMatchIgnore() {
		$url = new Red_Url_Query( '', new Red_Source_Flags() );
		$this->assertTrue( $url->is_match( '/this?b=2&a=1', new Red_Source_Flags( [ 'flag_query' => 'ignore' ] ) ) );
	}

	public function testQueryMatchIgnoreDefault() {
		$url = new Red_Url_Query( '/?a=1', new Red_Source_Flags() );
		$this->assertTrue( $url->is_match( '/this?b=2&a=1', new Red_Source_Flags( [ 'flag_query' => 'ignore' ] ) ) );
	}

	public function testQueryMatchIgnorePass() {
		$url = new Red_Url_Query( '/?a=1', new Red_Source_Flags() );
		$this->assertTrue( $url->is_match( '/this?b=2&a=1', new Red_Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryMatchArray() {
		$url = new Red_Url_Query( '/?arrs1[]=1&arrs1[]=2&arrs1[]=3&arrs2[]=1&arrs2[]=2&arrs2[]=3', new Red_Source_Flags() );
		$this->assertTrue( $url->is_match( '/this?arrs2[]=1&arrs2[]=2&arrs2[]=3&arrs1[]=1&arrs1[]=2&arrs1[]=3', new Red_Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryNotMatchArray() {
		$url = new Red_Url_Query( '/?arrs1[]=1&arrs1[]=2&arrs1[]=3&arrs2[]=1&arrs2[]=2&arrs2[]=5', new Red_Source_Flags() );
		$this->assertFalse( $url->is_match( '/this?arrs2[]=1&arrs2[]=2&arrs2[]=3&arrs1[]=1&arrs1[]=2&arrs1[]=3', new Red_Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryNotMatchArrayExact() {
		$url = new Red_Url_Query( '/?arrs1[]=1&arrs1[]=2&arrs1[]=3', new Red_Source_Flags() );
		$this->assertFalse( $url->is_match( '/this?arrs2[]=1&arrs2[]=2&arrs2[]=3&arrs1[]=1&arrs1[]=2&arrs1[]=3', new Red_Source_Flags() ) );

		$url = new Red_Url_Query( '/?arrs1[]=1&arrs1[]=2&arrs1[]=3&arrs2=5', new Red_Source_Flags() );
		$this->assertFalse( $url->is_match( '/this?arrs2[]=1&arrs2[]=2&arrs2[]=3&arrs1[]=1&arrs1[]=2&arrs1[]=3', new Red_Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryArrayCase() {
		$url = new Red_Url_Query( '/?hasCase[something]=test', new Red_Source_Flags() );

		$this->assertTrue( $url->is_match( '/?hasCase[something]=test', new Red_Source_Flags() ) );
		$this->assertFalse( $url->is_match( '/?hascase[something]=test', new Red_Source_Flags() ) );
	}

	public function testQueryArrayCaseInsensitive() {
		$url = new Red_Url_Query( '/?hasCase[something]=test', new Red_Source_Flags( [ 'flag_case' => true ] ) );

		$this->assertTrue( $url->is_match( '/?hasCase[something]=test', new Red_Source_Flags( [ 'flag_case' => true ] ) ) );
		$this->assertTrue( $url->is_match( '/?hascase[something]=test', new Red_Source_Flags( [ 'flag_case' => true ] ) ) );
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

	public function testQueryPassCase() {
		$this->assertEquals( '/?this=THAt', Red_Url_Query::add_to_target( '/', '/?this=THAt', new Red_Source_Flags( [ 'flag_query' => 'pass', 'flag_case' => true ] ) ) );
	}

	public function testQueryPassNoValue() {
		$this->assertEquals( '/?this', Red_Url_Query::add_to_target( '/', '/?this', new Red_Source_Flags( [ 'flag_query' => 'pass', 'flag_case' => true ] ) ) );
	}

	public function testFragment() {
		$this->assertEquals( '/?arrs1[]=1&arrs1[]=2&stuff=1#fragment', Red_Url_Query::add_to_target( '/#fragment', '/?arrs1[]=1&arrs1[]=2&stuff=1', new Red_Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryDoubleSlash() {
		$url = new Red_Url_Query( '//?this=query&more', new Red_Source_Flags() );
		$this->assertEquals( [
			'this' => 'query',
			'more' => '',
		], $url->get() );
	}

	public function testQueryUrl() {
		$url = new Red_Url_Query( '//page.html?page=http://domain.com:303/cats?this=query&more', new Red_Source_Flags() );
		$this->assertEquals( [
			'page' => 'http://domain.com:303/cats?this=query',
			'more' => '',
		], $url->get() );
	}

	public function testEscapedQueryUrlBefore() {
		$url = new Red_Url_Query( '/page.html\\?page=http://domain.com:303/cats?this=query&more', new Red_Source_Flags() );
		$this->assertEquals( [
			'page' => 'http://domain.com:303/cats?this=query',
			'more' => '',
		], $url->get() );
	}

	public function testEscapedQueryUrlAfter() {
		$url = new Red_Url_Query( '/page.html?page=http://domain.com:303/cats\\?this=query&more', new Red_Source_Flags() );
		$this->assertEquals( [
			'page' => 'http://domain.com:303/cats\\?this=query',
			'more' => '',
		], $url->get() );
	}

	public function testQueryPhpArray() {
		$url = new Red_Url_Query( '/?thing=save&arrs1[]=1&arrs1[]=2&arrs1[]=3&arrs2[]=1&arrs2[]=2&arrs2[]=3', new Red_Source_Flags() );
		$this->assertEquals( [
			'thing' => 'save',
			'arrs1' => [ 1, 2, 3 ],
			'arrs2' => [ 1, 2, 3 ],
		], $url->get() );
	}

	public function testArraySame() {
		$url = new Red_Url_Query( '', new Red_Source_Flags() );
		$this->assertEquals( [], $url->get_query_same( [], [], false ) );
		$this->assertEquals( [ 'a' => 'a' ], $url->get_query_same( [ 'a' => 'a' ], [ 'a' => 'a' ], false ) );
		$this->assertEquals( [ 'a' => '' ], $url->get_query_same( [ 'a' => '' ], [ 'a' => '' ], false ) );
		$this->assertEquals( [ 'a' => 'a' ], $url->get_query_same( [ 'a' => 'a', 'b' => 'b' ], [ 'a' => 'a' ], false ) );
		$this->assertEquals( [ 'a' => 'a' ], $url->get_query_same( [ 'a' => 'a' ], [ 'a' => 'a', 'b' => 'b' ], false ) );

		// Test case insensitive
		$this->assertEquals( [ 'a' => 'A' ], $url->get_query_same( [ 'a' => 'A' ], [ 'a' => 'a' ], true ) );
		$this->assertEquals( [ 'A' => 'a' ], $url->get_query_same( [ 'A' => 'a' ], [ 'a' => 'a' ], true ) );
		$this->assertEquals( [ 'A' => 'A' ], $url->get_query_same( [ 'A' => 'A' ], [ 'a' => 'a' ], true ) );
		$this->assertEquals( [ 'a' => 'A' ], $url->get_query_same( [ 'a' => 'A' ], [ 'a' => 'A' ], true ) );
		$this->assertEquals( [ 'A' => 'a' ], $url->get_query_same( [ 'A' => 'a' ], [ 'A' => 'a' ], true ) );
		$this->assertEquals( [ 'A' => 'A' ], $url->get_query_same( [ 'A' => 'A' ], [ 'A' => 'A' ], true ) );
	}

	public function testArraySameDeep() {
		$url = new Red_Url_Query( '', new Red_Source_Flags() );
		$this->assertEquals( [ 'a' => [ '1' => '1', '2' => '2' ] ], $url->get_query_same( [ 'a' => [ '1' => '1', '2' => '2' ] ], [ 'a' => [ '1' => '1', '2' => '2' ] ], false ) );
		$this->assertEquals( [], $url->get_query_same( [ 'a' => [ '1' => '1', '2' => '2' ] ], [ 'a' => [ '1' => '1' ] ], false ) );
	}

	public function testArrayDiff() {
		$url = new Red_Url_Query( '', new Red_Source_Flags() );
		$this->assertEquals( [], $url->get_query_diff( [], [] ) );
		$this->assertEquals( [], $url->get_query_diff( [ 'a' => 'a' ], [ 'a' => 'a' ] ) );
		$this->assertEquals( [ 'a' => 'a' ], $url->get_query_diff( [ 'a' => 'a' ], [] ) );
		$this->assertEquals( [ 'a' => 'a' ], $url->get_query_diff( [ 'a' => 'a' ], [ 'b' => 'b' ] ) );
	}

	public function testArrayDiffDeep() {
		$url = new Red_Url_Query( '', new Red_Source_Flags() );
		$this->assertEquals( [ 'a' => [ 'a' => 'a' ] ], $url->get_query_diff( [ 'a' => [ 'a' => 'a' ] ], [] ) );
		$this->assertEquals( [], $url->get_query_diff( [ 'a' => [ 'a' => 'a' ] ], [ 'a' => [ 'a' => 'a' ] ] ) );
		$this->assertEquals( [ 'a' => [ 'a' => 'a' ] ], $url->get_query_diff( [ 'a' => [ 'a' => 'a' ] ], [ 'a' => [ 'a' => 'b' ] ] ) );
	}

	public function testQueryAfterNone() {
		$url = new Red_Url_Query( '', new Red_Source_Flags() );
		$this->assertEquals( '', $url->get_query_after( '/no-query' ) );
	}

	public function testQueryAfterSingle() {
		$url = new Red_Url_Query( '', new Red_Source_Flags() );
		$this->assertEquals( 'cat', $url->get_query_after( '/query?cat' ) );
	}

	public function testQueryAfterEscaped() {
		$url = new Red_Url_Query( '', new Red_Source_Flags() );
		$this->assertEquals( 'cat', $url->get_query_after( '/query\\?cat' ) );
	}

	public function testQueryAfterBoth() {
		$url = new Red_Url_Query( '', new Red_Source_Flags() );
		$this->assertEquals( 'dog\\?cat', $url->get_query_after( '/query?dog\\?cat' ) );

		$url = new Red_Url_Query( '', new Red_Source_Flags() );
		$this->assertEquals( 'cat?cat', $url->get_query_after( '/query\\?cat?cat' ) );
	}
}
