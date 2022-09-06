<?php

use Redirection\Url\Query;
use Redirection\Url\Source_Flags;

class UrlQueryTest extends WP_UnitTestCase {
	public function testQueryMatchEmpty() {
		$url = new Query( '/test', new Source_Flags() );
		$this->assertTrue( $url->is_match( '', new Source_Flags() ) );
	}

	public function testQueryNotMatch() {
		$url = new Query( '/test', new Source_Flags() );
		$this->assertFalse( $url->is_match( '/test?a', new Source_Flags() ) );
	}

	public function testQueryNotMatchDiffValue() {
		$url = new Query( '/test?a=1', new Source_Flags() );
		$this->assertFalse( $url->is_match( '/test?a=2', new Source_Flags() ) );
	}

	public function testQueryNotMatchDiffKeys() {
		$url = new Query( '/test?a=1', new Source_Flags() );
		$this->assertFalse( $url->is_match( '/test?b=1', new Source_Flags() ) );
	}

	public function testQueryMatchDiffOrder() {
		$url = new Query( '/this?a=1&b=2', new Source_Flags() );
		$this->assertTrue( $url->is_match( '/this?b=2&a=1', new Source_Flags() ) );
	}

	public function testQueryMatchInvalidParams() {
		$url = new Query( '/this?=2', new Source_Flags() );
		$this->assertTrue( $url->is_match( '/this?=2', new Source_Flags() ) );
	}

	public function testQueryNotMatchInvalidParams() {
		$url = new Query( '/this?=2', new Source_Flags() );
		$this->assertFalse( $url->is_match( '/this?=1', new Source_Flags() ) );
	}

	public function testQueryMatchZeroParam() {
		$url = new Query( '/this?cat=0', new Source_Flags() );
		$this->assertTrue( $url->is_match( '/this?cat=0', new Source_Flags() ) );
	}

	public function testQueryMatchIgnoreCase() {
		$url = new Query( '/this?cats=2', new Source_Flags() );
		$this->assertTrue( $url->is_match( '/this?CATS=2', new Source_Flags( [ 'flag_case' => true ] ) ) );
	}

	public function testQueryMatchIgnore() {
		$url = new Query( '', new Source_Flags() );
		$this->assertTrue( $url->is_match( '/this?b=2&a=1', new Source_Flags( [ 'flag_query' => 'ignore' ] ) ) );
	}

	public function testQueryMatchIgnoreDefault() {
		$url = new Query( '/?a=1', new Source_Flags() );
		$this->assertTrue( $url->is_match( '/this?b=2&a=1', new Source_Flags( [ 'flag_query' => 'ignore' ] ) ) );
	}

