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
class Redirector_Random extends Redirector
{
	function initialize ($url)
	{
		$this->url = array ($url);
	}
	
	function name () { return __ ('Redirect to one of several URLs', 'redirection'); }
	
	function show ()
	{
		?>
		<tr>
			<th valign="top"><a target="_blank" href="<?php echo $this->url ?>"><?php _e ('Target URL', 'redirection'); ?>:</a></th>
			<td>
				<?php foreach ($this->url AS $url) : ?>
				<input style="width: 90%" type="text" name="new[]" value="<?php echo htmlspecialchars ($url); ?>"/>
				<?php endforeach; ?>
				<a href="#" onclick="add_new_url (this); return false;">add</a>
			</td>
		</tr>
		<?php
	}
	
	function save ($details)
	{
		$this->url = $details['new'];
		$this->url = array_filter ($this->url);
	}
	
	function get_target ($url, $matched_url, $regex)
	{
		$target = $this->url[array_rand ($this->url)];
		if ($regex)
			$target = preg_replace ('@'.str_replace ('@', '\\@', $matched_url).'@', $target, $url);
		return $target;
	}
}

$this->register ('Redirector_Random');

?>