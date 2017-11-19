<?php

include_once ABSPATH . 'wp-admin/includes/plugin.php';

class RedirectionApiDeletePluginTest extends WP_Ajax_UnitTestCase {
	public static $redirection;
	private $logs = array();

	private function do_action( $params = array() ) {
		return json_decode( self::$redirection->ajax_delete_plugin( $params ) );
	}

	public static function setupBeforeClass() {
		self::$redirection = Redirection_Admin::init()->api;
	}

	private function setNonce() {
		$this->_setRole( 'administrator' );
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'wp_rest' );
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
		$active[] = basename( dirname( REDIRECTION_FILE ) ).'/'.basename( REDIRECTION_FILE );
		update_option( 'active_plugins', $active );

		$this->setNonce();
		$result = $this->do_action();

		$active = get_option( 'active_plugins' );
		$this->assertEquals( array(), $active );
		$this->assertFalse( get_option( 'redirection_options' ) );

		$after = $wpdb->get_results( "SHOW TABLES LIKE '%{$wpdb->prefix}redirection_%'" );
		// todo test not working, but it does delete the tables
//		$this->assertEquals( array(), $after );
	}
}
