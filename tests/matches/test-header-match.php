<?php

require_once dirname( __FILE__ ) . '/../../matches/http-header.php';

class HeaderMatchTest extends WP_UnitTestCase {
	public function testTargetSanitized() {
		$match = new Header_Match();
		$saved = array(
			'url_from' => '/some/url',
			'url_notfrom' => '/some/url',
			'regex' => false,
			'name' => "thisisits-_",
			'value' => 'value',
		);
		$source = array(
			'url_from' => "/some/url\nsomethingelse1",
			'url_notfrom' => "/some/url\nsomethingelse2",
			'name' => 'this is it@s-_',
			'value' => 'value',
		);

		$this->assertEquals( $saved, $match->save( $source ) );
	}

	public function testBadData() {
		$match = new Header_Match();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'regex' => false,
			'name' => '',
			'value' => '',
		);
		$this->assertEquals( $saved, $match->save( array( 'bad' => 'thing' ) ) );
	}

	public function testLoadBad() {
		$match = new Header_Match();
		$match->load( serialize( array( 'url_from' => 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', 'url_notfrom' => 'yes', 'regex' => true, 'name' => '', 'value' => '' ) ) );
		$this->assertEquals( 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', $match->url_from );
	}

	public function testNoMatch() {
		$_SERVER['HTTP_THING'] = 'nothing';

		$match = new Header_Match( serialize( array( 'name' => 'thing', 'value' => 'other', 'regex' => false, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
		unset( $_SERVER['HTTP_THING'] );
	}

	public function testNoMatchNotExists() {
		$match = new Header_Match( serialize( array( 'name' => 'thing', 'value' => 'other', 'regex' => false, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
	}

	public function testMatch() {
		$_SERVER['HTTP_THING'] = 'nothing';

		$match = new Header_Match( serialize( array( 'name' => 'thing', 'value' => 'nothing', 'regex' => false, 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertTrue( $match->is_match( '' ) );
		unset( $_SERVER['HTTP_THING'] );
	}

	public function testMatchRegex() {
		$_SERVER['HTTP_THING'] = 'cat';

		$match = new Header_Match( serialize( array( 'name' => 'thing', 'value' => 'nothing|other|cat', 'regex' => true, 'url_from' => '/from', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertTrue( $match->is_match( '' ) );
		unset( $_SERVER['HTTP_THING'] );
	}
}
