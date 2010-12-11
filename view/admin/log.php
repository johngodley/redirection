<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php screen_icon(); ?>
	<?php $this->render_admin('annoy')?>

	<h2><?php _e ('Redirection Log', 'redirection'); ?></h2>

	<?php $this->render_admin( 'submenu' ); ?>

	<form method="get" action="<?php echo $this->url ($pager->url) ?>">
		<input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ) ?>"/>
		<input type="hidden" name="curpage" value="<?php echo $pager->current_page () ?>"/>
		<input type="hidden" name="sub" value="<?php echo esc_attr( $_GET['sub'] ) ?>"/>

		<p class="search-box">
			<label for="post-search-input" class="hidden"><?php _e ('Search', 'redirection') ?>:</label>

			<input type="text" class="search-input" name="search" value="<?php echo isset($_GET['search']) ? esc_attr( $_GET['search'] ) : ''?>"/>
			<?php if (isset ($_GET['search']) && $_GET['search'] != '') : ?>
				<input type="hidden" name="ss" value="<?php echo esc_attr( $_GET['search'] ) ?>"/>
			<?php endif;?>

			<input type="submit" class="button" value="<?php _e ('Search', 'redirection'); ?>"/>
		</p>
	
		<div id="pager" class="tablenav">
			<div class="alignleft actions">
				<select name="action2" id="action2_select">
					<option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
					<option value="delete"><?php _e('Delete'); ?></option>
				</select>
				
				<input type="submit" value="<?php _e('Apply'); ?>" name="doaction2" id="actionator" class="button-secondary action" />

				<?php $pager->per_page ('redirection'); ?>

				<?php if (isset ($_GET['module'])) : ?>
					<?php _e ('Module', 'redirection'); ?>:
					<select name="module">
						<?php echo $this->select (Red_Module::get_for_select (), intval ($_GET['module']))?>
					</select>
				<?php elseif (isset ($_GET['group'])) : ?>
					<?php _e ('Group', 'redirection'); ?>:
					<select name="group">
						<?php echo $this->select (Red_Group::get_for_select (), intval ($_GET['group']))?>
					</select>
				<?php endif; ?>

				<input type="submit" value="<?php _e('Filter'); ?>" class="button-secondary" />

				<br class="clear" />
			</div>
		
			<div class="tablenav-pages">
				<?php echo $pager->page_links (); ?>
			</div>
		</div>
	</form>

	<?php if (count ($logs) > 0) : ?>
		<table class="widefat post fixed" id="items" cellspacing="0" cellpadding="0">
			<thead>
			<tr>
				<th width="16" id="cb" class="manage-column column-cb check-column">
					<input type="checkbox" name="checkall" value=""/>
				</th>
				<th style="width:9em"<?php $pager->sortable_class ('created') ?>><?php echo $pager->sortable ('created', __ ('Date', 'redirection')) ?></th>
				<th><?php echo $pager->sortable ('url', __ ('Source URL', 'redirection')); ?></th>
				<th><?php echo $pager->sortable ('referrer', __ ('Referrer', 'redirection')); ?></th>
				<th style="width:9em" class="center<?php $pager->sortable_class ('ip', false) ?>"><?php echo $pager->sortable ('ip', __ ('IP', 'redirection')); ?></th>
				<th style="width:16px"></th>
			</tr>
			</thead>
		
			<tbody>
			<?php foreach ($logs AS $pos => $log) : ?>
				<tr id="item_<?php echo $log->id ?>" <?php if ($pos % 2 == 1) echo ' class="alt"' ?>>
					<?php $this->render_admin ('log_item', array ('log' => $log, 'pos' => $pos, 'lookup' => $lookup, 'pager' => $pager)); ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

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
		<?php wp_nonce_field ('redirection-process_logs'); ?>
		
		<input class="button-primary" type="submit" name="deleteall" value="<?php _e ('Delete Logs', 'redirection'); ?>"/>
	</form>
</div>

<script type="text/javascript">
var redirection;

jQuery(document).ready( function() {
	redirection = new Redirection( {
		progress: '<img src="<?php echo $this->url () ?>/images/progress.gif" alt="loading" width="50" height="16"/>',
		ajaxurl: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
		nonce: '<?php echo wp_create_nonce( 'redirection-items' ); ?>',
		none_select: '<?php echo esc_js( __( 'No items have been selected', 'redirection' ) ); ?>',
		are_you_sure: '<?php echo esc_js( __( 'Are you sure?', 'redirection') ); ?>',
	});
	redirection.logs();
});
</script>
