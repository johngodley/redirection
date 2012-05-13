<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php screen_icon(); ?>
	<?php $this->render_admin('annoy')?>

	<h2><?php _e ('Redirection Log', 'redirection'); ?></h2>

	<?php $this->render_admin( 'submenu', array( 'options' => $options ) ); ?>

	<form method="POST" action="">
		<?php $table->search_box( __( 'Search' ), 'search_id' ); ?>
		<?php $table->display(); ?>
	</form>

	<div style="clear: both"></div>
</div>

<?php $this->render_admin ('add', array ('hidden' => true))?>

<div class="wrap">
	<h2><?php _e ('Process Current Logs', 'redirection'); ?></h2>
	<p><?php _e ('These actions will affect all currently available logs (i.e. your search phrase will restrict the log items).', 'redirection'); ?></p>

	<form action="" method="post" accept-charset="utf-8">
		<?php wp_nonce_field ('redirection-process_logs'); ?>

		<?php if ( isset( $_POST['s'] ) ): ?>
			<input type="hidden" name="s" value="<?php echo esc_attr( $_POST['s'] ); ?>" />
		<?php endif; ?>

		<input class="button-primary" type="submit" name="deleteall" value="<?php _e ('Delete Logs', 'redirection'); ?>"/>
	</form>
</div>

<script type="text/javascript">
var redirection;

jQuery(document).ready( function() {
	redirection = new Redirection( {
		progress: '<img src="<?php echo $this->url () ?>/images/progress.gif" alt="loading" width="50" height="16"/>',
		ajaxurl: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
		nonce: '<?php echo wp_create_nonce( 'redirection-items' ); ?>',
		none_select: '<?php echo esc_js( __( 'No items have been selected', 'redirection' ) ); ?>',
		are_you_sure: '<?php echo esc_js( __( 'Are you sure?', 'redirection') ); ?>',
	});
	redirection.logs();
});
</script>
