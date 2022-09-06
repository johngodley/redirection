<?php

require dirname( __FILE__ ) . '/../../matches/server.php';

class ServerMatchTest extends WP_UnitTestCase {
	public function testNoData() {
		$match = new Match\Server();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'server' => '',
		);
		$this->assertEquals( $saved, $match->save( array( 'bad' => 'thing' ) ) );
	}

	public function testGuessProtocol() {
		$match = new Match\Server();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'server' => 'http://domain.com',
		);
		$this->assertEquals( $saved, $match->save( array( 'server' => 'domain.com' ) ) );
	}

	public function testHostOnly() {
		$match = new Match\Server();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'server' => 'http://domain.com',
		);
		$this->assertEquals( $saved, $match->save( array( 'server' => 'http://domain.com/something/?here' ) ) );
	}

	public function testBadServer() {
		$match = new Match\Server();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'server' => 'http://something',
		);
		$this->assertEquals( $saved, $match->save( array( 'server' => 'something' ) ) );
	}

	public function testValidServer() {
		$match = new Match\Server();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'server' => 'http://domain.com',
		);
		$this->assertEquals( $saved, $match->save( array( 'server' => 'http://domain.com' ) ) );
	}

	public function testLoadBad() {
		$match = new Match\Server();
		$match->load( serialize( array( 'url_from' => 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', 'url_notfrom' => 'yes', 'server' => '' ) ) );
		$this->assertEquals( 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', $match->url_from );
	}

	public function testNoMatch() {
		$_SERVER['SERVER_NAME'] = 'server.com';

		$match = new Match\Server( serialize( array( 'server' => 'other', 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
	}

	public function testNoMatchNoExist() {
		unset( $_SERVER['SERVER_NAME'] );

		$match = new Match\Server( serialize( array( 'server' => 'other', 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
	}

	public function testMatch() {
		$_SERVER['SERVER_NAME'] = 'server.com';

		$match = new Match\Server( serialize( array( 'server' => 'http://server.com', 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertTrue( $match->is_match( '' ) );
	}
}
