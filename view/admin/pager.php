<div id="pager" class="pager" style="<?php if ($visible == false) echo 'display: none' ?>">
	<form method="get" action="<?php echo $this->url ($pager->url) ?>">
		<?php if (!is_array ($page_url)) : ?>
		<input type="hidden" name="page" value="<?php echo $page_url ?>"/>
		<?php else : ?>
		<?php foreach ($page_url AS $key => $page) : ?>
		<input type="hidden" name="<?php echo $key ?>" value="<?php echo $page ?>"/>
		<?php endforeach; ?>
		<?php endif; ?>
		<input type="hidden" name="curpage" value="<?php echo $pager->current_page () ?>"/>
		<input type="hidden" name="sub" value="<?php echo $_GET['sub'] ?>"/>

		<?php _e ('Search', 'redirection'); ?>: 
		<input type="text" name="search" value="<?php echo htmlspecialchars ($_GET['search']) ?>"/>

		<?php _e ('Per page', 'redirection') ?>: 
		<?php $pager->per_page (); ?>
		
		<input type="submit" name="go" value="<?php _e ('go', 'redirection') ?>"/>
	</form>
</div>
