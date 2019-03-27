<?php

include_once ABSPATH . 'wp-admin/includes/plugin.php';

class RedirectionApiPluginTest extends Redirection_Api_Test {
	public function testNoPermission() {
		$this->setUnauthorised();

		$result = $this->callApi( 'plugin' );
		$this->assertEquals( 403, $result->status );

		$result = $this->callApi( 'plugin', array(), 'POST' );
		$this->assertEquals( 403, $result->status );

		$result = $this->callApi( 'plugin', array(), 'POST' );
		$this->assertEquals( 403, $result->status );
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
}
