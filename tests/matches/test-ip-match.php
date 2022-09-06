<?php

use Redirection\Match;
use Redirection\Front;

require dirname( __FILE__ ) . '/../../includes/match/match-ip.php';

class IPMatchTest extends WP_UnitTestCase {
	public function setUp() : void {
		remove_filter( 'redirection_request_ip', array( Front\Redirection::init(), 'no_ip_logging' ) );
	}

	public function testNoData() {
		$match = new Match\Ip();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'ip' => [],
		);
		$this->assertEquals( $saved, $match->save( array( 'bad' => 'thing' ) ) );
	}

	public function testBadIp() {
		$match = new Match\Ip();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'ip' => [],
		);
		$this->assertEquals( $saved, $match->save( array( 'ip' => [ 'cats' ] ) ) );
	}

	public function testGoodIp() {
		$match = new Match\Ip();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'ip' => [ '192.168.1.1' ],
		);
		$this->assertEquals( $saved, $match->save( array( 'ip' => [ '192.168.1.1' ] ) ) );
	}

	public function testIgnoreBadIp() {
		$match = new Match\Ip();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'ip' => [ '192.168.1.1' ],
		);
		$this->assertEquals( $saved, $match->save( array( 'ip' => [ 'a', 'b', '192.168.1.1' ] ) ) );
	}

	public function testLoadBad() {
		$match = new Match\Ip();
		$match->load( serialize( array( 'url_from' => 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', 'url_notfrom' => 'yes', 'ip' => '' ) ) );
		$this->assertEquals( 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', $match->url_from );
	}

	public function testNoMatch() {
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';

		$match = new Match\Ip( serialize( array( 'ip' => [ '192.168.1.2' ], 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
	}

	public function testNoMatchNoIp() {
		unset( $_SERVER['REMOTE_ADDR'] );

		$match = new Match\Ip( serialize( array( 'ip' => [ '192.168.1.2' ], 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
	}

	public function testNoMatchMultiple() {
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';

		$match = new Match\Ip( serialize( array( 'ip' => [ '192.168.1.2', '192.168.1.3' ], 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
	}

	public function testMatch() {
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';

		$match = new Match\Ip( serialize( array( 'ip' => [ '192.168.1.1' ], 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertTrue( $match->is_match( '' ) );
	}

	public function testMatchMultiple() {
		$_SERVER['REMOTE_ADDR'] = '192.168.1.2';

		$match = new Match\Ip( serialize( array( 'ip' => [ '192.168.1.1', '192.168.1.2' ], 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertTrue( $match->is_match( '' ) );
	}
}
