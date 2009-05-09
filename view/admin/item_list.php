<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php screen_icon(); ?>
	<?php $this->render_admin('annoy')?>
	
	<h2><?php _e ('Redirections for group', 'redirection'); ?>: <a href="<?php echo $this->base (); ?>?page=redirection.php&amp;sub=groups&amp;id=<?php echo $group->module_id ?>"><?php echo htmlspecialchars ($group->name); ?></a></h2>
		
	<?php $this->submenu (true); ?>
	
	<div id="pager" class="pager">
		<form method="get" action="<?php echo $this->url ($pager->url) ?>">
			<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>"/>
			<input type="hidden" name="curpage" value="<?php echo $pager->current_page () ?>"/>
			<input type="hidden" name="sub" value="<?php echo $_GET['sub'] ?>"/>

			<?php _e ('Group', 'redirection'); ?>:
			<select name="id">
				<?php echo $this->select ($groups, $_GET['id'])?>
			</select>
			
			<?php _e ('Search', 'redirection'); ?>: 
			<input type="text" class="search-input"  name="search" value="<?php echo htmlspecialchars ($_GET['search']) ?>" style="font-size: 0.8em"/>

			<?php $pager->per_page ('redirection'); ?>

			<input class="button-secondary" type="submit" name="go" value="<?php _e ('Go', 'redirection') ?>"/>
		</form>
	</div>
	<br/>
	
	<ul id="redirections_header" class="redirections_header">
		<li>
			<div class="date" style="width: 8em"><?php echo $pager->sortable ('last_access', __ ('Last Access', 'redirection')) ?></div>
			<div class="count"><?php echo $pager->sortable ('last_count', __ ('Hits', 'redirection')) ?></div>
			<div class="type"><?php echo $pager->sortable ('type', __ ('Type', 'redirection'), false) ?></div>
			<div class="item"><?php echo $pager->sortable ('url', __ ('URL', 'redirection'))  ?> / <?php echo $pager->sortable ('position', __ ('Position', 'redirection'))  ?></div>
		</li>
	</ul>
	
	<ul class="redirections" id="items">
	<?php if (is_array ($items) && count ($items) > 0) : ?>
		<?php foreach ($items AS $redirect) : ?>
		<li class="type_<?php echo $redirect->action->type () ?><?php if ($redirect->status == 'disabled') echo ' disabled' ?>" id="item_<?php echo $redirect->id ?>">
			<?php $this->render_admin ('item', array ('redirect' => $redirect, 'date_format' => $date_format)) ?>
		</li>
		<?php endforeach; ?>
	<?php endif; ?>
	</ul>
	
	<?php if ($pager->total_pages () > 0) : ?>
	<div class="pagertools">
	<?php foreach ($pager->area_pages () AS $page) : ?>
		<?php echo $page ?>
	<?php endforeach; ?>
	</div>
	<?php endif;?>
	
	<div class="pager pagertools">
		<a href="#" onclick="return select_all ()"><?php _e ('Select All', 'redirection'); ?></a> |
		<a href="#" onclick="return toggle_items('item')"><?php _e ('Toggle', 'redirection'); ?></a> | 
		<a href="#" onclick="return reset_items('item','<?php echo wp_create_nonce ('redirection-reset_items') ?>')"><?php _e ('Reset Hits', 'redirection'); ?></a> |
		<a href="#" onclick="return delete_items('item','<?php echo wp_create_nonce ('redirection-delete_items') ?>')"><?php _e ('Delete', 'redirection'); ?></a> |
		
		<?php _e ('Move To', 'redirection'); ?>:
		<select name="move" id="move">
			<?php echo $this->select ($groups)?>
		</select>
		
		<input class="button-secondary" type="submit" name="go" value="Go" onclick="return move_items('item','<?php echo wp_create_nonce ('redirection-move_items') ?>')"/>
	</div>
	
	<div class="sort" id="sort">
		<img src="<?php echo $this->url () ?>/images/sort.png" width="16" height="16" alt="Sort"/>

		<a id="toggle_sort_on" onclick="return sort_order ();" href="#"><?php _e ('re-order', 'redirection'); ?></a>
		<a id="toggle_sort_off" style="display: none" onclick="return save_redirect_order (<?php echo ($pager->current_page - 1) * $pager->per_page ?>,'<?php echo wp_create_nonce ('redirection-save_item_order') ?>');" href="#"><?php _e ('save order', 'redirection'); ?></a>
	</div>

	<?php if (!is_array ($items) || count ($items) == 0) : ?>
	  <p id="none"><?php _e ('You have no redirections.', 'redirection') ?></p>
	<?php endif; ?>
	
	<div id="loading" style="display: none">
		<img src="<?php echo $this->url () ?>/images/loading.gif" alt="loading" width="32" height="32"/>
	</div>
	
	<img src="<?php echo $this->url () ?>/images/progress.gif" width="50" height="16" alt="Progress" style="display: none"/>
	
	<?php global $is_IE; if (!$is_IE) : ?>
	<div style="clear: both"></div>
	<?php endif; ?>
</div>

<?php $this->render_admin ('add', array ('methods' => $methods, 'add_to_screen' => true, 'group' => $group->id)) ?>

<script type="text/javascript" charset="utf-8">
	jQuery(document).ready(function()
	{ 
		editItems ('edit_redirect');
	});
</script>