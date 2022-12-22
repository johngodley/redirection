<?php

require dirname( __FILE__ ) . '/../../matches/ip.php';

class IPMatchTest extends WP_UnitTestCase {
	public function setUp() : void {
		remove_filter( 'redirection_request_ip', array( Redirection::init(), 'no_ip_logging' ) );
	}

	public function testNoData() {
		$match = new Ip_Match();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'ip' => [],
		);
		$this->assertEquals( $saved, $match->save( array( 'bad' => 'thing' ) ) );
	}

	public function testBadIp() {
		$match = new Ip_Match();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'ip' => [],
		);
		$this->assertEquals( $saved, $match->save( array( 'ip' => [ 'cats' ] ) ) );
	}

	public function testGoodIp() {
		$match = new Ip_Match();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'ip' => [ '192.168.1.1' ],
		);
		$this->assertEquals( $saved, $match->save( array( 'ip' => [ '192.168.1.1' ] ) ) );
	}

	public function testIgnoreBadIp() {
		$match = new Ip_Match();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'ip' => [ '192.168.1.1' ],
		);
		$this->assertEquals( $saved, $match->save( array( 'ip' => [ 'a', 'b', '192.168.1.1' ] ) ) );
	}

	public function testLoadBad() {
		$match = new Ip_Match();
		$match->load( serialize( array( 'url_from' => 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', 'url_notfrom' => 'yes', 'ip' => '' ) ) );
		$this->assertEquals( 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', $match->url_from );
	}

	public function testNoMatch() {
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';

		$match = new Ip_Match( serialize( array( 'ip' => [ '192.168.1.2' ], 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
	}

	public function testNoMatchNoIp() {
		unset( $_SERVER['REMOTE_ADDR'] );

		$match = new Ip_Match( serialize( array( 'ip' => [ '192.168.1.2' ], 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
	}

	public function testNoMatchMultiple() {
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';

		$match = new Ip_Match( serialize( array( 'ip' => [ '192.168.1.2', '192.168.1.3' ], 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
	}

	public function testMatch() {
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';

		$match = new Ip_Match( serialize( array( 'ip' => [ '192.168.1.1' ], 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertTrue( $match->is_match( '' ) );
	}

	public function testMatchMultiple() {
		$_SERVER['REMOTE_ADDR'] = '192.168.1.2';

		$match = new Ip_Match( serialize( array( 'ip' => [ '192.168.1.1', '192.168.1.2' ], 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertTrue( $match->is_match( '' ) );
	}
}
