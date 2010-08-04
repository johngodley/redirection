<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<?php
	$options = $this->get_options ();
	$sub = isset ($_GET['sub']) ? $_GET['sub'] : '';
	$url = explode ('&', $_SERVER['REQUEST_URI']);
	$url = $url[0];
	
	if (!$options['support']) :?>
	<div style="text-align: center; width: 80px; height: 50px; float: right; margin: 5px 15px 1px 0; padding: 4px 3px 0px 3px;-moz-border-radius: 5px; -webkit-border-radius: 5px;" id="support-annoy">
	<a href="<?php echo $this->base(); ?>?page=redirection.php&amp;sub=support"><img src="<?php echo $this->url () ?>/images/donate.gif" alt="support" /></a>
</div>
<?php endif; ?>