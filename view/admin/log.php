<div class="wrap"> <?php if ($pos % 2 == 1) echo ' class="alt"' ?>
	<h2><?php _e ('Redirection Log', 'redirection'); ?></h2>
	
	<?php if (count ($logs) > 0) : ?>

	<?php $this->render_admin ('pager', array ('pager' => $pager, 'page_url' => array ('page' => 'redirection.php', 'sub' => 'log'), 'visible' => $pager->total_pages () > 1)); ?>	
	<table class="stats">
		<thead>
		<tr>
			<th><?php echo $pager->sortable ('created', 'Date') ?></th>
			<th><?php echo $pager->sortable ('url', 'Source URL'); ?></th>
			<th><?php echo $pager->sortable ('referrer', 'Referrer'); ?></th>
			<th><?php echo $pager->sortable ('ip', 'IP'); ?></th>
			<th width="16"/>
		</tr>
		</thead>
		
		<tfoot>
			<tr>
				<td colspan="5">
					<?php if ($pager->total_pages () > 1) : ?>
					<?php foreach ($pager->area_pages () AS $page) : ?>
						<?php echo $page ?>
					<?php endforeach; ?>
					<?php else : ?>
					&nbsp;
					<?php endif; ?>
				</td>
			</tr>
		</tfoot>
		
		<?php foreach ($logs AS $pos => $log) : ?>
			<tr id="r_<?php echo $log->id ?>" <?php if ($pos % 2 == 1) echo ' class="alt"' ?>>
				<?php $this->render_admin ('log_item', array ('log' => $log, 'pos' => $pos, 'lookup' => get_option ('redirection_lookup'))); ?>
			</tr>
		<?php endforeach; ?>
	</table>

	<?php else : ?>
	<p><?php _e ('You have no logs!', 'redirection'); ?></p>
	<?php endif; ?>
	
	<div id="loading" style="display: none">
		<img src="<?php echo $this->url () ?>/images/loading.gif" alt="loading" width="32" height="32"/>
	</div>
</div>

<div class="wrap">
	<h2>Delete All Logs</h2>
	<form action="<?php echo $this->url ($_SERVER['REQUEST_URI']) ?>" method="post" accept-charset="utf-8">
		<input type="submit" name="deleteall" value="<?php _e ('Delete All Logs', 'redirection'); ?>"/>
	</form>
</div>