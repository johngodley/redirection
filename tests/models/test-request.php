<?php

class RequestTest extends WP_UnitTestCase {
	private $ip = false;

	private function monitorAction( $hook ) {
		$action = new MockAction();

		add_action( $hook, array( $action, 'action' ), 10, 2 );

		return $action;
	}

	private function getActionData( $action ) {
		$data = $action->get_args();

		return $data[0][0];
	}

	public function testNoRequestUri() {
		$action = $this->monitorAction( 'redirection_request_url' );
		unset( $_SERVER['REQUEST_URI'] );

		$result = Redirection_Request::get_request_url();

		$this->assertEquals( '', $result );
		$this->assertEquals( 1, $action->get_call_count() );
		$this->assertEquals( '', $this->getActionData( $action ) );
	}

	public function testGoodRequestUri() {
		$action = $this->monitorAction( 'redirection_request_url' );
		$_SERVER['REQUEST_URI'] = 'test';

		$result = Redirection_Request::get_request_url();

		$this->assertEquals( 'test', $result );
		$this->assertEquals( 1, $action->get_call_count() );
		$this->assertEquals( 'test', $this->getActionData( $action ) );
	}

	public function testNoUserAgent() {
		$action = $this->monitorAction( 'redirection_request_agent' );
		unset( $_SERVER['HTTP_USER_AGENT'] );

		$result = Redirection_Request::get_user_agent();

		$this->assertEquals( '', $result );
		$this->assertEquals( 1, $action->get_call_count() );
		$this->assertEquals( '', $this->getActionData( $action ) );
	}

	public function testGoodUserAgent() {
		$action = $this->monitorAction( 'redirection_request_agent' );
		$_SERVER['HTTP_USER_AGENT'] = 'user agent';

		$result = Redirection_Request::get_user_agent();

		$this->assertEquals( 'user agent', $result );
		$this->assertEquals( 1, $action->get_call_count() );
		$this->assertEquals( 'user agent', $this->getActionData( $action ) );
	}

	public function testNoReferrer() {
		$action = $this->monitorAction( 'redirection_request_referrer' );
		unset( $_SERVER['HTTP_REFERER'] );

		$result = Redirection_Request::get_referrer();

		$this->assertEquals( '', $result );
		$this->assertEquals( 1, $action->get_call_count() );
		$this->assertEquals( '', $this->getActionData( $action ) );
	}

	public function testGoodReferrer() {
		$action = $this->monitorAction( 'redirection_request_referrer' );
		$_SERVER['HTTP_REFERER'] = 'referrer';

		$result = Redirection_Request::get_referrer();

		$this->assertEquals( 'referrer', $result );
		$this->assertEquals( 1, $action->get_call_count() );
		$this->assertEquals( 'referrer', $this->getActionData( $action ) );
	}

	public function monitorRequestIP() {
		$this->ip = false;
		add_filter( 'redirection_request_ip', array( $this, 'do_ip_filter' ) );
	}

	public function removeMonitorRequestIP() {
		remove_filter( 'redirection_request_ip', array( $this, 'do_ip_filter' ) );
	}

	public function do_ip_filter( $ip ) {
		$this->ip = $ip;
		return $ip;
	}

	public function testNoIP() {
		$this->monitorRequestIP();

		$_SERVER['REMOTE_ADDR'] = 'something';

		$result = Redirection_Request::get_ip();

		$this->assertEquals( '', $result );
		$this->assertEquals( '', $this->ip );

		$this->removeMonitorRequestIP();
	}

	public function testInvalidIP() {
		$this->monitorRequestIP();

		unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		unset( $_SERVER['REMOTE_ADDR'] );

		$result = Redirection_Request::get_ip();

		$this->assertEquals( '', $result );
		$this->assertEquals( '', $this->ip );

		$this->removeMonitorRequestIP();
	}

	public function testMultipleForwardedIP() {
		$this->monitorRequestIP();

		$_SERVER['HTTP_X_FORWARDED_FOR'] = ' 192.1.1.1, 192.1.1.2, 192.1.2.3';
		$_SERVER['REMOTE_ADDR'] = '192.1.1.2';

		$result = Redirection_Request::get_ip();

		$this->assertEquals( '192.1.1.1', $result );
		$this->assertEquals( '192.1.1.1', $this->ip );

		$this->removeMonitorRequestIP();
	}

