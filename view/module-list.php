<?php if ( !defined( 'ABSPATH' ) ) die( 'No direct access allowed' ); ?>
<div class="wrap">
	<?php screen_icon(); ?>

	<h2><?php _e( 'Modules', 'redirection' ); ?></h2>

	<?php $this->render( 'submenu', array( 'options' => $options ) ); ?>

	<form method="POST" action="">
		<?php $table->display(); ?>
	</form>

	<p><?php _e( 'Note that you can use the Bulk Actions dropdown to export multiple modules.', 'redirection' ); ?></p>
</div>

<script type="text/javascript">
jQuery(document ).ready( function() {
	new Redirection_Items();
} );
</script>
