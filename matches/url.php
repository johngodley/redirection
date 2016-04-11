<?php

class URL_Match extends Red_Match {
	function name () {
		return __( 'URL only', 'redirection' );
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
		<tr class="advanced">
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
		if ( ! isset( $details['target'] ) || strlen( $details['target'] ) === 0 )
			$details['target'] = '/';

		return array( 'url' => $this->sanitize_url( $details['target'] ) );
	}

	function get_target( $url, $matched_url, $regex ) {
		$target = $this->url;
		if ( $regex )
			$target = preg_replace( '@'.str_replace( '@', '\\@', $matched_url ).'@', $this->url, $url );

		if ( $target === '' )
			return $matched_url;
		return $target;
	}
}
