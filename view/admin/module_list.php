<?php if ( !defined( 'ABSPATH' ) ) die( 'No direct access allowed' ); ?>
<div class="wrap">
	<?php screen_icon(); ?>

	<h2><?php _e( 'Modules', 'redirection' ); ?></h2>

	<?php $this->render_admin( 'submenu', array( 'options' => $options ) ); ?>

	<?php if ( count( $modules ) > 0 ) : ?>
		<table class="modules" style="clear: both" summary="modules">
			<tr>
				<th></th>
				<th class="left"><?php _e( 'Details', 'redirection' ); ?></th>
				<th><?php _e( 'Groups', 'redirection' ); ?></th>
				<th><?php _e( 'Items', 'redirection' ); ?></th>
				<th><?php _e( 'Operations', 'redirection' ); ?></th>
			</tr>

			<?php foreach ( $modules AS $pos => $module ): ?>
				<tr id="item_<?php echo $module->id ?>">
					<?php $this->render_admin( 'module_item', array( 'module' => $module, 'token' => $token ) ); ?>
				</tr>
			<?php endforeach ?>
		</table>
		<p><?php _e( 'Note: Hits are dependant on log entries', 'redirection' )?></p>
	<?php else : ?>
		<p><?php _e( 'You have no modules defined yet', 'redirection' ); ?>.</p>
	<?php endif; ?>
</div>

<script type="text/javascript">
jQuery(document ).ready( function() {
	var redirection = new Redirection( {} );
	redirection.modules();
} );
</script>
