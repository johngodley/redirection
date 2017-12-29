<?php

$_tests_dir = getenv('WP_TESTS_DIR');
if ( !$_tests_dir ) $_tests_dir = '/tmp/wordpress-tests-lib';

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( __FILE__ ) . '/../redirection.php';
	require dirname( __FILE__ ) . '/../redirection-admin.php';
	require dirname( __FILE__ ) . '/../models/database.php';

	$database = new RE_Database();
	$database->install();
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

class Redirection_Api_Test extends WP_Ajax_UnitTestCase {
	protected function callApi( $endpoint, array $params = array(), $method = 'GET' ) {
		$request = new WP_REST_Request( $method, '/redirection/v1/'.$endpoint );

		foreach ( $params as $name => $value ) {
			$request->set_param( $name, $value );
		}

		return rest_do_request( $request );
	}

	protected function setNonce() {
		$this->_setRole( 'administrator' );
		$this->nonce = wp_create_nonce( 'wp_rest' );
	}

	protected function setUnauthorised() {
		$this->_setRole( 'anything' );
		$this->nonce = wp_create_nonce( 'wp_rest' );
	}
}
