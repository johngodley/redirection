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
if (!class_exists ('Redirector'))
{
	echo 'You should not reach this message.';
	debug_backtrace ();
}

class Redirector_Referrer extends Redirector
{
	var $referrer;
	var $regex;
	
	function name () { return __ ('Redirect based on referrer', 'redirection'); }
	
	function show ()
	{
		?>
		<tr>
			<th valign="top"><?php _e ('Referrer', 'redirection'); ?>:</th>
			<td valign="top"><input style="width: 95%" type="text" name="referrer" value="<?php echo htmlspecialchars ($this->referrer); ?>"/></td>
		</tr>
		<tr>
	    <th align="right"><?php _e ('Referrer Regex', 'redirection') ?>:</th>
	    <td><input type="checkbox" name="referrer_regex" <?php if ($this->regex == true) echo ' checked="checked"' ?>/></td>
	  </tr>
		<tr>
			<th valign="top">
				<?php if (strlen ($this->url[0]) > 0) : ?>
				<a target="_blank" href="<?php echo $this->url[0] ?>"><?php _e ('URL from referrer', 'redirection'); ?>:</a>
				<?php else : ?>
				<?php _e ('URL from referrer', 'redirection'); ?>:
				<?php endif; ?>
			</th>
			<td valign="top"><input style="width: 95%" type="text" name="url_from" value="<?php echo htmlspecialchars ($this->url[0]); ?>" id="new"/></td>
		</tr>
		<tr>
			<th valign="top">
				<?php if (strlen ($this->url[1]) > 0) : ?>
				<a target="_blank" href="<?php echo $this->url[1] ?>"><?php _e ('Not from referrer', 'redirection'); ?>:</a>
				<?php else : ?>
				<?php _e ('Not from referrer', 'redirection'); ?>:
				<?php endif; ?>
			</th>
			<td valign="top">
				<input style="width: 95%" type="text" name="url_notfrom" value="<?php echo htmlspecialchars ($this->url[1]); ?>" id="new"/><br/>
				<span class="sub"><?php _e ('An empty URL means the source URL is not redirected.', 'redirection'); ?></span>
			</td>
		</tr>
		<?php
	}
	
	function save ($details)
	{
		$this->url      = array ($details['url_from'], $details['url_notfrom']);
		$this->regex    = isset ($details['referrer_regex']) ? true : false;
		$this->referrer = $details['referrer'];
	}
	
	function initialize ($url)
	{
		$this->url = array ($url, '');
	}

	function wants_it ()
	{
		// Match referrer
		return true;
	}
	
	function get_target ($url, $matched_url, $regex)
	{
		// Check if referrer matches
		if (($this->regex == false && $_SERVER['HTTP_REFERER'] == $this->referrer) || ($this->regex == true && preg_match ('@'.str_replace ('@', '\\@', $this->referrer).'@', $_SERVER['HTTP_REFERER'], $matches)))
		{
			$target = $this->url[0];
			if ($regex)
				$target = preg_replace ('@'.str_replace ('@', '\\@', $matched_url).'@', $target, $url);
		}
		else
			$target = $this->url[1];
		return $target;
	}
}

$this->register ('Redirector_Referrer');

?>