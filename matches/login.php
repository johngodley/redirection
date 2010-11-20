<?php
/**
 * Redirection
 *
 * @package Redirection
 * @author John Godley
 * @copyright Copyright( C ) John Godley
 **/

/*
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages( including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption ) however caused and on any theory of liability, whether in
contract, strict liability, or tort( including negligence or otherwise ) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================ */

class Login_Match extends Red_Match {
	function name() {
		return __( 'URL and login status', 'redirection' );
	}
	
	function show()	{
		?>
		</table>
		
		<p style="padding: 0.5em">
			<?php _e( 'The target URL will be chosen from one of the following URLs, depending if the user is logged in or out.  Leaving a URL blank means that the user is not redirected.', 'redirection' ); ?>
		</p>
		<table class="edit">
		<tr>
			<th width="100" valign="top">
				<?php if ( strlen( $this->url_loggedin ) > 0 ) : ?>
					<a target="_blank" href="<?php echo esc_url( $this->url_loggedin ) ?>"><?php _e( 'Logged In', 'redirection' ); ?>:</a>
				<?php else : ?>
					<?php _e( 'Logged In', 'redirection' ); ?>:
				<?php endif; ?>
			</th>
			<td valign="top">
				<input style="width: 95%" type="text" name="url_loggedin" value="<?php echo esc_attr( $this->url_loggedin ); ?>"/>
			</td>
		</tr>
		<tr>
			<th width="100" valign="top">
				<?php if ( strlen( $this->url_loggedout ) > 0 ) : ?>
					<a target="_blank" href="<?php echo $this->url_loggedout ?>"><?php _e( 'Logged Out', 'redirection' ); ?>:</a>
				<?php else : ?>
					<?php _e( 'Logged Out', 'redirection' ); ?>:
				<?php endif; ?>
			</th>
			<td valign="top">
				<input style="width: 95%" type="text" name="url_loggedout" value="<?php echo esc_attr( $this->url_loggedout ); ?>"/><br/>
			</td>
		</tr>
		<?php
	}
	
	function save( $details )	{
		if ( isset( $details['target'] ) )
			$details['target'] = $details;

		return array(
			'url_loggedin' => $details['url_loggedin'],
			'url_loggedout' => $details['url_loggedout']
		);
	}
	
	function initialize( $url )	{
		$this->url = array( $url, '' );
	}
	
	function get_target( $url, $matched_url, $regex ) {
		if ( is_user_logged_in() === false )
			$target = $this->url_loggedout;
		else
			$target = $this->url_loggedin;

		if ( $regex )
			$target = preg_replace( '@'.str_replace( '@', '\\@', $matched_url ).'@', $target, $url );
		return $target;
	}
	
	function wants_it()	{
		if ( is_user_logged_in() && strlen( $this->url_loggedin ) > 0 )
			return true;
		if ( !is_user_logged_in() && strlen( $this->url_loggedout ) > 0 )
			return true;
	}
	
	function match_name() {
		return sprintf( 'login status', $this->user_agent );
	}
}
