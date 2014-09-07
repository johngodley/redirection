<?php if ( !defined( 'ABSPATH' ) ) die( 'No direct access allowed' ); ?>
<div class="wrap">
	<?php screen_icon(); ?>

	<h2><?php _e( 'Modules', 'redirection' ); ?></h2>

	<?php $this->render_admin( 'submenu', array( 'options' => $options ) ); ?>

	<form method="POST" action="">
		<?php $table->display(); ?>
	</form>
</div>

<script type="text/javascript">
jQuery(document ).ready( function() {
	new Redirection_Items();
} );
</script>
