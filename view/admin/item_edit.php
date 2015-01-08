<?php if ( !defined( 'ABSPATH' ) ) die( 'No direct access allowed' ); ?>
<table class="edit" data-url="<?php echo admin_url( 'admin-ajax.php' ) ?>">
	<tr>
		<th width="100"><a target="_blank" href="<?php echo esc_url( $redirect->url ) ?>"><?php _e( 'Source URL', 'redirection' ); ?>:</a></th>
		<td>
			<input style="width: 85%" type="text" name="old" value="<?php echo esc_attr( $redirect->url ); ?>" id="original"/>
			<label><?php _e( 'Regex', 'redirection' ); ?>: <input type="checkbox" name="regex" <?php if ( $redirect->regex == true ) echo ' checked="checked"' ?>/></label>

			<a href="#" class="advanced-toggle">&#9881;</a>
		</td>
	</tr>
	<tr class="advanced">
		<th width="100"><?php _e ('Description', 'redirection'); ?>:</th>
		<td>
			<input style="width: 85%" type="text" name="title" value="<?php echo esc_attr( $redirect->title ); ?>"/>
			<span class="sub">(<?php _e( "optional", 'redirection' ); ?>)</span>
		</td>
	</tr>

	<?php $redirect->match->show(); ?>

	<tr>
		<th></th>
		<td>
			<div class="table-actions">
				<input class="button-primary" type="submit" name="save" value="<?php _e( 'Save', 'redirection' ); ?>"/>
				<input class="button-secondary" type="submit" name="cancel" value="<?php _e( 'Cancel', 'redirection' ); ?>"/>

				<input type="hidden" name="action" value="red_redirect_save"/>
				<input type="hidden" name="id" value="<?php echo esc_attr( $redirect->id ); ?>"/>
				<?php wp_nonce_field( 'redirection-redirect_save_'.$redirect->id ) ?>

				<span id="info_<?php echo esc_attr( $redirect->id ) ?>"></span>
			</div>
			<div class="table-loading">
				<div class="spinner"></div>
			</div>
		</td>
	</tr>
</table>
