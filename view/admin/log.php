<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php screen_icon(); ?>

	<h2><?php _e ('Redirection Log', 'redirection'); ?></h2>

	<?php $this->render_admin( 'submenu', array( 'options' => $options ) ); ?>

	<form action="tools.php">
		<input type="hidden" name="page" value="redirection.php"/>
		<input type="hidden" name="sub" value="<?php echo esc_attr( $type ); ?>"/>

		<?php $table->search_box( __( 'Search' ), 'search_id' ); ?>
	</form>

	<form method="POST" action="">
		<?php $table->display(); ?>

		<h3><?php _e( 'Log Management', 'redirection' ); ?></h3>

		<?php wp_nonce_field( 'redirection-log_management' ); ?>

		<p><?php _e( 'These apply to the current search term, if any, otherwise all logs.', 'redirection' ); ?></p>
		<p>
			<input class="button action" type="submit" name="delete-all" value="<?php esc_attr_e( 'Delete All' ); ?>"/>
		</p>
		<p>
			<input class="button action" type="submit" name="export-csv" value="<?php esc_attr_e( 'Export To CSV' ); ?>"/>
		</p>
	</form>

	<div style="clear: both"></div>
</div>

<?php $this->render_admin ('add', array ('hidden' => true))?>

<script type="text/javascript">
var redirection;

(function($) {
	$(document).ready( function() {
		new Redirection_Logs();
		new Redirection_Add( 'select[name=red_action]', '#target', '#add', false );
	});
})(jQuery);
</script>
