<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="date">
	<a href="<?php echo admin_url( 'admin-ajax.php' ); ?>?action=red_group_edit&amp;id=<?php echo $group->id; ?>&amp;_ajax_nonce=<?php echo wp_create_nonce( 'redirection-group_'.$group->id ); ?>" class="redirection-edit"><?php _e ('edit group', 'redirection'); ?></a>
</div>

<div class="count">
	<?php if ($group->tracking) : ?>
	<a href="<?php echo admin_url( 'tools.php?page=redirection.php' ) ?>?&amp;sub=log&amp;group=<?php echo $group->id ?>"><?php echo $group->hits (); ?></a>
	<?php else : ?>
		&mdash;
	<?php endif; ?>
</div>

<div class="item">
	<input class="check" type="checkbox" name="checkall[]" value="<?php echo $group->id ?>"/>
	<a href="<?php echo admin_url( 'tools.php?page=redirection.php' ) ?>&amp;sub=redirects&amp;id=<?php echo $group->id ?>">
		<?php echo esc_html( $group->name ); ?>
	</a>

	<?php echo $group->items () ?>

	<?php if ($group->status == 'disabled') : ?>
		&mdash; <?php _e ('disabled', 'redirection'); ?>
	<?php endif; ?>
</div>
