<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="item" style="width:70%">
	<form method="post" accept-charset="utf-8" action="<?php echo $this->url (); ?>/ajax.php?id=<?php echo $group->id ?>&amp;cmd=save_group&amp;_ajax_nonce=<?php echo wp_create_nonce ('redirection-save_group')?>" id="redirect_form_<?php echo $group->id ?>">
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
					<input class="button-primary" type="submit" name="save" value="<?php _e ('Save', 'redirection'); ?>"/>
					<input class="button-secondary" type="submit" name="cancel" value="<?php _e ('Cancel', 'redirection'); ?>" onclick="return show_group(<?php echo $group->id ?>);"/>
				
					<span id="info_<?php echo $group->id ?>"></span>
				</td>
			</tr>
		</table>
	</form>
</div>

<script type="text/javascript" charset="utf-8">
	 jQuery('#redirect_form_<?php echo $group->id ?>').ajaxForm ( { beforeSubmit: function ()
			{
  			jQuery('#info_<?php echo $group->id ?>').html (wp_progress);
			},
			success: function (response)
			{
				jQuery('#item_<?php echo $group->id ?>').html (response);
				editItems ('edit_group');
			}});
</script>
