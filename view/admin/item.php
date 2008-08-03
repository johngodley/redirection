<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><div class="date" id="info_<?php echo $redirect->id ?>" style="width:8em">
<?php if ($redirect->last_access == 0) : ?>
&mdash;
<?php else : ?>
	<?php echo date (str_replace ('F', 'M', get_option ('date_format')), $redirect->last_access) ?>
<?php endif; ?>
</div>

<div class="count">
	<a href="edit.php?page=redirection.php&amp;sub=log&amp;redirect=<?php echo $redirect->id ?>"><?php echo $redirect->last_count ?></a>
</div>

<div class="type">
	<?php echo $redirect->type () ?>
</div>

<div class="item">
	<input type="checkbox" class="check" name="select[]" value="<?php echo $redirect->id ?>"/>
	
<?php if ($redirect->regex) : ?><em><?php endif; ?>
	<a href="<?php echo $this->url ($redirect->url) ?>" onclick="show_redirect(<?php echo $redirect->id ?>); return false"><?php echo RE_Log::show_url ($redirect->url); ?></a>
<?php if ($redirect->regex) : ?></em><?php endif; ?>
	
	<?php if ($redirect->match_type != 'url') echo '('.$redirect->match_name ().')' ?>
	
	<?php if ($redirect->status == 'disabled') : ?>
		&mdash; <?php _e ('disabled', 'redirection'); ?>
	<?php endif; ?>
</div>
