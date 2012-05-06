<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<div class="date" id="info_<?php echo $redirect->id ?>" style="width:8em">
	<?php if ($redirect->last_access == 0) : ?>
	&mdash;
	<?php else : ?>
		<?php echo date (str_replace ('F', 'M', get_option ('date_format')), $redirect->last_access) ?>
	<?php endif; ?>
</div>

<div class="count">
	<a href="<?php echo admin_url( 'tools.php?page=redirection.php' ); ?>&amp;sub=log&amp;redirect=<?php echo $redirect->id ?>"><?php echo $redirect->last_count ?></a>
</div>

<div class="type">
	<?php echo $redirect->type () ?>
</div>

<div class="item<?php if ($redirect->regex)  echo ' item-regex' ?>">
	<input type="checkbox" class="check" name="checkall[]" value="<?php echo $redirect->id ?>"/>

	<a href="<?php echo $this->url ($redirect->url) ?>" class="redirection-edit"><?php if ($redirect->title) echo esc_html( $redirect->title ); else echo RE_Log::show_url ($redirect->url); ?></a>

	<?php if ($redirect->match_type != 'url') echo '('.esc_html( $redirect->match_name() ).')' ?>

	<?php if ($redirect->status == 'disabled') : ?>
		&mdash; <?php _e ('disabled', 'redirection'); ?>
	<?php endif; ?>
</div>
