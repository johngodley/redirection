<link rel="stylesheet" type="text/css" href="<?php bloginfo ('wpurl') ?>/wp-content/plugins/redirection/admin.css" />
<script type="text/javascript">
  wp_base        = '<?php echo $this->url () ?>/';
  wp_please_wait = '<?php _e ('Please wait...', 'redirection') ?>';
	wp_type        = '<?php echo $type ?>';
	wp_progress    = '<img src="<?php echo $this->url () ?>/images/progress.gif" alt="loading" width="50" height="16"/>';
</script>
<script type="text/javascript" src="<?php echo $this->url () ?>/js/prototype.js"></script>
<script type="text/javascript" src="<?php echo $this->url () ?>/js/scriptaculous.js"></script>
<script type="text/javascript" src="<?php echo $this->url () ?>/js/redirection.js"></script>
