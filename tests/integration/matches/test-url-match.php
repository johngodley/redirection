<?php

class UrlMatchTest extends WP_UnitTestCase {
	public function testTargetSanitized() {
		$match = new URL_Match();
		$saved = '/some/url';

		$this->assertEquals( $match->save( array( 'url' => "/some/url\nsomethingelse1" ) ), '/some/url' );
		$this->assertEquals( $match->save( array( 'url' => "/some/url\rsomethingelse2" ) ), '/some/url' );
		$this->assertEquals( $match->save( array( 'url' => "/some/url\r\nsomethingelse3" ) ), '/some/url' );
	}

	public function testBadData() {
		$match = new URL_Match();
		$saved = 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}';
		$this->assertEquals( $saved, $match->save( array( 'url' => 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}' ) ) );
	}

	public function testLoadBad() {
		$match = new URL_Match();
		$match->load( 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}' );
		$this->assertEquals( 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', $match->url );
	}

	public function testDefaultSlash() {
		$match = new URL_Match();

		$this->assertEquals( $match->save( array() ), '/' );
	}

	public function testMatch() {
		$match = new URL_Match( '/something' );
		$this->assertTrue( $match->is_match( '' ) );
	}

	public function testMatchedTarget() {
		$match = new URL_Match( '/url' );
		$this->assertEquals( '/url', $match->get_target_url( '', '', new Red_Source_Flags(), true ) );
	}

	public function testRegexTarget() {
		$match = new URL_Match( '/url/$1' );
		$this->assertEquals( '/url/1', $match->get_target_url( '/category/1', '/category/(.*?)', new Red_Source_Flags( [ 'flag_regex' => true ] ), true ) );
	}

	/**
	 * Get the target for a regex redirect of `^/old/(.*)$` => `/$1`
	 *
	 * @param string $requested_url The URL being requested.
	 * @return string|false
	 */
	private function get_relative_regex_target( $requested_url ) {
		$match = new URL_Match( '/$1' );

		return $match->get_target_url( $requested_url, '^/old/(.*)$', new Red_Source_Flags( [ 'flag_regex' => true ] ), true );
	}

	public function testRegexTargetKeepsLeadingSlash() {
		$this->assertEquals( '/page', $this->get_relative_regex_target( '/old/page' ) );
	}

	public function testRegexTargetIsNotProtocolRelative() {
		$this->assertEquals( '/test.example/login', $this->get_relative_regex_target( '/old//test.example/login' ) );
	}

	public function testRegexTargetIsNotProtocolRelativeWithBackslash() {
		$this->assertEquals( '/test.example/login', $this->get_relative_regex_target( '/old/\\test.example/login' ) );
	}

	public function testRegexTargetIsNotProtocolRelativeWithControlCharacter() {
		$this->assertEquals( '/test.example/login', $this->get_relative_regex_target( "/old/\t/test.example/login" ) );
	}

	public function testRegexTargetAllowsExplicitProtocolRelative() {
		$match = new URL_Match( '//cdn.example/$1' );
		$target = $match->get_target_url( '/old/file.jpg', '^/old/(.*)$', new Red_Source_Flags( [ 'flag_regex' => true ] ), true );

		$this->assertEquals( '//cdn.example/file.jpg', $target );
	}

	public function testRegexTargetAllowsExplicitHost() {
		$match = new URL_Match( 'https://good.example/$1' );
		$target = $match->get_target_url( '/old//test.example/login', '^/old/(.*)$', new Red_Source_Flags( [ 'flag_regex' => true ] ), true );

		$this->assertEquals( 'https://good.example//test.example/login', $target );
	}
}
