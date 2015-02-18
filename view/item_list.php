<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php screen_icon(); ?>

	<h2><?php _e( 'Redirections', 'redirection' ); ?>:</h2>

	<?php $this->render( 'submenu', array( 'options' => $options ) ); ?>

	<form action="tools.php">
		<input type="hidden" name="page" value="redirection.php"/>

		<?php $table->search_box( __( 'Search' ), 'search_id' ); ?>
	</form>

	<form method="POST" action="">
		<?php $table->display(); ?>
	</form>

	<div style="clear: both"></div>
</div>

<?php $this->render( 'add', array( 'add_to_screen' => true, 'group' => $group, 'hidden' => false ) ); ?>

<script type="text/javascript">
jQuery(document).ready( function() {
	var items = new Redirection_Items();
	var add = new Redirection_Add( 'select[name=red_action]', '#target', '#add', true );
});
</script>
