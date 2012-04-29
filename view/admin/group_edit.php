<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="item" style="width:70%">
	<form method="post" accept-charset="utf-8" action="<?php echo admin_url( 'admin-ajax.php' ) ?>">
		<table class="edit">
			<tr>
				<th width="70"><?php _e ('Name', 'redirection'); ?>:</th>
				<td><input style="width: 95%" type="text" name="name" value="<?php echo esc_attr( $group->name ) ?>"/></td>
			</tr>
			<tr>
				<th width="70"><?php _e ('Tracked', 'redirection'); ?>:</th>
				<td><label><input type="checkbox" name="tracking"<?php echo $this->checked ($group->tracking) ?>/> <span class="sub"><?php _e ('Whether to track \'hits\' to items', 'redirection'); ?></span></label></td>
			</tr>
			<tr>
				<th width="70"><?php _e ('Enabled', 'redirection'); ?>:</th>
				<td><label><input type="checkbox" name="status"<?php if ($group->status == 'enabled') echo ' checked="checked"' ?>/> <span class="sub"><?php _e ('Disabling a group will disable all items contained within it', 'redirection'); ?></span></label></td>
			</tr>
			<tr>
				<th width="70"></th>
				<td>
					<input class="button-primary" type="submit" name="save" value="<?php _e ('Save', 'redirection'); ?>"/>
					<input class="button-secondary" type="submit" name="cancel" value="<?php _e ('Cancel', 'redirection'); ?>"/>
					<img class="loader" src="<?php echo $this->url(); ?>/images/progress.gif" alt="loading" width="50" height="16" style="display: none"/>

					<input type="hidden" name="action" value="red_group_save"/>
					<input type="hidden" name="id" value="<?php echo $group->id; ?>"/>
					<input type="hidden" name="_ajax_nonce" value="<?php echo wp_create_nonce ('redirection-group_save_'.$group->id); ?>"/>
				</td>
			</tr>
		</table>
	</form>
</div>
