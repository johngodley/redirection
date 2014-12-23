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

<?php $this->render( 'add', array( 'add_to_screen' => true, 'group' => $group ? $group->id : 0, 'hidden' => false ) ); ?>

<script type="text/javascript">
var redirection;

jQuery(document).ready( function() {
	new Redirection_Items();
	new Redirection_Add( 'select[name=red_action]', '#target', '#add', true );
});
</script>