	public function testPreferForwardedIP() {
		$this->monitorRequestIP();

		$_SERVER['HTTP_X_FORWARDED_FOR'] = '192.1.1.1';
		$_SERVER['REMOTE_ADDR'] = '192.1.1.2';

		$result = Redirection_Request::get_ip();

		$this->assertEquals( '192.1.1.1', $result );
		$this->assertEquals( '192.1.1.1', $this->ip );

		$this->removeMonitorRequestIP();
	}

	public function testDefaultHostIP() {
		$this->monitorRequestIP();

		unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		$_SERVER['REMOTE_ADDR'] = '192.1.1.1';

		$result = Redirection_Request::get_ip();

		$this->assertEquals( '192.1.1.1', $result );
		$this->assertEquals( '192.1.1.1', $this->ip );

		$this->removeMonitorRequestIP();
	}

	public function testCloudfareIP() {
		$this->monitorRequestIP();

		unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		unset( $_SERVER['REMOTE_ADDR'] );
		$_SERVER['HTTP_CF_CONNECTING_IP'] = '192.1.1.3';

		$result = Redirection_Request::get_ip();

		$this->assertEquals( '192.1.1.3', $result );
		$this->assertEquals( '192.1.1.3', $this->ip );

		$this->removeMonitorRequestIP();
	}

	public function testBadIP4() {
		$this->monitorRequestIP();

		unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		unset( $_SERVER['REMOTE_ADDR'] );
		$_SERVER['HTTP_CF_CONNECTING_IP'] = 'cat';

		$result = Redirection_Request::get_ip();

		$this->assertEquals( '', $result );
		$this->assertEquals( '', $this->ip );

		$this->removeMonitorRequestIP();
	}

	public function testIP6() {
		$this->monitorRequestIP();

		unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		unset( $_SERVER['HTTP_CF_CONNECTING_IP'] );
		$_SERVER['REMOTE_ADDR'] = '2001:db8:85a3:10:10:8a2e:370:7334';

		$result = Redirection_Request::get_ip();

		$this->assertEquals( '2001:db8:85a3:10:10:8a2e:370:7334', $result );
		$this->assertEquals( '2001:db8:85a3:10:10:8a2e:370:7334', $this->ip );

		$this->removeMonitorRequestIP();
	}

	public function testBadIP6() {
		$this->monitorRequestIP();

		unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		unset( $_SERVER['HTTP_CF_CONNECTING_IP'] );
		$_SERVER['REMOTE_ADDR'] = '2001gfdgdfcat:db8:85a3:10:10:8a2e:370:7334';

		$result = Redirection_Request::get_ip();

		$this->assertEquals( '', $result );
		$this->assertEquals( '', $this->ip );

		$this->removeMonitorRequestIP();
	}

	public function testNoIPLogging() {
		add_filter( 'redirection_request_ip', array( Redirection::init(), 'no_ip_logging' ) );;
		red_set_options( array( 'ip_logging' => 0 ) );

		unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		unset( $_SERVER['REMOTE_ADDR'] );
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';

		$result = Redirection_Request::get_ip();
		$this->assertEquals( '', $result );
	}

	public function testMaskIP4() {
		add_filter( 'redirection_request_ip', array( Redirection::init(), 'mask_ip' ) );;
		red_set_options( array( 'ip_logging' => 2 ) );

		unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		unset( $_SERVER['REMOTE_ADDR'] );
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';

		$result = Redirection_Request::get_ip();
		$this->assertEquals( '192.168.1.0', $result );
	}

	public function testMaskIP6() {
		add_filter( 'redirection_request_ip', array( Redirection::init(), 'mask_ip' ) );;
		red_set_options( array( 'ip_logging' => 2 ) );

		unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		unset( $_SERVER['REMOTE_ADDR'] );
		$_SERVER['REMOTE_ADDR'] = '2001:db8:85a3:10:10:8a2e:370:7334';

		$result = Redirection_Request::get_ip();
		$this->assertEquals( '2000:420:22:0:10:226:260:3224', $result );
	}
}
