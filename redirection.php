<?php
/*
Plugin Name: Redirection
Plugin URI: https://redirection.me/
Description: Manage all your 301 redirects and monitor 404 errors
Version: 3.2
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

define( 'REDIRECTION_DB_VERSION', '2.4' );     // DB schema version. Only change if DB needs changing
define( 'REDIRECTION_FILE', __FILE__ );
define( 'REDIRECTION_DEV_MODE', false );

if ( ! defined( 'REDIRECTION_FLYING_SOLO' ) ) {
	define( 'REDIRECTION_FLYING_SOLO', apply_filters( 'redirection_flying_solo', true ) );
}

include dirname( __FILE__ ).'/redirection-version.php';
include dirname( __FILE__ ).'/redirection-settings.php';
include dirname( __FILE__ ).'/models/redirect.php';
include dirname( __FILE__ ).'/models/module.php';
include dirname( __FILE__ ).'/models/log.php';
include dirname( __FILE__ ).'/models/flusher.php';
include dirname( __FILE__ ).'/models/match.php';
include dirname( __FILE__ ).'/models/action.php';
include dirname( __FILE__ ).'/models/request.php';

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
	include_once dirname( __FILE__ ).'/redirection-admin.php';
	include_once dirname( __FILE__ ).'/redirection-api.php';

	Redirection_Api::init();

	remove_action( 'rest_api_init', 'red_start_rest' );
}

if ( red_is_admin() || red_is_wpcli() ) {
	include_once dirname( __FILE__ ).'/redirection-admin.php';
	include_once dirname( __FILE__ ).'/redirection-api.php';
} else {
	include_once dirname( __FILE__ ).'/redirection-front.php';
}

if ( red_is_wpcli() ) {
	include_once dirname( __FILE__ ).'/redirection-cli.php';
}

add_action( 'rest_api_init', 'red_start_rest' );
