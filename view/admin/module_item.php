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

<td valign="top">
	<h4>
		<a href="edit.php?page=redirection.php&amp;sub=groups&amp;id=<?php echo $module->id ?>"><?php echo htmlspecialchars ($module->name); ?></a>
		<?php echo $module->name_extra (); ?>
	</h4>
	
	<?php $module->options (); ?>
	
	<?php if ($module->is_valid ()) : ?>
		<div class="toolbar">
			<strong>View as:</strong>
			
			<a href="edit.php?page=redirection.php&amp;sub=csv&amp;module=<?php echo $module->id ?>"><?php _e ('CSV', 'redirection'); ?></a>
			<a href="edit.php?page=redirection.php&amp;sub=xml&amp;module=<?php echo $module->id ?>"><?php _e ('XML', 'redirection'); ?></a>
			<a href="edit.php?page=redirection.php&amp;sub=apache&amp;module=<?php echo $module->id ?>"><?php _e ('Apache', 'redirection'); ?></a>
			<a href="edit.php?page=redirection.php&amp;sub=rss&amp;module=<?php echo $module->id ?>"><?php _e ('RSS', 'redirection'); ?></a>
		</div>
	<?php endif; ?>
</td>

<td class="center"><a href="edit.php?page=redirection.php&amp;sub=groups&amp;id=<?php echo $module->id ?>"><?php echo $module->groups (); ?></a></td>
<td class="center"><?php echo $module->redirects (); ?></td>
<td class="center"><a href="edit.php?page=redirection.php&amp;sub=log&amp;module=<?php echo $module->id ?>"><?php echo $module->hits (); ?></a></td>

<td id="info_<?php echo $module->id ?>" class="operations">
	<a href="#" onclick="return edit_module (<?php echo $module->id ?>)"><img src="<?php echo $this->url () ?>/images/edit.png" width="16" height="16" alt="Edit"/></a>
	<a href="#" onclick="return edit_module (<?php echo $module->id ?>)"><?php _e ('edit', 'redirection'); ?></a>
	
	<a href="#" onclick="return delete_module (<?php echo $module->id ?>)"><img src="<?php echo $this->url () ?>/images/delete.png" width="16" height="16" alt="Delete"/></a>
	<a href="#" onclick="return delete_module (<?php echo $module->id ?>)"><?php _e ('delete', 'redirection'); ?></a>
	
	<a href="#" onclick="return reset_module (<?php echo $module->id ?>)"><img src="<?php echo $this->url () ?>/images/delete.png" width="16" height="16" alt="Delete"/></a>
	<a href="#" onclick="return reset_module (<?php echo $module->id ?>)"><?php _e ('reset', 'redirection'); ?></a>
</td>





