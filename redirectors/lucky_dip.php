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
class Redirector_LuckyDip extends Redirector
{
	function initialize ($url) { }
	
	function name () { return __ ('Redirect to a random WordPress post', 'redirection'); }
	
	function show ()
	{
	}
	
	function save ($details)
	{
	}
	
	function get_target ($url, $matched_url, $regex)
	{
		// Pick a random WordPress page
		global $wpdb;
		$id = $wpdb->get_var ("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='publish' AND post_password='' AND post_type='post' ORDER BY RAND()");
		return str_replace (get_bloginfo ('home'), '', get_permalink ($id));
	}
}

$this->register ('Redirector_LuckyDip');

?>