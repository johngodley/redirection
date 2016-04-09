<?php

class Login_Match extends Red_Match {
	public $user_agent = '';

	function name() {
		return __( 'URL and login status', 'redirection' );
	}

	function show() {
		?>
		<tr>
			<th></th>
			<td>
				<p>
					<?php _e( 'The target URL will be chosen from one of the following URLs, depending if the user is logged in or out.  Leaving a URL blank means that the user is not redirected.', 'redirection' ); ?>
				</p>
			</td>
		</tr>
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
					<a target="_blank" href="<?php echo esc_url( $this->url_loggedout ) ?>"><?php _e( 'Logged Out', 'redirection' ); ?>:</a>
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

	function save( $details ) {
		if ( isset( $details['target'] ) )
			$details['target'] = $this->sanitize_url( $details );

		return array(
			'url_loggedin' => isset( $details['url_loggedin'] ) ? $this->sanitize_url( $details['url_loggedin'] ) : false,
			'url_loggedout' => isset( $details['url_loggedout'] ) ? $this->sanitize_url( $details['url_loggedout'] ) : false,
		);
	}

	function initialize( $url ) {
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

	function wants_it() {
		if ( is_user_logged_in() && strlen( $this->url_loggedin ) > 0 )
			return true;

		if ( ! is_user_logged_in() && strlen( $this->url_loggedout ) > 0 )
			return true;

		return false;
	}

	function match_name() {
		return sprintf( 'login status', $this->user_agent );
	}
}
