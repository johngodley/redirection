<?php
/*
Plugin Name: Redirection
Plugin URI: http://urbangiraffe.com/plugins/redirection/
Description: Manage all your 301 redirects and monitor 404 errors
Version: 2.4.5
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

define( 'REDIRECTION_VERSION', '2.3.2' );     // DB schema version. Only change if DB needs changing
define( 'REDIRECTION_FILE', __FILE__ );

include dirname( __FILE__ ).'/models/module.php';
include dirname( __FILE__ ).'/models/log.php';
include dirname( __FILE__ ).'/models/flusher.php';
include dirname( __FILE__ ).'/models/match.php';
include dirname( __FILE__ ).'/models/action.php';
include dirname( __FILE__ ).'/models/redirect.php';

function red_get_options() {
	$options = get_option( 'redirection_options' );
	if ( $options === false )
		$options = array();

	$defaults = apply_filters( 'red_default_options', array(
		'support'         => false,
		'token'           => md5( uniqid() ),
		'monitor_post'    => 0,
		'auto_target'     => '',
		'expire_redirect' => 7,
		'expire_404'      => 7,
		'modules'         => array(),
	) );

	foreach ( $defaults as $key => $value ) {
		if ( ! isset( $options[ $key ] ) )
			$options[ $key ] = $value;
	}

	$options['lookup'] = apply_filters( 'red_lookup_ip', 'http://urbangiraffe.com/map/?ip=' );
	return $options;
}

if ( is_admin() )
	include dirname( __FILE__ ).'/redirection-admin.php';
else
	include dirname( __FILE__ ).'/redirection-front.php';
