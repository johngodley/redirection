<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><div class="item" style="width:70%">
	<form action="" method="post" accept-charset="utf-8" onsubmit="save_group(<?php echo $group->id ?>, this); return false">
	<table class="edit">
		<tr>
			<th width="70"><?php _e ('Name', 'redirection'); ?>:</th>
			<td><input style="width: 95%" type="text" name="name" value="<?php echo htmlspecialchars ($group->name) ?>"/></td>
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
				<input type="submit" name="save" value="<?php _e ('Save', 'redirection'); ?>"/>
				<input type="submit" name="cancel" value="<?php _e ('Cancel', 'redirection'); ?>" onclick="cancel_group(<?php echo $group->id ?>);return false"/>
				
				<span id="info_<?php echo $group->id ?>"></span>
			</td>
		</tr>
	</table>
	</form>
</div>