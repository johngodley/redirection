<?php

class RequestTest extends WP_UnitTestCase {
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

	public function testNoIP() {
		$action = $this->monitorAction( 'redirection_request_ip' );
		unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		unset( $_SERVER['REMOTE_ADDR'] );

		$result = Redirection_Request::get_ip();

		$this->assertEquals( '', $result );
		$this->assertEquals( 1, $action->get_call_count() );
		$this->assertEquals( '', $this->getActionData( $action ) );
	}

	public function testPreferForwardedIP() {
		$action = $this->monitorAction( 'redirection_request_ip' );
		$_SERVER['HTTP_X_FORWARDED_FOR'] = 'forwarded';
		$_SERVER['REMOTE_ADDR'] = 'remote address';

		$result = Redirection_Request::get_ip();

		$this->assertEquals( 'forwarded', $result );
		$this->assertEquals( 1, $action->get_call_count() );
		$this->assertEquals( 'forwarded', $this->getActionData( $action ) );
	}

	public function testDefaultHostIP() {
		$action = $this->monitorAction( 'redirection_request_ip' );
		unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		$_SERVER['REMOTE_ADDR'] = 'remote address';

		$result = Redirection_Request::get_ip();

		$this->assertEquals( 'remote address', $result );
		$this->assertEquals( 1, $action->get_call_count() );
		$this->assertEquals( 'remote address', $this->getActionData( $action ) );
	}
}
