<?php
/**
 * Redirection
 *
 * @package Redirection
 * @author John Godley
 * @copyright Copyright (C) John Godley
 **/

/*
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages (including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================ */

class Random_Action extends Red_Action
{
	function can_change_code () { return true; }
	function can_perform_action () { return false; }
	
	function action_codes ()
	{
		return array
		(
			301 => get_status_header_desc (301),
			302 => get_status_header_desc (302),
			307 => get_status_header_desc (307)
		);
	}
	
	function process_before ($code, $target)
	{
		// Pick a random WordPress page
		global $wpdb;
		$id = $wpdb->get_var ("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='publish' AND post_password='' AND post_type='post' ORDER BY RAND() LIMIT 0,1");
		
		$target = str_replace (get_bloginfo ('url'), '', get_permalink ($id));
		
		wp_redirect ($target, $code);
		exit ();
	}
}

?>