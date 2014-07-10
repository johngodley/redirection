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
		redirection = new Redirection( {
			progress: '<img src="<?php echo $this->url () ?>/images/progress.gif" alt="loading" width="50" height="16"/>',
			ajaxurl: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
			nonce: '<?php echo wp_create_nonce( 'redirection-items' ); ?>',
			none_select: '<?php echo esc_js( __( 'No items have been selected', 'redirection' ) ); ?>',
			are_you_sure: '<?php echo esc_js( __( 'Are you sure?', 'redirection') ); ?>',
		});

		redirection.logs();
	});
})(jQuery);
</script>
