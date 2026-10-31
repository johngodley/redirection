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
	 * @param string $requested_url The URL being requested.
	 * @param string $target_url Target URL.
	 * @param string $source_url Source URL.
	 * @return string|false
	 */
	private function get_regex_target( $requested_url, $target_url = '/$1', $source_url = '^/old/(.*)$' ) {
		$match = new URL_Match( $target_url );

		return $match->get_target_url( $requested_url, $source_url, new Red_Source_Flags( [ 'flag_regex' => true ] ), true );
	}

	public function testRegexTargetKeepsLeadingSlash() {
		$this->assertEquals( '/page', $this->get_regex_target( '/old/page' ) );
	}

	public function testRegexTargetIsNotProtocolRelative() {
		$this->assertEquals( '/test.example/login', $this->get_regex_target( '/old//test.example/login' ) );
	}

	public function testRegexTargetIsNotProtocolRelativeWithBackslash() {
		$this->assertEquals( '/test.example/login', $this->get_regex_target( '/old/\\test.example/login' ) );
	}

	public function testRegexTargetIsNotProtocolRelativeWithControlCharacter() {
		$this->assertEquals( '/test.example/login', $this->get_regex_target( "/old/\t/test.example/login" ) );
	}

	public function testRegexTargetIsCheckedAfterRedirectSanitizing() {
		$target = $this->get_regex_target( '/old/"/test.example/login' );

		$this->assertEquals( '/test.example/login', wp_sanitize_redirect( $target ) );
	}

	public function testCapturedRegexTargetIsNotProtocolRelative() {
		$this->assertEquals( '/test.example/login', $this->get_regex_target( '/old///test.example/login', '$1' ) );
	}

	public function testCapturedRegexTargetIsNotAbsolute() {
		$this->assertEquals( '/https://test.example/login', $this->get_regex_target( '/old/https://test.example/login', '$1' ) );
	}

	public function testRegexTargetAllowsNonSpecialScheme() {
		$this->assertEquals( 'mailto:user@example.com', $this->get_regex_target( '/old/page', 'mailto:user@example.com' ) );
	}

	public function testCapturedRegexTargetSupportsBracedReference() {
		$this->assertEquals( '/test.example/login', $this->get_regex_target( '/old///test.example/login', '${1}' ) );
	}

	public function testCapturedRegexTargetSupportsBackslashReference() {
		$this->assertEquals( '/test.example/login', $this->get_regex_target( '/old///test.example/login', '\\1' ) );
	}

	public function testRegexTargetIsCheckedAfterRedirectSanitizingRemovesCharacters() {
		foreach ( [ '"', '|', '<', '>', '^', "'", '{', '}', '$', '%0a', '%0d', '%0A', '%0D' ] as $character ) {
			$target = $this->get_regex_target( '/old/' . $character . '/test.example/login' );

			$this->assertEquals( '/test.example/login', wp_sanitize_redirect( $target ), $character );
		}
	}

	public function testCapturedRegexTargetIsCheckedAfterRedirectSanitizing() {
		$target = $this->get_regex_target( '/old/"//test.example/login', '$1' );

		$this->assertEquals( '/test.example/login', wp_sanitize_redirect( $target ) );
	}

	public function testMultipleCapturedRegexTargetIsNotProtocolRelative() {
		$this->assertEquals( '/test.example/login', $this->get_regex_target( '/old///test.example/login', '$1$2', '^/old/(/)(.*)$' ) );
	}

	public function testCapturedRegexTargetCannotSetScheme() {
		$targets = [
			'javascript:alert(1)' => '/javascript:alert(1)',
			'JaVaScRiPt:alert(1)' => '/JaVaScRiPt:alert(1)',
			'"javascript:alert(1)' => '/javascript:alert(1)',
			'data:text/html,test' => '/data:text/html,test',
			'https:test.example' => '/https:test.example',
			'https:\\\\test.example' => '/https:test.example',
		];

		foreach ( $targets as $captured => $expected ) {
			$this->assertEquals( $expected, $this->get_regex_target( '/old/' . $captured, '$1' ), $captured );
		}
	}

	public function testRegexTargetWithManySlashesIsNotProtocolRelative() {
		$this->assertEquals( '/test.example/login', $this->get_regex_target( '/old/////test.example/login' ) );
		$this->assertEquals( '/test.example/login', $this->get_regex_target( '/old/\\/\\test.example/login' ) );
	}

	public function testRegexTargetWithLookalikeSlashIsEncoded() {
		foreach ( [ "\u{FF0F}", "\u{2215}", "\u{2044}" ] as $slash ) {
			$target = wp_sanitize_redirect( $this->get_regex_target( '/old/' . $slash . $slash . 'test.example/login' ) );

			$this->assertStringStartsWith( '/%', $target );
		}
	}

	public function testRegexTargetSanitizingCannotSetHost() {
		$target = $this->get_regex_target( '/old//test.example/login', '|/$1' );

		$this->assertEquals( '/test.example/login', wp_sanitize_redirect( $target ) );
	}

	public function testRegexTargetAllowsRelativePath() {
		$this->assertEquals( 'folder/page', $this->get_regex_target( '/old/page', 'folder/$1' ) );
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

	public function testRegexTargetAllowsCaptureAfterHost() {
		$this->assertEquals( 'https://good.example/page', $this->get_regex_target( '/old/page', 'https://good.example$1', '^/old(/.*)$' ) );
	}

	public function testRegexTargetAllowsEmptyCaptureAfterHost() {
		$this->assertEquals( 'https://good.example', $this->get_regex_target( '/old', 'https://good.example$1', '^/old(.*)$' ) );
	}

	public function testRegexTargetKeepsHostWithoutSlashes() {
		$targets = [
			'https:good.example$1' => 'https:good.example/@test.example',
			'https:/good.example$1' => 'https:/good.example/@test.example',
			'https:\\\\good.example$1' => 'https:\\good.example/@test.example',
			'\\\\good.example$1' => '\\good.example/@test.example',
			'HTTP:good.example$1' => 'HTTP:good.example/@test.example',
			'myapp://good.example$1' => 'myapp://good.example/@test.example',
			'///good.example$1' => '///good.example/@test.example',
		];

		foreach ( $targets as $target => $expected ) {
			$this->assertEquals( $expected, $this->get_regex_target( '/old@test.example', $target, '^/old(.*)$' ), $target );
		}
	}

	public function testRegexTargetWithNonSpecialSchemeIsUnchanged() {
		$this->assertEquals( 'mailto:user@example.com', $this->get_regex_target( '/old@example.com', 'mailto:user$1', '^/old(.*)$' ) );
	}
}
