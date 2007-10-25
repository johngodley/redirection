<div class="wrap">
	<div class="options">
		<a href="edit.php?page=redirection.php&amp;sub=RSS">RSS</a>
	</div>
	<h2><?php _e ('Redirection 404 Log', 'redirection'); ?></h2>
	
	<?php if (count ($logs) > 0) : ?>

	<?php $this->render_admin ('pager', array ('pager' => $pager, 'page_url' => array ('page' => 'redirection.php', 'sub' => 'log'), 'visible' => $pager->total_pages () > 1)); ?>	
	<table class="stats">
		<thead>
		<tr>
			<th><?php echo $pager->sortable ('created', 'Date') ?></th>
			<th><?php echo $pager->sortable ('url', 'Source URL'); ?></th>
			<th><?php echo $pager->sortable ('referrer', 'Referrer'); ?></th>
			<th><?php echo $pager->sortable ('ip', 'IP'); ?></th>
			<th width="38"/>
		</tr>
		</thead>
		
		<tfoot>
			<tr>
				<td colspan="5">
					<?php if ($pager->total_pages () > 1) : ?>
						<?php foreach ($pager->area_pages () AS $page) : ?>
							<?php echo $page ?>
						<?php endforeach; ?>
					<?php else : ?>&nbsp;
					<?php endif; ?>
				</td>
			</tr>
		</tfoot>
		
		<?php foreach ($logs AS $pos => $log) : ?>
		<tr id="r_<?php echo $log->id ?>" <?php if ($pos % 2 == 1) echo ' class="alt"' ?>>
			<?php $this->render_admin ('404_item', array ('log' => $log, 'lookup' => get_option ('redirection_lookup'))); ?>
		</tr>
		<?php endforeach; ?>
	</table>

	<?php else : ?>
	<p><?php _e ('You have no 404 logs!', 'redirection'); ?></p>
	<?php endif; ?>
	
	<div id="loading" style="display: none">
		<img src="<?php echo $this->url () ?>/images/loading.gif" alt="loading" width="32" height="32"/>
	</div>
</div>

<?php if (count ($logs) > 0) : ?>
<?php $this->render_admin ('add', array ('redirectors' => $redirectors, 'add_to_screen' => false)) ?>

<div class="wrap">
	<h2><?php _e ('Delete All 404s', 'redirection'); ?></h2>
	<p><?php _e ('This will delete all logged 404 errors.  Please be sure this is what you want to do.', 'redirection'); ?></p>
	<form action="<?php echo $this->url ($_SERVER['REQUEST_URI']) ?>" method="post" accept-charset="utf-8">
		<input type="submit" name="delete" value="Delete 404s" id="delete" onclick="if (confirm ('<?php _e ('Are you sure?', 'redirection'); ?>')) return true; else return false"/>
	</form>
</div>
<?php endif; ?>