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
class A_Redirector_URL extends Redirector
{
	function name () { return __ ('Simple redirection', 'redirection'); }
	
	function show ()
	{
		?>
		<tr>
			<th><a target="_blank" href="<?php echo $this->url ?>"><?php _e ('Target URL', 'redirection'); ?>:</a></th>
			<td><input style="width: 95%" type="text" name="url_new" value="<?php echo htmlspecialchars ($this->url); ?>" id="new"/></td>
		</tr>
		<?php
	}
	
	function save ($details)
	{
		$this->url = $details['url_new'];
		if (strlen ($this->url) == 0)
			$this->url = '/';
	}
	
	function get_target ($url, $matched_url, $regex)
	{
		$target = $this->url;
		if ($regex)
			$target = preg_replace ('@'.str_replace ('@', '\\@', $matched_url).'@', $this->url, $url);
		return $target;
	}
}

$this->register ('A_Redirector_URL');

?>