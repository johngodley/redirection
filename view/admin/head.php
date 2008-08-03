<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><link rel="stylesheet" href="<?php echo $this->url () ?>/admin.css" type="text/css" media="screen" title="no title" charset="utf-8"/>

<script type="text/javascript">
  wp_base        = '<?php echo $this->url () ?>/';
  wp_please_wait = '<?php _e ('Please wait...', 'redirection') ?>';
	wp_type        = '<?php echo $type ?>';
	wp_progress    = '<img src="<?php echo $this->url () ?>/images/progress.gif" alt="loading" width="50" height="16"/>';
	wp_red_select  = true;
</script>

<script type="text/javascript" src="<?php echo $this->url () ?>/js/prototype.js"></script>
<script type="text/javascript" src="<?php echo $this->url () ?>/js/scriptaculous.js?load=builder,effects,dragdrop"></script>
<script type="text/javascript" src="<?php echo $this->url () ?>/js/redirection.js"></script>

<?php global $is_IE;if ($is_IE) : ?>
<style type="text/css" media="screen">
.item, .date, .count, .tools, .type { margin-bottom: -2px; }
.item { margin-top: -4px;}
.pager input {padding: 1px;}
</style>
<?php endif; ?>