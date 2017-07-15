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

	public function save( array $details, $no_target_url = false ) {
		if ( ! isset( $details['action_data'] ) || strlen( $details['action_data'] ) === 0 ) {
			$details['action_data'] = '/';
		}

		if ( $no_target_url ) {
			return null;
		}

		return $this->sanitize_url( $details['action_data'] );
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
