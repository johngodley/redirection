<?php
/*
Plugin Name: Redirection
Plugin URI: https://redirection.me/
Description: Manage all your 301 redirects and monitor 404 errors
Version: 5.3.10
Author: John Godley
Text Domain: redirection
Domain Path: /locale
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages(including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort(including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================
*/

define( 'REDIRECTION_DB_VERSION', '4.2' );     // DB schema version. Only change if DB needs changing
define( 'REDIRECTION_FILE', __FILE__ );
define( 'REDIRECTION_DEV_MODE', false );

if ( ! defined( 'REDIRECTION_FLYING_SOLO' ) ) {
	define( 'REDIRECTION_FLYING_SOLO', apply_filters( 'redirection_flying_solo', true ) );
}

// This file must support PHP < 5.6 so as not to crash
if ( version_compare( phpversion(), '5.6' ) < 0 ) {
	add_action( 'plugin_action_links_' . basename( dirname( REDIRECTION_FILE ) ) . '/' . basename( REDIRECTION_FILE ), 'red_deprecated_php', 10, 4 );

	function red_deprecated_php( $links ) {
		/* translators: 1: server PHP version. 2: required PHP version. */
		array_unshift( $links, '<a href="https://redirection.me/support/problems/php-version/" style="color: red; text-decoration: underline">' . sprintf( __( 'Disabled! Detected PHP %1$s, need PHP %2$s+', 'redirection' ), phpversion(), '5.6' ) . '</a>' );
		return $links;
	}

	return;
}

require_once __DIR__ . '/redirection-version.php';
require_once __DIR__ . '/redirection-settings.php';
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

function red_is_wpcli() {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return true;
	}

	return false;
}

function red_is_admin() {
	if ( is_admin() ) {
		return true;
	}

	return false;
}

function red_start_rest() {
	require_once __DIR__ . '/redirection-admin.php';
	require_once __DIR__ . '/api/api.php';

	Redirection_Api::init();
	Redirection_Admin::init();

	remove_action( 'rest_api_init', 'red_start_rest' );
}

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
