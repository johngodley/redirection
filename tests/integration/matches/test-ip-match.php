<?php

if ( ! defined( 'REDIRECTION_REFACTOR' ) || ! REDIRECTION_REFACTOR ) {
	require_once PLUGIN_PATH . '/matches/ip.php';
}

class IPMatchTest extends WP_UnitTestCase {
	private $remote_addr = null;

	public function setUp() : void {
		parent::setUp();

		$this->remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : null;
	}

	public function tearDown() : void {
		if ( $this->remote_addr === null ) {
			unset( $_SERVER['REMOTE_ADDR'] );
		} else {
			$_SERVER['REMOTE_ADDR'] = $this->remote_addr;
		}

		$front = Redirection::init();
		remove_filter( 'redirection_log_ip', array( $front, 'no_ip_logging' ) );
		remove_filter( 'redirection_log_ip', array( $front, 'mask_ip' ) );

		parent::tearDown();
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

	/**
	 * Log privacy settings are for logging, and must not affect matching.
	 */
	public function testMatchWithNoIpLogging() {
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';
		add_filter( 'redirection_log_ip', array( Redirection::init(), 'no_ip_logging' ) );

		$match = new Ip_Match( serialize( array( 'ip' => [ '192.168.1.1' ], 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertTrue( $match->is_match( '' ) );
	}

	public function testMatchWithMaskedIpLogging() {
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';
		add_filter( 'redirection_log_ip', array( Redirection::init(), 'mask_ip' ) );

		$match = new Ip_Match( serialize( array( 'ip' => [ '192.168.1.1' ], 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertTrue( $match->is_match( '' ) );
	}

	public function testNoMatchWithMaskedIpLogging() {
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';
		add_filter( 'redirection_log_ip', array( Redirection::init(), 'mask_ip' ) );

		// The masked IP must not be used to match, so this masked form does not match.
		$match = new Ip_Match( serialize( array( 'ip' => [ '192.168.1.0' ], 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
	}

	public function testMatchIpv6WithMaskedIpLogging() {
		$_SERVER['REMOTE_ADDR'] = '2001:db8:85a3:10:10:8a2e:370:7334';
		add_filter( 'redirection_log_ip', array( Redirection::init(), 'mask_ip' ) );

		$match = new Ip_Match( serialize( array( 'ip' => [ '2001:db8:85a3:10:10:8a2e:370:7334' ], 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertTrue( $match->is_match( '' ) );
	}
}
