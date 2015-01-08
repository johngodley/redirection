<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<table class="edit" data-url="<?php echo admin_url( 'admin-ajax.php' ) ?>">
	<tr>
		<th width="70"><?php _e ('Name', 'redirection'); ?>:</th>
		<td><input style="width: 95%" type="text" name="name" value="<?php echo esc_attr( $group->get_name() ) ?>"/></td>
	</tr>
	<tr>
		<th width="70"><?php _e ('Tracked', 'redirection'); ?>:</th>
		<td><label><input type="checkbox" name="tracking"<?php echo checked ($group->tracking) ?>/> <span class="sub"><?php _e ('Whether to track \'hits\' to items', 'redirection'); ?></span></label></td>
	</tr>
	<tr>
		<th width="70"><?php _e ('Enabled', 'redirection'); ?>:</th>
		<td><label><input type="checkbox" name="status"<?php if ($group->status == 'enabled') echo ' checked="checked"' ?>/> <span class="sub"><?php _e ('Disabling a group will disable all items contained within it', 'redirection'); ?></span></label></td>
	</tr>
	<tr>
		<th width="70"></th>
		<td>
			<div class="table-actions">
				<input class="button-primary" type="submit" name="save" value="<?php _e ('Save', 'redirection'); ?>"/>
				<input class="button-secondary" type="submit" name="cancel" value="<?php _e ('Cancel', 'redirection'); ?>"/>

				<input type="hidden" name="action" value="red_group_save"/>
				<input type="hidden" name="id" value="<?php echo esc_attr( $group->id ); ?>"/>
				<?php wp_nonce_field( 'redirection-group_save_'.$group->id ) ?>
			</div>

			<div class="table-loading">
				<div class="spinner"></div>
			</div>
		</td>
	</tr>
</table>
</div>
