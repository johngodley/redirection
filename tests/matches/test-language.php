<?php

require dirname( __FILE__ ) . '/../../matches/language.php';

class LanguageMatchTest extends WP_UnitTestCase {
	public function testNoData() {
		$match = new Match\Language();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'language' => '',
		);
		$this->assertEquals( $saved, $match->save( array( 'bad' => 'thing' ) ) );
	}

	public function testSanitize() {
		$match = new Match\Language();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'language' => 'abc',
		);
		$this->assertEquals( $saved, $match->save( array( 'language' => 'a b c' ) ) );
	}

	public function testLoadBad() {
		$match = new Match\Language();
		$match->load( serialize( array( 'url_from' => 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', 'url_notfrom' => 'yes', 'language' => '' ) ) );
		$this->assertEquals( 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', $match->url_from );
	}

	public function testNoMatch() {
		unset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] );

		$match = new Match\Language( serialize( array( 'language' => 'de,fr', 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
	}

	public function testMatch() {
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de,fr';

		$match = new Match\Language( serialize( array( 'language' => 'fr', 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertTrue( $match->is_match( '' ) );
	}
}
