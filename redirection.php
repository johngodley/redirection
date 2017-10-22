<?php
/*
Plugin Name: Redirection
Plugin URI: http://urbangiraffe.com/plugins/redirection/
Description: Manage all your 301 redirects and monitor 404 errors
Version: 2.8.1
Author: John Godley
Author URI: http://urbangiraffe.com
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

define( 'REDIRECTION_DB_VERSION', '2.3.3' );     // DB schema version. Only change if DB needs changing
define( 'REDIRECTION_FILE', __FILE__ );
define( 'REDIRECTION_DEV_MODE', false );

if ( !defined( 'REDIRECTION_FLYING_SOLO' ) ) {
	define( 'REDIRECTION_FLYING_SOLO', apply_filters( 'redirection_flying_solo', true ) );
}

include dirname( __FILE__ ).'/redirection-version.php';
include dirname( __FILE__ ).'/models/redirect.php';
include dirname( __FILE__ ).'/models/module.php';
include dirname( __FILE__ ).'/models/log.php';
include dirname( __FILE__ ).'/models/flusher.php';
include dirname( __FILE__ ).'/models/match.php';
include dirname( __FILE__ ).'/models/action.php';
include dirname( __FILE__ ).'/models/request.php';

function red_get_options() {
	$options = get_option( 'redirection_options' );
	if ( $options === false ) {
		$options = array();
	}

	$defaults = apply_filters( 'red_default_options', array(
		'support'             => false,
		'token'               => md5( uniqid() ),
		'monitor_post'        => 0,
		'monitor_types'       => array(),
		'associated_redirect' => '',
		'auto_target'         => '',
		'expire_redirect'     => 7,
		'expire_404'          => 7,
		'modules'             => array(),
		'newsletter'          => false,
	) );

	foreach ( $defaults as $key => $value ) {
		if ( ! isset( $options[ $key ] ) ) {
			$options[ $key ] = $value;
		}
	}

	// Back-compat. If monitor_post is set without types then it's from an older Redirection
	if ( $options['monitor_post'] > 0 && count( $options['monitor_types'] ) === 0 ) {
		$options['monitor_types'] = array( 'post' );
	}

	return $options;
}

function red_is_wpcli() {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return true;
	}

	return false;
}

function red_is_admin() {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return true;
	}

	if ( is_admin() ) {
		return true;
	}

	return false;
}

if ( red_is_wpcli() ) {
	include dirname( __FILE__ ).'/redirection-admin.php';
    include dirname( __FILE__ ).'/redirection-cli.php';
} elseif ( red_is_admin() ) {
	include dirname( __FILE__ ).'/redirection-admin.php';
} else {
	include dirname( __FILE__ ).'/redirection-front.php';
}
