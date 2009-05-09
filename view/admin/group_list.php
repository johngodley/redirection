<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php screen_icon(); ?>
	<?php $this->render_admin('annoy')?>

	<h2><?php _e ('Groups for module', 'redirection'); ?>: <a href="edit.php?page=redirection.php&amp;sub=modules"><?php echo htmlspecialchars ($module->name) ?></a></h2>
		
	<?php $this->submenu (true); ?>
	<div id="pager" class="pager">
		<form method="get" action="<?php echo $this->url ($pager->url) ?>">
			<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>"/>
			<input type="hidden" name="curpage" value="<?php echo $pager->current_page () ?>"/>
			<input type="hidden" name="sub" value="<?php echo $_GET['sub'] ?>"/>

			<?php _e ('Module', 'redirection'); ?>:
			<select name="id">
				<?php echo $this->select ($modules, $_GET['id'])?>
			</select>
			
			<?php _e ('Search', 'redirection'); ?>: 
			<input type="text" class="search-input" name="search" value="<?php echo htmlspecialchars ($_GET['search']) ?>" style="font-size: 0.8em"/>

			<?php $pager->per_page ('redirection'); ?>

			<input class="button-secondary" type="submit" name="go" value="<?php _e ('go', 'redirection') ?>"/>
		</form>
	</div>

	<br/>
	
	<?php if (count ($groups) > 0) : ?>
		<ul id="redirections_header" class="redirections_header">
			<li>
				<div class="tools" style="width: 6.5em">&nbsp;</div>
				<div class="count"><?php echo $pager->sortable ('redirects', __ ('Hits', 'redirection')) ?></div>
				<div class="item"><?php echo $pager->sortable ('name', __ ('Name', 'redirection'))  ?></div>
			</li>
		</ul>
		
		<ul class="redirections" id="items">
		<?php if (count ($groups) > 0) : ?>
			<?php foreach ($groups AS $group) : ?>
			<li id="item_<?php echo $group->id ?>" class="<?php if ($group->status == 'disabled') echo 'disabled' ?>"><?php $this->render_admin ('group_item', array ('group' => $group)) ?></li>
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

		<?php if (count ($groups) > 0) : ?>
		<div class="pager pagertools">
			<a href="#" onclick="return select_all ()"><?php _e ('Select All', 'redirection'); ?></a> |
			<a href="#" onclick="return toggle_items('group')"><?php _e ('Toggle', 'redirection'); ?></a> | 
			<a href="#" onclick="return reset_items('group','<?php echo wp_create_nonce ('redirection-reset_items') ?>')"><?php _e ('Reset Hits', 'redirection'); ?></a> |
			<a href="#" onclick="return delete_items('group','<?php echo wp_create_nonce ('redirection-delete_items') ?>')"><?php _e ('Delete', 'redirection'); ?></a> |

			<?php _e ('Move To', 'redirection'); ?>:
			<select name="move" id="move">
				<?php echo $this->select ($modules)?>
			</select>
			<input class="button-secondary" type="submit" name="go" value="Go" onclick="return move_items('group','<?php echo wp_create_nonce ('redirection-move_items') ?>')"/>
		</div>
		
		<div class="sort" id="sort">
			<img src="<?php echo $this->url () ?>/images/sort.png" width="16" height="16" alt="Sort"/>

			<a id="toggle_sort_on" onclick="return sort_order ();" href="#"><?php _e ('re-order', 'redirection'); ?></a>
			<a id="toggle_sort_off" style="display: none" onclick="return save_group_order (<?php echo ($pager->current_page - 1) * $pager->per_page ?>,'<?php echo wp_create_nonce ('redirection-save_group_order') ?>');" href="#"><?php _e ('save order', 'redirection'); ?></a>
		</div>
		<?php endif; ?>
	
		<div id="loading" style="display: none">
			<img src="<?php echo $this->url () ?>/images/loading.gif" alt="loading" width="32" height="32"/>
		</div>
		
		<?php global $is_IE; if (!$is_IE) : ?>
		<div style="clear: both"></div>
		<?php endif; ?>

	<?php else :?>
	  <p id="none"><?php _e ('You have no groups in this module.', 'redirection') ?></p>
	<?php endif; ?>
</div>

<div class="wrap">
	<h2><?php _e ('Add Group', 'redirection'); ?></h2>
	
	<form action="<?php echo $this->url ($_SERVER['REQUEST_URI']) ?>" method="post" accept-charset="utf-8">
		<?php wp_nonce_field ('redirection-add_group'); ?>
		<table class="form-table">
			<tr>
				<th width="50"><?php _e ('Name', 'redirection'); ?>:</th>
				<td><input size="40" class="regular-text" type="text" name="name" value=""/></td>
			</tr>
			<tr>
				<th width="50"></th>
				<td><input class="button-primary" type="submit" name="add" value="<?php _e ('Add', 'redirection'); ?>"/><input type="hidden" name="module_id" value="<?php echo $module->id ?>"/></td>
			</tr>
		</table>
	</form>
</div>

<script type="text/javascript" charset="utf-8">
	jQuery(document).ready(function()
	{ 
		editItems ('edit_group');
	});
</script>