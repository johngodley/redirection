<?php

class Follow_Match extends Red_Match {
	function name () {
		return __( 'Follow-Up Patern', 'redirection' );
	}

	function show() {
		?>
		<?php if ( $this->action->can_perform_action() ) : ?>
			<tr>
				<th><a target="_blank" href="<?php echo esc_url( $this->url ) ?>"><?php _e( 'Target URL', 'redirection' ); ?>:</a></th>
				<td>
					<input style="width: 95%" type="text" name="target" value="<?php echo esc_attr( $this->url ); ?>"/>
				</td>
			</tr>
		<?php endif; ?>
		<?php if ( $this->action->can_change_code() ) : ?>
			<tr>
				<th><?php _e( 'HTTP Code', 'redirection' ); ?>:</th>
				<td>
					<select name="action_code">
						<?php $this->action->display_actions(); ?>
					</select>
				</td>
			</tr>
		<?php endif;
	}

	function save( $details ) {
		if ( ! isset( $details['target'] ) || strlen( $details['target'] ) == 0 ) {
			$details['target'] = '/';
		} else {
			$clean_url = rtrim( $details['target'], '/' );
			$follow_url = $clean_url . '$1';

			$details['target'] = $follow_url;
		}

		return array( 'url' => $details['target'] );
	}

	function get_target( $url, $matched_url, $regex ) {
		$target = $this->url;
		if ( $regex )
			$target = preg_replace( '@'.str_replace( '@', '\\@', $matched_url ).'@', $this->url, $url );

		if ( $target == '' )
			return $matched_url;
		return $target;
	}
}
