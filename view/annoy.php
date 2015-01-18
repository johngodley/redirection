<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<?php
	$options = $this->get_options ();

	if (!$options['support']) :?>
	<div style="text-align: center; width: 94px; height: 32px; float: right; margin: 5px 15px 1px 0; padding: 4px 3px 0px 3px;-moz-border-radius: 5px; -webkit-border-radius: 5px;" id="support-annoy">
	<a href="<?php echo admin_url( 'tools.php?page=redirection.php&amp;sub=support' ); ?>">
		<img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" alt="support" />
	</a>
</div>
<?php endif; ?>
