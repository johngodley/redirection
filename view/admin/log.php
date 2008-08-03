<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><div class="wrap"> <?php if ($pos % 2 == 1) echo ' class="alt"' ?>
	<h2><?php _e ('Redirection Log', 'redirection'); ?></h2>
	<?php $this->submenu (true); ?>
		<div id="pager" class="pager">
			<form method="get" action="<?php echo $this->url ($pager->url) ?>">
				<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>"/>
				<input type="hidden" name="curpage" value="<?php echo $pager->current_page () ?>"/>
				<input type="hidden" name="sub" value="<?php echo $_GET['sub'] ?>"/>

				<?php if (isset ($_GET['module'])) : ?>
					<?php _e ('Module', 'redirection'); ?>: <select name="module"><?php echo $this->select (Red_Module::get_for_select (), intval ($_GET['module']))?></select>
				<?php elseif (isset ($_GET['group'])) : ?>
					<?php _e ('Group', 'redirection'); ?>: <select name="group"><?php echo $this->select (Red_Group::get_for_select (), intval ($_GET['group']))?></select>
				<?php endif; ?>
				
				<?php _e ('Search', 'redirection'); ?>: 
				<input type="text" name="search" value="<?php echo htmlspecialchars ($_GET['search']) ?>"/>
				<?php if (isset ($_GET['search']) && $_GET['search'] != '') : ?>
					<input type="hidden" name="ss" value="<?php echo htmlspecialchars ($_GET['search']) ?>"/>
				<?php endif;?>

				<?php _e ('Per page', 'redirection') ?>: 
				<?php $pager->per_page (); ?>

				<input type="submit" name="go" value="<?php _e ('go', 'redirection') ?>"/>
			</form>
		</div>

		<br/>
		
		<?php if (count ($logs) > 0) : ?>
		<table class="stats" id="items" cellspacing="0" cellpadding="0">
		<thead>
		<tr>
			<th width="16">	<input type="checkbox" name="select_all" value="" onclick="select_all (); return true"/></th>
			<th style="width:9em"<?php $pager->sortable_class ('created') ?>><?php echo $pager->sortable ('created', __ ('Date', 'redirection')) ?></th>
			<th<?php $pager->sortable_class ('url') ?>><?php echo $pager->sortable ('url', __ ('Source URL', 'redirection')); ?></th>
			<th<?php $pager->sortable_class ('referrer') ?>><?php echo $pager->sortable ('referrer', __ ('Referrer', 'redirection')); ?></th>
			<th style="width:7em" class="center<?php $pager->sortable_class ('ip', false) ?>"><?php echo $pager->sortable ('ip', __ ('IP', 'redirection')); ?></th>
			<th style="width:16px"></th>
		</tr>
		</thead>
		
		<tfoot>
			<tr>
				<td></td>
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
			<tr id="item_<?php echo $log->id ?>" <?php if ($pos % 2 == 1) echo ' class="alt"' ?>>
				<?php $this->render_admin ('log_item', array ('log' => $log, 'pos' => $pos, 'lookup' => $lookup, 'pager' => $pager)); ?>
			</tr>
		<?php endforeach; ?>
	</table>

	<div class="pager pagertools">
		<a href="#" onclick="return select_all ()"><?php _e ('Select All', 'redirection'); ?></a> |
		<a href="#" onclick="return delete_items('log')"><?php _e ('Delete', 'redirection'); ?></a>
	</div>
	
	<?php else : ?>
	<p><?php _e ('There are no logs to display!', 'redirection'); ?></p>
	<?php endif; ?>
	
	<div style="clear: both"></div>
</div>

<?php $this->render_admin ('add', array ('hidden' => true))?>

<div class="wrap">
	<h2><?php _e ('Process Current Logs', 'redirection'); ?></h2>
	<p><?php _e ('These actions will affect all currently available logs (i.e. your search phrase will restrict the log items).', 'redirection'); ?></p>
	
	<form action="<?php echo $this->url ($_SERVER['REQUEST_URI']) ?>" method="post" accept-charset="utf-8">
		<input type="submit" name="deleteall" value="<?php _e ('Delete Logs', 'redirection'); ?>"/>
		<input type="submit" name="download" value="<?php _e ('Export to CSV', 'redirection'); ?>"/>
	</form>
</div>