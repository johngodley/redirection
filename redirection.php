<?php
/*
Plugin Name: Redirection
Plugin URI: https://redirection.me/
Description: Manage all your 301 redirects and monitor 404 errors
Version: 5.6.0
Author: John Godley
Text Domain: redirection
Requires PHP: 7.2
Requires at least: 6.5
============================================================================================================
For full license details see license.txt
============================================================================================================
*/

define( 'REDIRECTION_DB_VERSION', '4.2' );     // DB schema version. Only change if DB needs changing
define( 'REDIRECTION_FILE', __FILE__ );

if ( ! defined( 'REDIRECTION_FLYING_SOLO' ) ) {
	define( 'REDIRECTION_FLYING_SOLO', apply_filters( 'redirection_flying_solo', true ) );
}

// This file must support PHP < 7.2 so as not to crash
if ( version_compare( phpversion(), '7.2' ) < 0 ) {
	add_filter( 'plugin_action_links_' . basename( dirname( REDIRECTION_FILE ) ) . '/' . basename( REDIRECTION_FILE ), 'red_deprecated_php' );

	/**
	 * @param array<string> $links
	 * @return array<string>
	 */
	function red_deprecated_php( array $links ): array {
		/* translators: 1: server PHP version. 2: required PHP version. */
		array_unshift( $links, '<a href="https://redirection.me/support/problems/php-version/" style="color: red; text-decoration: underline">' . sprintf( __( 'Disabled! Detected PHP %1$s, need PHP %2$s+', 'redirection' ), phpversion(), '7.2' ) . '</a>' );
		return $links;
	}

	return;
}

require_once __DIR__ . '/build/redirection-version.php';
require_once __DIR__ . '/redirection-settings.php';
require_once __DIR__ . '/models/options.php';
require_once __DIR__ . '/models/redirect/redirect.php';
require_once __DIR__ . '/models/url/url.php';
require_once __DIR__ . '/models/regex.php';
require_once __DIR__ . '/models/module.php';
require_once __DIR__ . '/models/log/log.php';
require_once __DIR__ . '/models/flusher.php';
require_once __DIR__ . '/models/match.php';
require_once __DIR__ . '/models/action.php';
require_once __DIR__ . '/models/request.php';
require_once __DIR__ . '/models/header.php';
require_once __DIR__ . '/models/group.php';

/**
 * @return bool
 */
function red_is_wpcli() {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return true;
	}

	return false;
}

/**
 * @return bool
 */
function red_is_admin() {
	if ( is_admin() ) {
		return true;
	}

	return false;
}

/**
 * @return void
 */
function red_start_rest() {
	require_once __DIR__ . '/redirection-admin.php';
	require_once __DIR__ . '/api/api.php';

	Redirection_Api::init();
	Redirection_Admin::init();

	remove_action( 'rest_api_init', 'red_start_rest' );
}

/**
 * @return void
 */
function redirection_locale() {
	load_plugin_textdomain( 'redirection', false, dirname( plugin_basename( REDIRECTION_FILE ) ) . '/locale/' );
}

if ( red_is_admin() || red_is_wpcli() ) {
	require_once __DIR__ . '/redirection-admin.php';
	require_once __DIR__ . '/api/api.php';
} else {
	require_once __DIR__ . '/redirection-front.php';
}

if ( red_is_wpcli() ) {
	require_once __DIR__ . '/redirection-cli.php';
}

add_action( 'rest_api_init', 'red_start_rest' );
add_action( 'init', 'redirection_locale' );
