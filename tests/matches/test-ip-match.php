<?php

require dirname( __FILE__ ) . '/../../matches/ip.php';

class IPMatchTest extends WP_UnitTestCase {
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

	public function testLoadBad() {
		$match = new Ip_Match();
		$match->load( serialize( array( 'url_from' => 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', 'url_notfrom' => 'yes', 'ip' => '' ) ) );
		$this->assertEquals( 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', $match->url_from );
	}

	public function testNoTargetNoUrl() {
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';

		$match = new Ip_Match( serialize( array( 'ip' => [ '192.168.1.2' ], 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertEquals( false, $match->get_target( 'a', 'b', new Red_Source_Flags() ) );
	}

	public function testRegexNoTargetNoUrl() {
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';

		$match = new Ip_Match( serialize( array( 'ip' => [ '192.168.1.2' ], 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertEquals( false, $match->get_target( 'a', 'b', new Red_Source_Flags( [ 'flag_regex' => true ] ) ) );
	}

	public function testNoTargetUrl() {
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';

		$match = new Ip_Match( serialize( array( 'ip' => [ '192.168.1.1' ], 'url_from' => '', 'url_notfrom' => '' ) ) );
		$this->assertEquals( false, $match->get_target( 'a', 'b', new Red_Source_Flags( [ 'flag_regex' => true ] ) ) );
	}

	public function testNoTargetNotFrom() {
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';

		$match = new Ip_Match( serialize( array( 'ip' => [ '192.168.1.2' ], 'url_from' => '/from', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertEquals( '/notfrom', $match->get_target( 'a', 'b', new Red_Source_Flags() ) );
	}

	public function testNoTargetFrom() {
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';

		$match = new Ip_Match( serialize( array( 'ip' => [ '192.168.1.1' ], 'url_from' => '/from', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertEquals( '/from', $match->get_target( 'a', 'b', new Red_Source_Flags() ) );
	}
}
