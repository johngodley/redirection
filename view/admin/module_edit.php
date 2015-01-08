<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<table class="edit" data-url="<?php echo admin_url( 'admin-ajax.php' ) ?>">
	<tr>
		<th width="70"><?php _e ('Name', 'redirection'); ?>:</th>
		<td><input style="width: 95%" type="text" name="name" value="<?php echo esc_attr( $module->get_name() ) ?>"/></td>
	</tr>

	<?php $module->config(); ?>

	<tr>
		<th width="70"></th>
		<td>
			<div class="table-actions">
				<input class="button-primary" type="submit" name="save" value="<?php _e ('Save', 'redirection'); ?>"/>
				<input class="button-secondary" type="submit" name="cancel" value="<?php _e ('Cancel', 'redirection'); ?>"/>

				<input type="hidden" name="action" value="red_module_save"/>
				<input type="hidden" name="id" value="<?php echo esc_attr( $module->get_id() ); ?>"/>
				<?php wp_nonce_field( 'red_module_save_'.$module->get_id() ) ?>
			</div>

			<div class="table-loading">
				<div class="spinner"></div>
			</div>
		</td>
	</tr>
</table>
</div>
