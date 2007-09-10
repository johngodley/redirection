<td><a href="#" onclick="show_404(<?php echo $log->id ?>); return false"><?php echo date (get_option ('date_format'), $log->created) ?></a></td>
<td>
	<a id="u_<?php echo $log->id ?>" href="<?php echo $this->url (stripslashes ($log->url)) ?>"><?php echo stripslashes ($log->show_url ($log->url)) ?></a>
</td>
<td>
	<?php if (strlen ($log->referrer) > 0) : ?>
	<a href="<?php echo $this->url ($log->referrer) ?>"><?php echo $log->show_url ($log->referrer ()) ?></a>
	<?php endif; ?>
</td>
<td><a target="_blank" href="<?php echo $lookup.$log->ip ?>"><?php echo htmlspecialchars ($log->ip) ?></a></td>
<td width="38">
	<a href="#" title="<?php _e ('Add redirection', 'redirection'); ?>" onclick="add_404(<?php echo $log->id ?>); return false"><img src="<?php echo $this->url () ?>/images/add.png" width="16" height="16" alt="Add"/></a>
	<a href="#" title="<?php _e ('Delete', 'redirection'); ?>" onclick="delete_log(<?php echo $log->id ?>); return false"><img src="<?php echo $this->url () ?>/images/delete.png" width="16" height="16" alt="Delete"/></a>
</td>
