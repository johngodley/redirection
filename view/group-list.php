<?php if ( !defined( 'ABSPATH' ) ) die( 'No direct access allowed' ); ?>
<div class="wrap">
	<?php screen_icon(); ?>

	<h2><?php _e( 'Groups', 'redirection' ); ?></a></h2>

	<?php $this->render( 'submenu', array( 'options' => $options ) ); ?>

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

	<p><?php _e( 'Use groups to organise your redirects. Groups are assigned to a module, which affects how the redirects in that group work. If you are unsure then stick to the WordPress module.', 'redirection' ); ?></p>

	<form action="" method="post" accept-charset="utf-8">
		<?php wp_nonce_field( 'redirection-add_group' ); ?>
		<table class="form-table">
			<tr>
				<th style="width: 50px"><?php _e( 'Name', 'redirection' ); ?>:</th>
				<td>
					<input size="30" class="regular-text" type="text" name="name" value=""/>

					<select name="module_id">
						<?php foreach ( Red_Module::get_for_select() AS $module ) : ?>
							<option value="<?php echo esc_attr( $module->get_id() ); ?>"><?php echo esc_html( $module->get_name() ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th style="width: 50px"></th>
				<td>
					<input class="button-primary" type="submit" name="add" value="<?php esc_attr_e( 'Add', 'redirection' ); ?>"/>
				</td>
			</tr>
		</table>
	</form>
</div>

<script type="text/javascript">
( function( $ ) {
	$( document ).ready( function() {
		var items = new Redirection_Items( $ );
		var adder = new Redirection_Add( $, '#target', true );

		adder.setup( 'select[name=red_action]', '#add' );
		items.setup( 'table.items' );
	} );
} )( jQuery );
</script>
