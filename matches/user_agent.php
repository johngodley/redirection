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

class Agent_Match extends Red_Match {
	var $user_agent;

	function name() {
		return __( 'URL and user agent', 'redirection' );
	}

	function show()	{
		$defined = array(
			'feedburner|feedvalidator' => __( 'FeedBurner', 'redirection' ),
			'MSIE'                     => __( 'Internet Explorer', 'redirection' ),
			'Firefox'                  => __( 'FireFox', 'redirection' ),
			'Opera'                    => __( 'Opera', 'redirection' ),
			'Safari'                   => __( 'Safari', 'redirection' ),
			'iPhone'                   => __( 'iPhone', 'redirection' ),
			'iPad'                     => __( 'iPad', 'redirection' ),
			'Android'                  => __( 'Android', 'redirection' ),
			'Wii'                      => __( 'Nintendo Wii', 'redirection' ),
		 );

		?>
		<tr>
			<th width="100"><?php _e( 'User Agent', 'redirection' ); ?>:</th>
			<td>
				<input id="user_agent_<?php echo $this->id ?>" style="width: 65%" type="text" name="user_agent" value="<?php echo esc_attr( $this->user_agent ); ?>"/>
				<select style="width: 30%" onchange="return update_user_agent( this,<?php echo $this->id ?> )">
					<?php foreach ( $defined AS $key => $value ) : ?>
						<option value="<?php echo $key ?>"<?php if ( $key == $this->user_agent ) echo ' selected="selected"' ?>><?php echo esc_html( $value ) ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<?php if ( $this->action->can_change_code() ) : ?>
		<tr>
			<th><?php _e( 'HTTP Code', 'redirection' ); ?>:</th>
			<td>
				<select name="action_code">
					<?php $this->action->display_actions(); ?>
				</select>
			</td>
		</tr>
		<?php endif; ?>

		<?php if ( $this->action->can_perform_action() ) : ?>
		</table>
		<p style="padding: 0.5em"><?php _e( 'The visitor will be redirected from the source URL if the user agent matches.  You can specify a <em>matched</em> target URL as the address to send visitors if they do match, and <em>not matched</em> if they don\'t match.  Leaving a URL blank means that the visitor is not redirected. <strong>All matches are performed as regular expressions</strong>.
', 'redirection' ); ?></p>
		<table class="edit">

		<tr>
			<th width="100" valign="top">
				<?php if ( strlen( $this->url_from ) > 0 ) : ?>
				<a target="_blank" href="<?php echo esc_url( $this->url_from ) ?>"><?php _e( 'Matched', 'redirection' ); ?>:</a>
				<?php else : ?>
				<?php _e( 'Matched', 'redirection' ); ?>:
				<?php endif; ?>
			</th>
			<td valign="top"><input style="width: 95%" type="text" name="url_from" value="<?php echo esc_attr( $this->url_from ); ?>" id="new"/></td>
		</tr>
		<tr>
			<th width="100" valign="top">
				<?php if ( strlen( $this->url_notfrom ) > 0 ) : ?>
				<a target="_blank" href="<?php echo esc_url( $this->url_notfrom ) ?>"><?php _e( 'Not matched', 'redirection' ); ?>:</a>
				<?php else : ?>
				<?php _e( 'Not matched', 'redirection' ); ?>:
				<?php endif; ?>
			</th>
			<td valign="top">
				<input style="width: 95%" type="text" name="url_notfrom" value="<?php echo esc_attr( $this->url_notfrom ); ?>" id="new"/><br/>
			</td>
		</tr>
		<?php endif; ?>
		<?php
	}

	function save( $details )	{
		if ( isset( $details['target'] ) )
			$details['url_from'] = $details['target'];

		return array(
			'url_from'    => $details['url_from'],
			'url_notfrom' => $details['url_notfrom'],
			'user_agent'  => $details['user_agent']
		);
	}

	function initialize( $url ) {
		$this->url = array( $url, '' );
	}

	function wants_it() {
		// Match referrer
		return true;
	}

	function get_target( $url, $matched_url, $regex ) {
		// Check if referrer matches
		if ( preg_match( '@'.str_replace( '@', '\\@', $this->user_agent ).'@i', $_SERVER['HTTP_USER_AGENT'], $matches ) > 0 )
			return preg_replace( '@'.str_replace( '@', '\\@', $matched_url ).'@', $this->url_from, $url );
		elseif ( $this->url_notfrom != '' )
			return $this->url_notfrom;
		return false;
	}

	function match_name()	{
		return sprintf( 'user agent - %s', $this->user_agent );
	}
}
