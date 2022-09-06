<?php

use Redirection\Group;
use Redirection\Redirect;

class RedirectSanitizeTest extends WP_UnitTestCase {
	private function get_new( array $extra = array() ) {
		return array_merge( [
			'url' => '/a',
			'group_id' => $this->group->get_id(),
			'match_type' => 'url',
			'action_type' => 'url',
		], $extra );
	}

	public function setUp() : void {
		$this->sanitizer = new Redirect\Sanitize();
		$this->group = Group\Group::create( 'group', 1 );
	}

	public function testRemoveHttp() {
		$this->assertEquals( '/some/url', $this->sanitizer->sanitize_url( 'http://domain.com/some/url' ) );
		$this->assertEquals( '/some/url', $this->sanitizer->sanitize_url( 'https://domain.com/some/url' ) );
	}

	public function testRemoveNewline() {
		$this->assertEquals( '/some/url', $this->sanitizer->sanitize_url( "/some/url\nsomethingelse1" ) );
		$this->assertEquals( '/some/url', $this->sanitizer->sanitize_url( "/some/url\rsomethingelse2" ) );
		$this->assertEquals( '/some/url', $this->sanitizer->sanitize_url( "/some/url\r\nsomethingelse3" ) );
	}

	public function testAddLeadingSlash() {
		$this->assertEquals( '/some/url', $this->sanitizer->sanitize_url( 'some/url' ) );
	}

	public function testDecodePath() {
		$this->assertEquals( '/中国/thing?other=中国', $this->sanitizer->sanitize_url( '/%E4%B8%AD%E5%9B%BD/thing?other=%E4%B8%AD%E5%9B%BD' ) );
	}

	// Note this also checks a good URL, good match, good group, and good action
	public function testTrim() {
		$result = $this->sanitizer->get( $this->get_new( array( 'url' => ' /spaces   ' ) ) );
		$this->assertEquals( '/spaces', $result['url'] );
	}

	public function testStripSlashes() {
		$result = $this->sanitizer->get( $this->get_new( array( 'url' => '/spaces\'' ) ) );
		$this->assertEquals( "/spaces'", $result['url'] );
	}

	public function testPlusChar() {
		$result = $this->sanitizer->get( $this->get_new( array( 'url' => '/test(.+)' ) ) );
		$this->assertEquals( '/test(.+)', $result['url'] );
	}

	public function testBadRegex() {
		$result = $this->sanitizer->get( $this->get_new( array( 'regex' => 'cat' ) ) );
		$this->assertEquals( 0, $result['regex'] );
	}

	public function testGoodRegex() {
		$result = $this->sanitizer->get( $this->get_new( array( 'regex' => true ) ) );
		$this->assertEquals( 1, $result['regex'] );
	}

	public function testNoTitleNull() {
		$result = $this->sanitizer->get( $this->get_new( array() ) );
		$this->assertNull( $result['title'] );
	}

	public function testTitle() {
		$result = $this->sanitizer->get( $this->get_new( array( 'title' => 'title' ) ) );
		$this->assertEquals( 'title', $result['title'] );
	}

	public function testLongTitle() {
		$title = str_repeat( 'a', 501 );
		$result = $this->sanitizer->get( $this->get_new( array( 'title' => $title ) ) );
		$this->assertEquals( substr( $title, 0, 500 ), $result['title'] );
	}

	public function testEmptyUrl() {
		$result = $this->sanitizer->get( $this->get_new( array( 'url' => '' ) ) );
		$this->assertEquals( '/', $result['url'] );
	}

	public function testEmptyUrlRegex() {
		$result = $this->sanitizer->get( $this->get_new( array( 'url' => '', 'regex' => true ) ) );
		$this->assertWPError( $result );
	}

	public function testUrl() {
		$result = $this->sanitizer->get( $this->get_new() );
		$this->assertEquals( '/a', $result['url'] );
	}

	public function testBadGroup() {
		$result = $this->sanitizer->get( $this->get_new( array( 'group_id' => 'cat' ) ) );
		$this->assertWPError( $result );
	}

	public function testBadMatch() {
		$result = $this->sanitizer->get( $this->get_new( array( 'match_type' => 'cat' ) ) );
		$this->assertWPError( $result );

		$result = $this->sanitizer->get( $this->get_new( array( 'match_type' => '' ) ) );
		$this->assertWPError( $result );
	}

	public function testBadRedirectCode() {
		$result = $this->sanitizer->get( $this->get_new( array( 'action_code' => 'cat' ) ) );
		$this->assertEquals( 301, $result['action_code'] );

		$result = $this->sanitizer->get( $this->get_new( array( 'action_code' => '404' ) ) );
		$this->assertEquals( 301, $result['action_code'] );
	}

	public function testGoodRedirectCode() {
		$result = $this->sanitizer->get( $this->get_new( array( 'action_code' => '301' ) ) );
		$this->assertEquals( 301, $result['action_code'] );
	}

	public function testBadErrorCode() {
		$result = $this->sanitizer->get( $this->get_new( array( 'action_code' => '301', 'action_type' => 'error' ) ) );
		$this->assertEquals( 404, $result['action_code'] );
	}

