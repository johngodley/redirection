<?php

require_once ABSPATH . 'wp-admin/includes/plugin.php';

class RedirectionApiPluginTest extends Redirection_Api_Test {
	private function get_endpoints() {
		return [
			[ 'plugin', 'GET', [] ],
			[ 'plugin', 'POST', [] ],
			[ 'plugin/delete', 'POST', [] ],
			[ 'plugin/test', 'GET', [] ],
			[ 'plugin/test', 'POST', [] ],
			[ 'plugin/post', 'GET', [] ],
			[ 'plugin/database', 'POST', [] ],
		];
	}

	public function testNoPermission() {
		$this->setUnauthorised();

		// None of these should work
		$this->check_endpoints( $this->get_endpoints() );
	}

	public function testEditorPermission() {
		// Everything else is 403
		$working = [
			Redirection_Capabilities::CAP_SUPPORT_MANAGE => [
				[ 'plugin', 'GET' ],
				[ 'plugin', 'POST' ],
				[ 'plugin/delete', 'POST' ],
				[ 'plugin/test', 'GET' ],
				[ 'plugin/test', 'POST' ],
				[ 'plugin/post', 'GET' ],
				[ 'plugin/database', 'POST' ],
			],
		];

		$this->setEditor();

		foreach ( $working as $cap => $working_caps ) {
			$this->add_capability( $cap );
			$this->check_endpoints( $this->get_endpoints(), $working_caps );
			$this->clear_capability();
		}
	}

	public function testDelete() {
		global $wpdb;

		if ( is_multisite() ) {
			// This doesnt do anything on multisite
			return;
		}

		$before = $wpdb->get_results( "SHOW TABLES LIKE '%{$wpdb->prefix}redirection_%'" );
		$this->assertNotEquals( array(), $before );

		$active = get_option( 'active_plugins' );
		$active[] = basename( dirname( REDIRECTION_FILE ) ) . '/' . basename( REDIRECTION_FILE );
		update_option( 'active_plugins', $active );

		$this->setNonce();
		$result = $this->callApi( 'plugin/delete', array(), 'POST' );

		$active = get_option( 'active_plugins' );
		$this->assertEquals( array(), $active );
		$this->assertFalse( get_option( 'redirection_options' ) );

		$after = $wpdb->get_results( "SHOW TABLES LIKE '%{$wpdb->prefix}redirection_%'" );
		// todo test not working, but it does delete the tables
//		$this->assertEquals( array(), $after );
	}

	public function testStatus() {
		$this->setNonce();

		$results = $this->callApi( 'plugin' );
		$this->assertEquals( 'db', $results->data['status'][0]['id'] );
	}

	public function testFixStatus() {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->prefix}redirection_groups" );

		$this->setNonce();

		$results = $this->callApi( 'plugin' );

		$this->assertEquals( 'groups', $results->data['status'][1]['id'] );
		$this->assertEquals( 'problem', $results->data['status'][1]['status'] );

		$results = $this->callApi( 'plugin', array(), 'POST' );

		$this->assertEquals( 'groups', $results->data['status'][1]['id'] );
		$this->assertEquals( 'good', $results->data['status'][1]['status'] );
	}

	public function testMatchPost() {
		$this->setNonce();

		$results = $this->callApi( 'plugin/post', [ 'text' => 'e' ] );
		$this->assertTrue( count( $results->data ) > 0 );
	}

	public function testNoMatchPost() {
		$this->setNonce();

		$results = $this->callApi( 'plugin/post', [ 'text' => 'z' ] );
		$this->assertTrue( count( $results->data ) === 0 );
	}
}
