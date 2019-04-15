<?php
/*
Plugin Name: Redirection
Plugin URI: https://redirection.me/
Description: Manage all your 301 redirects and monitor 404 errors
Version: 4.2.3
Author: John Godley
Author URI: https://johngodley.com
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

define( 'REDIRECTION_DB_VERSION', '4.1' );     // DB schema version. Only change if DB needs changing
define( 'REDIRECTION_FILE', __FILE__ );
define( 'REDIRECTION_DEV_MODE', false );

if ( ! defined( 'REDIRECTION_FLYING_SOLO' ) ) {
	define( 'REDIRECTION_FLYING_SOLO', apply_filters( 'redirection_flying_solo', true ) );
}

// This file must support PHP < 5.4 so as not to crash
if ( version_compare( phpversion(), '5.4' ) < 0 ) {
	add_action( 'plugin_action_links_' . basename( dirname( REDIRECTION_FILE ) ) . '/' . basename( REDIRECTION_FILE ), 'red_deprecated_php', 10, 4 );

	function red_deprecated_php( $links ) {
		/* translators: 1: PHP version */
		array_unshift( $links, '<a href="https://redirection.me/support/problems/php-version/" style="color: red; text-decoration: underline">' . sprintf( __( 'Disabled! Detected PHP %s, need PHP 5.4+', 'redirection' ), phpversion() ) . '</a>' );
		return $links;
	}

	return;
}

include dirname( __FILE__ ) . '/redirection-version.php';
include dirname( __FILE__ ) . '/redirection-settings.php';
include dirname( __FILE__ ) . '/models/redirect.php';
include dirname( __FILE__ ) . '/models/module.php';
include dirname( __FILE__ ) . '/models/log.php';
include dirname( __FILE__ ) . '/models/flusher.php';
include dirname( __FILE__ ) . '/models/match.php';
include dirname( __FILE__ ) . '/models/action.php';
include dirname( __FILE__ ) . '/models/request.php';

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
	include_once dirname( __FILE__ ) . '/redirection-admin.php';
	include_once dirname( __FILE__ ) . '/redirection-api.php';

	Redirection_Api::init();
	Redirection_Admin::init();

	remove_action( 'rest_api_init', 'red_start_rest' );
}

function redirection_locale() {
	load_plugin_textdomain( 'redirection', false, dirname( plugin_basename( REDIRECTION_FILE ) ) . '/locale/' );
}

if ( red_is_admin() || red_is_wpcli() ) {
	include_once dirname( __FILE__ ) . '/redirection-admin.php';
	include_once dirname( __FILE__ ) . '/redirection-api.php';
} else {
	include_once dirname( __FILE__ ) . '/redirection-front.php';
}

if ( red_is_wpcli() ) {
	include_once dirname( __FILE__ ) . '/redirection-cli.php';
}

add_action( 'rest_api_init', 'red_start_rest' );
add_action( 'init', 'redirection_locale' );

// This is causing a lot of problems with the REST API - disable qTranslate
add_filter( 'qtranslate_language_detect_redirect', function( $lang, $url ) {
	$url = Redirection_Request::get_request_url();

	if ( strpos( $url, '/wp-json/' ) !== false || strpos( $url, 'index.php?rest_route' ) !== false ) {
		return false;
	}

	return $lang;
}, 10, 2 );
