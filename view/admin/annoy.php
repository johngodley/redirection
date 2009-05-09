<?php
	$options = $this->get_options ();
	$sub = isset ($_GET['sub']) ? $_GET['sub'] : '';
	$url = explode ('&', $_SERVER['REQUEST_URI']);
	$url = $url[0];
	
	if (!$options['support']) :?>
<div style="text-align: center; width: 200px; float: right; margin-top: 5px; padding-bottom: 1px">
	<img src="<?php echo $this->url () ?>/images/donate.gif" alt="support" /><br/>
</div>
<?php endif; ?>