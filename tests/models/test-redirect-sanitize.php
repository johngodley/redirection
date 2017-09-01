<?php

class RedirectSanitizeTest extends WP_UnitTestCase {
	private function get_new( array $extra = array() ) {
		return array_merge( array( 'url' => '/a', 'group_id' => $this->group->get_id(), 'match_type' => 'url', 'action_type' => 'url' ), $extra );
	}

	public function setUp() {
		$this->sanitizer = new Red_Item_Sanitize();
		$this->group = Red_Group::create( 'group', 1 );
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

	// Note this also checks a good URL, good match, good group, and good action
	public function testTrim() {
		$result = $this->sanitizer->get( $this->get_new( array( 'url' => ' /spaces   ' ) ) );
		$this->assertEquals( '/spaces', $result['url'] );
	}

	public function testStripSlashes() {
		$result = $this->sanitizer->get( $this->get_new( array( 'url' => '/spaces\\\'' ) ) );
		$this->assertEquals( "/spaces'", $result['url'] );
	}

	public function testBadRegex() {
		$result = $this->sanitizer->get( $this->get_new( array( 'regex' => 'cat' ) ) );
		$this->assertEquals( 0, $result['regex'] );
	}

	public function testGoodRegex() {
		$result = $this->sanitizer->get( $this->get_new( array( 'regex' => 'true' ) ) );
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

	public function testBadUrl() {
		$result = $this->sanitizer->get( $this->get_new( array( 'url' => '' ) ) );
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
		$this->assertEquals( 0, $result['action_code'] );

		$result = $this->sanitizer->get( $this->get_new( array( 'action_code' => '404' ) ) );
		$this->assertEquals( 0, $result['action_code'] );
	}

	public function testGoodRedirectCode() {
		$result = $this->sanitizer->get( $this->get_new( array( 'action_code' => '301' ) ) );
		$this->assertEquals( 301, $result['action_code'] );
	}

	public function testBadErrorCode() {
		$result = $this->sanitizer->get( $this->get_new( array( 'action_code' => '301', 'action_type' => 'error' ) ) );
		$this->assertEquals( 0, $result['action_code'] );
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
		$result = $this->sanitizer->get( $this->get_new( array( 'action_data' => '/a' ) ) );
		$this->assertEquals( '/a', $result['action_data'] );
	}

	public function testStripSlasheserializeData() {
		$result = $this->sanitizer->get( $this->get_new( array( 'match_type' => 'login' ) ) );
		$this->assertEquals( serialize( array( 'logged_in' => '', 'logged_out' => '' ) ), $result['action_data'] );
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
}
