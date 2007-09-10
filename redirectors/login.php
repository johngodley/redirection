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
class Redirector_Login extends Redirector
{
	function name () { return __ ('Redirect based on login status', 'redirection');}
	
	function show ()
	{
		?>
		<tr>
			<th valign="top">
				<?php if (strlen ($this->url[0]) > 0) : ?>
				<a target="_blank" href="<?php echo $this->url[0] ?>"><?php _e ('Logged in URL', 'redirection'); ?>:</a>
				<?php else : ?>
				<?php _e ('Logged in URL', 'redirection'); ?>:
				<?php endif; ?>
			</th>
			<td valign="top"><input style="width: 95%" type="text" name="url_loggedin" value="<?php echo htmlspecialchars ($this->url[0]); ?>" id="new"/></td>
		</tr>
		<tr>
			<th valign="top">
				<?php if (strlen ($this->url[1]) > 0) : ?>
				<a target="_blank" href="<?php echo $this->url[1] ?>"><?php _e ('Logged out URL', 'redirection'); ?>:</a>
				<?php else : ?>
				<?php _e ('Logged out URL', 'redirection'); ?>:
				<?php endif; ?>
			</th>
			<td valign="top">
				<input style="width: 95%" type="text" name="url_loggedout" value="<?php echo htmlspecialchars ($this->url[1]); ?>" id="new"/><br/>
				<span class="sub"><?php _e ('An empty URL means the source URL is not redirected when the user is logged in/logged out.', 'redirection'); ?></span>
			</td>
		</tr>
		<?php
	}
	
	function save ($details)
	{
		$this->url = array ($details['url_loggedin'], $details['url_loggedout']);
	}
	
	function initialize ($url)
	{
		$this->url = array ($url, '');
	}
	
	function get_target ($url, $matched_url, $regex)
	{
		if (is_user_logged_in () === false)
			$target = $this->url[1];
		else
			$target = $this->url[0];

		if ($regex)
			$target = preg_replace ('@'.str_replace ('@', '\\@', $matched_url).'@', $target, $url);
		return $target;
	}
	
	function wants_it ()
	{
		if (is_user_logged_in () && strlen ($this->url[0]) > 0)
			return true;
		if (!is_user_logged_in () && strlen ($this->url[1]) > 0)
			return true;
	}
}


$this->register ('Redirector_Login');

?>
