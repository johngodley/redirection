<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<script type="text/javascript">
  wp_please_wait = '<?php echo esc_js( __( 'Please wait...', 'redirection' ) ) ?>';
	wp_type        = '<?php echo esc_js( $type ) ?>';
	wp_progress    = '<?php echo esc_js( '<img src="'.$this->url().'/images/progress.gif" alt="loading" width="50" height="16"/>' ) ?>';
	wp_are_you_sure = '<?php echo esc_js( __( 'Are you sure?', 'redirection' ) ); ?>';
	wp_none_select  = '<?php echo esc_js(  __( 'No items have been selected', 'redirection' ) ); ?>';
</script>

<?php global $is_IE;if ($is_IE) : ?>
<style type="text/css" media="screen">
.item, .date, .count, .tools, .type { margin-bottom: -2px; }
.item { margin-top: -4px;}
.pager input {padding: 1px;}
</style>
<?php endif; ?>