	public function testQueryMatchIgnorePass() {
		$url = new Query( '/?a=1', new Source_Flags() );
		$this->assertTrue( $url->is_match( '/this?b=2&a=1', new Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryMatchArray() {
		$url = new Query( '/?arrs1[]=1&arrs1[]=2&arrs1[]=3&arrs2[]=1&arrs2[]=2&arrs2[]=3', new Source_Flags() );
		$this->assertTrue( $url->is_match( '/this?arrs2[]=1&arrs2[]=2&arrs2[]=3&arrs1[]=1&arrs1[]=2&arrs1[]=3', new Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryNotMatchArray() {
		$url = new Query( '/?arrs1[]=1&arrs1[]=2&arrs1[]=3&arrs2[]=1&arrs2[]=2&arrs2[]=5', new Source_Flags() );
		$this->assertFalse( $url->is_match( '/this?arrs2[]=1&arrs2[]=2&arrs2[]=3&arrs1[]=1&arrs1[]=2&arrs1[]=3', new Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryNotMatchArrayExact() {
		$url = new Query( '/?arrs1[]=1&arrs1[]=2&arrs1[]=3', new Source_Flags() );
		$this->assertFalse( $url->is_match( '/this?arrs2[]=1&arrs2[]=2&arrs2[]=3&arrs1[]=1&arrs1[]=2&arrs1[]=3', new Source_Flags() ) );

		$url = new Query( '/?arrs1[]=1&arrs1[]=2&arrs1[]=3&arrs2=5', new Source_Flags() );
		$this->assertFalse( $url->is_match( '/this?arrs2[]=1&arrs2[]=2&arrs2[]=3&arrs1[]=1&arrs1[]=2&arrs1[]=3', new Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
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
		$this->assertEquals( '/cats', Query::add_to_target( '/cats', '/target', new Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryPassParams() {
		$this->assertEquals( '/cats?hey=there', Query::add_to_target( '/cats', '/target?hey=there', new Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryPassParamsInt() {
		$this->assertEquals( '/cats?1=there', Query::add_to_target( '/cats', '/target?1=there', new Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryPassParamsEncoding() {
		$this->assertEquals( '/cats?this=a+cat', Query::add_to_target( '/cats', '/target?this=a%20cat', new Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
		$this->assertEquals( '/cats?this=a+cat', Query::add_to_target( '/cats', '/target?this=a+cat', new Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryPassParamsWithDefaults() {
		$this->assertEquals( '/cats?cat=a&thing=cat', Query::add_to_target( '/cats?cat=a', '/target?thing=cat', new Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryPassParamsNoOverride() {
		$this->assertEquals( '/cats?cat=a', Query::add_to_target( '/cats?cat=a', '/target?cat=b', new Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryPassArray() {
		$this->assertEquals( '/?arrs1[]=1&arrs1[]=2&arrs1[]=3&arrs2[]=1&arrs2[]=2&arrs2[]=3', Query::add_to_target( '/', '/?arrs1[]=1&arrs1[]=2&arrs1[]=3&arrs2[]=1&arrs2[]=2&arrs2[]=3', new Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryPassCase() {
		$this->assertEquals( '/?this=THAt', Red_Url_Query::add_to_target( '/', '/?this=THAt', new Red_Source_Flags( [ 'flag_query' => 'pass', 'flag_case' => true ] ) ) );
	}

	public function testQueryPassNoValue() {
		$this->assertEquals( '/?this', Red_Url_Query::add_to_target( '/', '/?this', new Red_Source_Flags( [ 'flag_query' => 'pass', 'flag_case' => true ] ) ) );
	}

	public function testFragment() {
		$this->assertEquals( '/?arrs1[]=1&arrs1[]=2&stuff=1#fragment', Query::add_to_target( '/#fragment', '/?arrs1[]=1&arrs1[]=2&stuff=1', new Source_Flags( [ 'flag_query' => 'pass' ] ) ) );
	}

	public function testQueryDoubleSlash() {
		$url = new Query( '//?this=query&more', new Source_Flags() );
		$this->assertEquals( [
			'this' => 'query',
			'more' => '',
		], $url->get() );
	}

	public function testQueryUrl() {
		$url = new Query( '//page.html?page=http://domain.com:303/cats?this=query&more', new Source_Flags() );
		$this->assertEquals( [
			'page' => 'http://domain.com:303/cats?this=query',
			'more' => '',
		], $url->get() );
	}

	public function testEscapedQueryUrlBefore() {
		$url = new Query( '/page.html\\?page=http://domain.com:303/cats?this=query&more', new Source_Flags() );
		$this->assertEquals( [
			'page' => 'http://domain.com:303/cats?this=query',
			'more' => '',
		], $url->get() );
	}

	public function testEscapedQueryUrlAfter() {
		$url = new Query( '/page.html?page=http://domain.com:303/cats\\?this=query&more', new Source_Flags() );
		$this->assertEquals( [
			'page' => 'http://domain.com:303/cats\\?this=query',
			'more' => '',
		], $url->get() );
	}

	public function testQueryPhpArray() {
		$url = new Query( '/?thing=save&arrs1[]=1&arrs1[]=2&arrs1[]=3&arrs2[]=1&arrs2[]=2&arrs2[]=3', new Source_Flags() );
		$this->assertEquals( [
			'thing' => 'save',
			'arrs1' => [ 1, 2, 3 ],
			'arrs2' => [ 1, 2, 3 ],
		], $url->get() );
	}

	public function testArraySame() {
		$url = new Query( '', new Source_Flags() );
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
		$url = new Query( '', new Source_Flags() );
		$this->assertEquals( [ 'a' => [ '1' => '1', '2' => '2' ] ], $url->get_query_same( [ 'a' => [ '1' => '1', '2' => '2' ] ], [ 'a' => [ '1' => '1', '2' => '2' ] ], false ) );
		$this->assertEquals( [], $url->get_query_same( [ 'a' => [ '1' => '1', '2' => '2' ] ], [ 'a' => [ '1' => '1' ] ], false ) );
	}

	public function testArrayDiff() {
		$url = new Query( '', new Source_Flags() );
		$this->assertEquals( [], $url->get_query_diff( [], [] ) );
		$this->assertEquals( [], $url->get_query_diff( [ 'a' => 'a' ], [ 'a' => 'a' ] ) );
		$this->assertEquals( [ 'a' => 'a' ], $url->get_query_diff( [ 'a' => 'a' ], [] ) );
		$this->assertEquals( [ 'a' => 'a' ], $url->get_query_diff( [ 'a' => 'a' ], [ 'b' => 'b' ] ) );
	}

	public function testArrayDiffDeep() {
		$url = new Query( '', new Source_Flags() );
		$this->assertEquals( [ 'a' => [ 'a' => 'a' ] ], $url->get_query_diff( [ 'a' => [ 'a' => 'a' ] ], [] ) );
		$this->assertEquals( [], $url->get_query_diff( [ 'a' => [ 'a' => 'a' ] ], [ 'a' => [ 'a' => 'a' ] ] ) );
		$this->assertEquals( [ 'a' => [ 'a' => 'a' ] ], $url->get_query_diff( [ 'a' => [ 'a' => 'a' ] ], [ 'a' => [ 'a' => 'b' ] ] ) );
	}

	public function testQueryAfterNone() {
		$url = new Query( '', new Source_Flags() );
		$this->assertEquals( '', $url->get_query_after( '/no-query' ) );
	}

	public function testQueryAfterSingle() {
		$url = new Query( '', new Source_Flags() );
		$this->assertEquals( 'cat', $url->get_query_after( '/query?cat' ) );
	}

	public function testQueryAfterEscaped() {
		$url = new Query( '', new Source_Flags() );
		$this->assertEquals( 'cat', $url->get_query_after( '/query\\?cat' ) );
	}

	public function testQueryAfterBoth() {
		$url = new Query( '', new Source_Flags() );
		$this->assertEquals( 'dog\\?cat', $url->get_query_after( '/query?dog\\?cat' ) );

		$url = new Query( '', new Source_Flags() );
		$this->assertEquals( 'cat?cat', $url->get_query_after( '/query\\?cat?cat' ) );
	}
}
