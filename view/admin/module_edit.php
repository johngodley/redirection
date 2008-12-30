<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><td class="logo">
	<a href="edit.php?page=redirection.php&amp;sub=groups&amp;id=<?php echo $module->id ?>">
	<?php if ($module->type == 'apache') : ?>
	<img src="<?php echo $this->url () ?>/images/modules/apache.png" width="55" height="56" alt="Apache"/>
	<?php elseif ($module->type == 'wp') : ?>
	<img src="<?php echo $this->url () ?>/images/modules/wordpress.png" width="64" height="64" alt="Wordpress"/>
	<?php elseif ($module->type == '404') : ?>
	<img src="<?php echo $this->url () ?>/images/modules/404.png" width="64" height="64" alt="Wordpress"/>
	<?php endif; ?>
	</a>
</td>

<td colspan="5" class="edit">
	<form action="<?php echo $this->url (); ?>/ajax.php?id=<?php echo $module->id ?>&amp;cmd=save_module&amp;_ajax_nonce=<?php echo wp_create_nonce ('redirection-edit_module-'.$module->id); ?>" id="redirect_form_<?php echo $module->id ?>" method="post" accept-charset="utf-8">
		<table class="edit">
			<tr>
				<th><?php _e ('Name', 'redirection'); ?>:</th>
				<td><input type="text" name="name" value="<?php echo htmlspecialchars ($module->name) ?>" style="width: 95%"/></td>
			</tr>

			<?php $module->config (); ?>
	
			<tr>
				<th></th>
				<td>
					<input class="button-secondary" type="submit" name="save" value="<?php _e ('Save', 'redirection'); ?>"/>
					<input class="button-secondary" type="submit" name="cancel" value="<?php _e ('Cancel', 'redirection'); ?>" onclick="return show_module(<?php echo $module->id ?>)"/>
				
					<span id="info_<?php echo $module->id ?>"></span>
				</td>
			</tr>
		</table>
	</form>
	
	<script type="text/javascript" charset="utf-8">
		 jQuery('#redirect_form_<?php echo $module->id ?>').ajaxForm ( { beforeSubmit: function ()
				{
	  			jQuery('#info_<?php echo $module->id ?>').html (wp_progress);
				},
				success: function (response)
				{
					jQuery('#item_<?php echo $module->id ?>').html (response);
					editItems ('edit_module');
				}});
	</script>
</td>
