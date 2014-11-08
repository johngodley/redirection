<?php if ( !defined( 'ABSPATH' ) ) die( 'No direct access allowed' ); ?>
<div class="wrap">
	<?php screen_icon(); ?>

	<h2><?php _e( 'Groups', 'redirection' ); ?></a></h2>

	<?php $this->render_admin( 'submenu', array( 'options' => $options ) ); ?>

	<form action="tools.php">
		<input type="hidden" name="page" value="redirection.php"/>
		<input type="hidden" name="sub" value="groups"/>

		<?php $table->search_box( __( 'Search' ), 'search_id' ); ?>
	</form>

	<form method="POST" action="">
		<?php $table->display(); ?>
	</form>
</div>

<div class="wrap">
	<h2><?php _e( 'Add Group', 'redirection' ); ?></h2>

	<form action="" method="post" accept-charset="utf-8">
		<?php wp_nonce_field( 'redirection-add_group' ); ?>
		<table class="form-table">
			<tr>
				<th width="50"><?php _e( 'Name', 'redirection' ); ?>:</th>
				<td><input size="40" class="regular-text" type="text" name="name" value=""/></td>
			</tr>
			<tr>
				<th width="50"></th>
				<td>
					<input class="button-primary" type="submit" name="add" value="<?php esc_attr_e( 'Add', 'redirection' ); ?>"/>
					<input type="hidden" name="module_id" value="<?php echo $module->get_id() ?>"/>
				</td>
			</tr>
		</table>
	</form>
</div>

<script type="text/javascript">
var redirection;

jQuery(document).ready( function() {
	new Redirection_Items();
	new Redirection_Add( 'select[name=red_action]', '#target', '#add', true );
});
</script>
