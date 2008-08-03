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
<form action="" method="post" accept-charset="utf-8" onsubmit="return save_module(<?php echo $module->id ?>,this)">
	<table class="edit">
		<tr>
			<th><?php _e ('Name', 'redirection'); ?>:</th>
			<td><input type="text" name="name" value="<?php echo htmlspecialchars ($module->name) ?>" style="width: 95%"/></td>
		</tr>

		<?php $module->config (); ?>
	
		<tr>
			<th></th>
			<td>
				<input type="submit" name="save" value="<?php _e ('Save', 'redirection'); ?>"/>
				<input type="submit" name="cancel" value="<?php _e ('Cancel', 'redirection'); ?>" onclick="return cancel_module(<?php echo $module->id ?>);return false"/>
				
				<span id="info_<?php echo $module->id ?>"></span>
			</td>
		</tr>
	</table>
</form>
</td>
