<?php

class RequestTest extends WP_UnitTestCase {
	private $ip = false;

	private function resetIpSettings() {
		unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		unset( $_SERVER['REMOTE_ADDR'] );
		red_set_options( [ 'ip_headers' => [] ] );
	}

	private function allowIpFrom( $header ) {
		red_set_options( [ 'ip_headers' => [ $header ] ] );
	}

	public function setUp() : void {
		remove_filter( 'redirection_request_ip', array( Redirection::init(), 'no_ip_logging' ) );
		$this->resetIpSettings();
	}

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

	public function testSlashedRequestUrl() {
		$action = $this->monitorAction( 'redirection_request_url' );
		$_SERVER['REQUEST_URI'] = "test\\'s";

		$result = Redirection_Request::get_request_url();

		$this->assertEquals( "test's", $result );
		$this->assertEquals( 1, $action->get_call_count() );
		$this->assertEquals( "test's", $this->getActionData( $action ) );
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

	public function testNoAcceptLanguage() {
		unset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
		$language = Redirection_Request::get_accept_language();

		$this->assertEquals( [], $language );
	}

	public function testAcceptLanguage() {
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ab, cd, de;something';
		$language = Redirection_Request::get_accept_language();

		$this->assertEquals( [ 'ab', 'cd', 'de' ], $language );
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

		$result = Redirection_Request::get_ip();

		$this->assertEquals( '', $result );
		$this->assertEquals( '', $this->ip );

		$this->removeMonitorRequestIP();
	}

	public function testMultipleForwardedIP() {
		$this->monitorRequestIP();

		$this->allowIpFrom( 'HTTP_X_FORWARDED_FOR' );

		$_SERVER['HTTP_X_FORWARDED_FOR'] = ' 192.1.1.1, 192.1.1.2, 192.1.2.3';
		$_SERVER['REMOTE_ADDR'] = '192.1.1.2';

		$result = Redirection_Request::get_ip();

		// The right-most value is used.
		$this->assertEquals( '192.1.2.3', $result );
		$this->assertEquals( '192.1.2.3', $this->ip );

		$this->removeMonitorRequestIP();
	}

	public function testPreferForwardedIP() {
		$this->monitorRequestIP();

		$_SERVER['HTTP_X_FORWARDED_FOR'] = '192.1.1.1';
		$_SERVER['REMOTE_ADDR'] = '192.1.1.2';

		$this->allowIpFrom( 'HTTP_X_FORWARDED_FOR' );

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

		red_set_options( array( 'ip_headers' => [] ) );

		unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		unset( $_SERVER['REMOTE_ADDR'] );
		$_SERVER['HTTP_CF_CONNECTING_IP'] = '192.1.1.3';

		$result = Redirection_Request::get_ip();

		$this->assertEquals( '', $result );

		$this->allowIpFrom( 'HTTP_CF_CONNECTING_IP' );

		$result = Redirection_Request::get_ip();
		$this->assertEquals( '192.1.1.3', $result );

		$this->removeMonitorRequestIP();
	}

	public function testBadIP4() {
		$this->monitorRequestIP();

		red_set_options( array( 'ip_headers' => [] ) );

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
		$front = Redirection::init();

		add_filter( 'redirection_request_ip', array( $front, 'mask_ip' ) );
		red_set_options( array( 'ip_logging' => 2 ) );

		unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		unset( $_SERVER['REMOTE_ADDR'] );
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';

		$result = Redirection_Request::get_ip();
		$this->assertEquals( '192.168.1.0', $result );
		remove_filter( 'redirection_request_ip', array( $front, 'mask_ip' ) );
	}

	public function testMaskIP6() {
		$front = Redirection::init();

		add_filter( 'redirection_request_ip', array( $front, 'mask_ip' ) );;
		red_set_options( array( 'ip_logging' => 2 ) );

		unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		unset( $_SERVER['REMOTE_ADDR'] );
		$_SERVER['REMOTE_ADDR'] = '2001:db8:85a3:10:10:8a2e:370:7334';

		$result = Redirection_Request::get_ip();
		$this->assertEquals( '2001:db8:85a3:10::', $result );
		remove_filter( 'redirection_request_ip', array( $front, 'mask_ip' ) );
	}

	public function testMissingHeader() {
		$result = Redirection_Request::get_header( 'test' );

		$this->assertFalse( $result );
	}

	public function testHeader() {
		$_SERVER['HTTP_TEST'] = 'test';

		$result = Redirection_Request::get_header( 'test' );

		$this->assertEquals( 'test', $result );
		unset( $_SERVER['HTTP_TEST'] );
	}

	public function testMissingCookie() {
		$result = Redirection_Request::get_cookie( 'cookie' );

		$this->assertFalse( $result );
	}

	public function testCookie() {
		$_COOKIE['cookie'] = 'cat';
		$result = Redirection_Request::get_cookie( 'cookie' );

		$this->assertEquals( 'cat', $result );
		unset( $_COOKIE['cookie'] );
	}

	private function setRequestHeaders( $headers ) {
		foreach ( $headers as $name => $value ) {
			$_SERVER[ $name ] = $value;
		}
	}

	private function clearRequestHeaders( $headers ) {
		foreach ( array_keys( $headers ) as $name ) {
			unset( $_SERVER[ $name ] );
		}
	}

	public function testCredentialHeadersIgnored() {
		$headers = [
			'HTTP_AUTHORIZATION' => 'Bearer secret-token',
			'HTTP_PROXY_AUTHORIZATION' => 'Basic secret-proxy',
			'HTTP_COOKIE' => 'session=secret-cookie',
			'HTTP_HOST' => 'example.com',
			'HTTP_X_CUSTOM' => 'custom',
		];
		$this->setRequestHeaders( $headers );

		$result = Redirection_Request::get_request_headers();

		$this->assertArrayNotHasKey( 'Authorization', $result );
		$this->assertArrayNotHasKey( 'Proxy-Authorization', $result );
		$this->assertArrayNotHasKey( 'Cookie', $result );
		$this->assertArrayNotHasKey( 'Host', $result );

		// No credential value leaks under any other name.
		$this->assertNotContains( 'Bearer secret-token', $result );
		$this->assertNotContains( 'Basic secret-proxy', $result );
		$this->assertNotContains( 'session=secret-cookie', $result );

		// Everything else is still collected.
		$this->assertEquals( 'custom', $result['X-Custom'] );

		$this->clearRequestHeaders( $headers );
	}

	public $ignored_headers = [];

	public function ignore_custom_header( $ignore ) {
		$this->ignored_headers = $ignore;

		return array_merge( $ignore, [ 'x-custom' ] );
	}

	public function testIgnoredHeadersFilter() {
		$headers = [
			'HTTP_AUTHORIZATION' => 'Bearer secret-token',
			'HTTP_X_CUSTOM' => 'custom',
		];
		$this->setRequestHeaders( $headers );

		add_filter( 'redirection_request_headers_ignore', array( $this, 'ignore_custom_header' ) );

		$result = Redirection_Request::get_request_headers();

		// The filter is given the credential headers as defaults.
		$this->assertContains( 'authorization', $this->ignored_headers );
		$this->assertContains( 'proxy-authorization', $this->ignored_headers );
		$this->assertContains( 'cookie', $this->ignored_headers );
		$this->assertContains( 'host', $this->ignored_headers );

		// And a filtered addition is also ignored.
		$this->assertArrayNotHasKey( 'X-Custom', $result );
		$this->assertArrayNotHasKey( 'Authorization', $result );

		remove_filter( 'redirection_request_headers_ignore', array( $this, 'ignore_custom_header' ) );
		$this->clearRequestHeaders( $headers );
	}
}