	public function testGoodErrorCode() {
		$result = $this->sanitizer->get( $this->get_new( array( 'action_code' => '404', 'action_type' => 'error' ) ) );
		$this->assertEquals( 404, $result['action_code'] );
	}

	public function testBadActionTYPE() {
		$result = $this->sanitizer->get( $this->get_new( array( 'action_type' => 'cats' ) ) );
		$this->assertWPError( $result );
	}

	public function testUnserializeData() {
		$result = $this->sanitizer->get( $this->get_new( array( 'action_data' => array( 'url' => '/a' ) ) ) );
		$this->assertEquals( '/a', $result['action_data'] );
	}

	public function testStripSlasheserializeData() {
		$result = $this->sanitizer->get( $this->get_new( [
			'match_type' => 'login',
			'action_data' => [
				'logged_in' => '',
				'logged_out' => '',
			],
		] ) );
		$this->assertEquals( serialize( [
			'logged_in' => '',
			'logged_out' => '',
		] ), $result['action_data'] );
	}

	public function testBadPosition() {
		$result = $this->sanitizer->get( $this->get_new( array( 'position' => 'cat' ) ) );
		$this->assertEquals( 0, $result['position'] );

		$result = $this->sanitizer->get( $this->get_new( array( 'position' => -1 ) ) );
		$this->assertEquals( 0, $result['position'] );
	}

	public function testGoodPosition() {
		$result = $this->sanitizer->get( $this->get_new( array( 'position' => 6 ) ) );
		$this->assertEquals( 6, $result['position'] );
	}

	public function testAbsoluteUrl() {
		$result = $this->sanitizer->get( $this->get_new( array( 'url' => 'http://full.com' ) ) );

		$this->assertEquals( 'server', $result['match_type'] );
		$this->assertEquals( '/', $result['url'] );
		$this->assertEquals( 'http://full.com', unserialize( $result['action_data'] )['server'] );
	}

	public function testSourceFlags() {
		$flags = [ 'source' => [ 'flag_case' => true ] ];
		$result = $this->sanitizer->get( $this->get_new( [ 'match_data' => $flags ] ) );

		$this->assertEquals( $flags['source']['flag_case'], $result['match_data']['source']['flag_case'] );
	}

	public function testSourceFlagsRegexOverride() {
		$flags = [ 'regex' => true ];
		$result = $this->sanitizer->get( $this->get_new( $flags ) );

		$this->assertEquals( 1, $result['regex'] );
		$this->assertEquals( 'regex', $result['match_url'] );
		$this->assertTrue( $result['match_data']['source']['flag_regex'] );
	}

	public function testMatchUrlSet() {
		$result = $this->sanitizer->get( $this->get_new( [ 'url' => '/TEST/?thing=1' ] ) );

		$this->assertEquals( '/test', $result['match_url'] );
	}

	public function testMatchUrlSanitize() {
		$result = $this->sanitizer->get( $this->get_new( [ 'url' => 'url' ] ) );

		$this->assertEquals( '/url', $result['match_url'] );
		$this->assertEquals( '/url', $result['url'] );
	}

	public function testMatchUrlSetRegex() {
		$result = $this->sanitizer->get( $this->get_new( [ 'url' => '/test', 'regex' => true ] ) );

		$this->assertEquals( 'regex', $result['match_url'] );
	}

	public function testRegexFlagsSetColumn() {
		\Redirection\Plugin\Settings\red_set_options( [ 'flag_case' => false, 'flag_regex' => false, 'flag_query' => 'exact', 'flag_trailing' => false ] );
		$result = $this->sanitizer->get( $this->get_new( [ 'url' => '/test', 'match_data' => [ 'source' => [ 'flag_regex' => true ] ] ] ) );

		$this->assertEquals( 'regex', $result['match_url'] );
		$this->assertEquals( 1, $result['regex'] );
	}

	public function testGetJsonDefaultSame() {
		\Redirection\Plugin\Settings\red_set_options( [ 'flag_case' => true, 'flag_regex' => false, 'flag_query' => 'exact', 'flag_trailing' => false ] );

		$flags = [ 'source' => [ 'flag_case' => true, 'flag_regex' => false, 'flag_query' => 'exact', 'flag_trailing' => false ] ];
		$result = $this->sanitizer->get( $this->get_new( [ 'match_data' => $flags ] ) );
		$this->assertTrue( ! isset( $result['match_data'] ) );
	}

	public function testGetJsonDefaultDifferent() {
		\Redirection\Plugin\Settings\red_set_options( [ 'flag_case' => false, 'flag_regex' => false, 'flag_query' => 'exact', 'flag_trailing' => false ] );

		$flags = [ 'source' => [ 'flag_case' => true, 'flag_query' => 'ignore', 'flag_trailing' => true ] ];
		$result = $this->sanitizer->get( $this->get_new( [ 'match_data' => $flags ] ) );

		$this->assertEquals( $flags['source'], $result['match_data']['source'] );
	}
}
