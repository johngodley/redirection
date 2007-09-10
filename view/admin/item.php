<div class="tools">
	<a href="#" onclick="delete_redirect (<?php echo $redirect->id ?>); return false"><img src="<?php echo $this->url () ?>/images/delete.png" width="16" height="16" alt="delete"/></a>
</div>

<div class="date" id="d_<?php echo $redirect->id ?>">
<?php if ($redirect->last_access == 0) : ?>
&nbsp;
<?php else : ?>
	<?php echo date ($date_format, $redirect->last_access) ?>
<?php endif; ?>
</div>

<div class="count">
	<a href="#" title="<?php _e ('Reset', 'redirection'); ?>" onclick="reset_redirect(<?php echo $redirect->id ?>); return false"><?php echo $redirect->last_count ?></a>
</div>

<div class="type">
	<?php echo $redirect->type ?>
</div>

<div class="item">
<?php if ($redirect->regex) : ?><em><?php endif; ?>
	<a href="<?php echo $this->url ($redirect->url) ?>" onclick="show_redirect(<?php echo $redirect->id ?>); return false"><?php echo RE_Log::show_url ($redirect->url); ?></a>
<?php if ($redirect->regex) : ?></em><?php endif; ?>
</div>
