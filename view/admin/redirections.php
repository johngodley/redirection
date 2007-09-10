<div class="wrap">
	<h2><?php printf (__ ('%s Redirections', 'redirection'), ucfirst ($type)) ?></h2>

	<?php $this->render_admin ('pager', array ('pager' => $pager, 'page_url' => 'redirection.php', 'visible' => true)); ?>	
	
	<ul id="redirections_header" class="redirections_header" style="<?php if (count ($items) <= 1) echo 'display: none' ?>">
		<li>
			<div class="tools" style="width: 16px">&nbsp;</div>
			<div class="date"><?php echo $pager->sortable ('last_access', 'Last Access') ?></div>
			<div class="count"><?php echo $pager->sortable ('last_count', 'Count') ?></div>
			<div class="type"><?php echo $pager->sortable ('type', 'Type', false) ?></div>
			<div class="item"><?php echo $pager->sortable ('url', 'URL')  ?> / <?php echo $pager->sortable ('position', 'Position')  ?></div>
		</li>
	</ul>
	
	<ul class="redirections" id="redirections">
	<?php if (is_array ($items) && count ($items) > 0) : ?>
		<?php foreach ($items AS $redirect) : ?>
		<li class="type_<?php echo $redirect->type ?>" id="r_<?php echo $redirect->id ?>"><?php $this->render_admin ('item', array ('redirect' => $redirect, 'date_format' => $date_format)) ?></li>
		<?php endforeach; ?>
	<?php endif; ?>
	</ul>
	
	<?php if ($pager->total_pages () > 0) : ?>
	<div class="pagertools">
	<?php foreach ($pager->area_pages () AS $page) : ?>
		<?php echo $page ?>
	<?php endforeach; ?>
	</div>
	<?php endif; ?>

	
	<div class="pagertools" id="save_order" style="<?php if (count ($items) <= 1) echo 'display: none' ?>">
		<a href="#" onclick="save_order('<?php echo urlencode ($_SERVER['REQUEST_URI']) ?>'); return false;">save order</a>
	</div>
	
	<?php $this->render_admin ('sortables'); ?>

	<?php if (!is_array ($items) || count ($items) == 0) : ?>
	  <p id="none"><?php _e ('You have no redirections.', 'redirection') ?></p>
	<?php endif; ?>
	
	<div id="loading" style="display: none">
		<img src="<?php echo $this->url () ?>/images/loading.gif" alt="loading" width="32" height="32"/>
	</div>
	
	<img src="<?php echo $this->url () ?>/images/progress.gif" width="50" height="16" alt="Progress" style="display: none"/>
	
	<div style="clear: both"></div>
</div>

<?php $this->render_admin ('add', array ('redirectors' => $redirectors, 'add_to_screen' => true)) ?>