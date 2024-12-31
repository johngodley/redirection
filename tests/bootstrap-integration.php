<?php

/**
 * PHPUnit bootstrap file.
 *
 * @package Starter_Plugin
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
define( 'PLUGIN_PATH', dirname( __DIR__ ) );

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' );
if ( false !== $_phpunit_polyfills_path ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path );
}

require 'vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 *
 * @return void
 */
function _manually_load_plugin() {
	require PLUGIN_PATH . '/redirection.php';
	require PLUGIN_PATH . '/redirection-admin.php';
	require PLUGIN_PATH . '/database/schema/latest.php';

	$database = new Red_Latest_Database();
	$database->remove();
	$database->install();
}

// @phpstan-ignore function.notFound
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
require PLUGIN_PATH . '/tests/api-test.php';
