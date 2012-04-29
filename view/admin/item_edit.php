<?php if ( !defined( 'ABSPATH' ) ) die( 'No direct access allowed' ); ?>
<form method="post" accept-charset="utf-8" action="<?php echo admin_url( 'admin-ajax.php' ) ?>" style="padding: 3px">
	<h3><?php printf( __( '%s by matching %s', 'redirection' ), esc_html( $redirect->actions( $redirect->action_type ) ), esc_html( $redirect->match->name() ) ) ?></h3>

	<table class="edit">
		<tr>
			<th width="100"><?php _e ('Title', 'redirection'); ?>:</th>
			<td>
				<input style="width: 85%" type="text" name="title" value="<?php echo esc_attr( $redirect->title ); ?>"/>
				<span class="sub">(<?php _e( "optional", 'redirection' ); ?>)</span>
			</td>
		</tr>

		<tr>
			<th width="100"><a target="_blank" href="<?php echo esc_url( $redirect->url ) ?>"><?php _e( 'Source URL', 'redirection' ); ?>:</a></th>
			<td>
				<input style="width: 85%" type="text" name="old" value="<?php echo esc_attr( $redirect->url ); ?>" id="original"/>
				<label><?php _e( 'Regex', 'redirection' ); ?>: <input type="checkbox" name="regex" <?php if ( $redirect->regex == true ) echo ' checked="checked"' ?>/></label>
			</td>
		</tr>

		<?php $redirect->match->show(); ?>

		<tr>
			<th></th>
			<td>
				<input class="button-primary" type="submit" name="save" value="<?php _e( 'Save', 'redirection' ); ?>"/>
				<input class="button-secondary" type="submit" name="cancel" value="<?php _e( 'Cancel', 'redirection' ); ?>"/>

				<input type="hidden" name="action" value="red_redirect_save"/>
				<input type="hidden" name="id" value="<?php echo esc_attr( $redirect->id ); ?>"/>
				<input type="hidden" name="_ajax_nonce" value="<?php echo wp_create_nonce( 'redirection-redirect_save_'.$redirect->id ); ?>"/>

				<span id="info_<?php echo esc_attr( $redirect->id ) ?>"></span>
			</td>
		</tr>
	</table>
</form>
