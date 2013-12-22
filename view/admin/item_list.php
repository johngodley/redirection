<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php screen_icon(); ?>

	<h2>
		<?php _e ('Redirections for group', 'redirection'); ?>:

		<?php if ( $group ) : ?>
		<a href="<?php echo admin_url( 'tools.php?page=redirection.php' ); ?>&amp;sub=groups&amp;id=<?php echo $group->module_id ?>">
			<?php echo esc_html( $group->name ); ?>
		</a>
		<?php endif; ?>
	</h2>

	<?php $this->render_admin( 'submenu', array( 'options' => $options ) ); ?>

	<div id="pager" class="pager">
		<form method="get" action="">
			<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>"/>
			<input type="hidden" name="curpage" value="<?php echo $pager->current_page () ?>"/>
			<input type="hidden" name="sub" value="<?php echo esc_attr( isset($_GET['sub']) ? $_GET['sub'] : '' )?>"/>

			<?php _e ('Group', 'redirection'); ?>:
			<select name="id">
				<?php echo $this->select ($groups, isset( $_GET['id'] ) ? intval( $_GET['id'] ) : '')?>
			</select>

			<?php _e ('Search', 'redirection'); ?>:
			<input type="text" class="search-input"  name="search" value="<?php echo isset($_GET['search']) ? esc_attr( $_GET['search'] ) : '' ?>" style="font-size: 0.8em"/>

			<?php $pager->per_page ('redirection'); ?>

			<input class="button-secondary" type="submit" name="go" value="<?php _e ('Go', 'redirection') ?>"/>
		</form>
	</div>
	<br/>

	<ul id="redirections_header" class="redirections_header">
		<li>
			<div class="date" style="width: 8em"><?php echo __ ('Last Access', 'redirection') ?></div>
			<div class="count"><?php echo __('Hits', 'redirection') ?></div>
			<div class="type"><?php echo __ ('Type', 'redirection') ?></div>
			<div class="item"><?php echo __('URL', 'redirection')  ?> / <?php echo __ ('Position', 'redirection')  ?></div>
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
		<a class="select-all" href="#select-all"><?php _e ('Select All', 'redirection'); ?></a> |
		<a class="toggle-all" href="#toggle-all"><?php _e ('Toggle', 'redirection'); ?></a> |
		<a class="reset-all"  href="#reset-all"><?php _e ('Reset Hits', 'redirection'); ?></a> |
		<a class="delete-all" href="#delete-all"><?php _e ('Delete', 'redirection'); ?></a> |

		<?php _e ('Move To', 'redirection'); ?>:
		<select name="move" id="move">
			<?php echo $this->select( $groups )?>
		</select>

		<input class="button-secondary move-all" type="submit" value="<?php _e( 'Go', 'redirection'); ?>"/>
	</div>

	<div class="sort" id="sort">
		<img src="<?php echo $this->url () ?>/images/sort.png" width="16" height="16" alt="Sort"/>

		<a class="sort-on"   id="toggle_sort_on"  href="#"><?php _e ('re-order', 'redirection'); ?></a>
		<a class="sort-save" id="toggle_sort_off"  href="#" style="display: none"><?php _e ('save order', 'redirection'); ?></a>
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

<?php $this->render_admin ('add', array ('add_to_screen' => true, 'group' => $group ? $group->id : 0, 'hidden' => false)) ?>

<script type="text/javascript">
var redirection;

jQuery(document).ready( function() {
	redirection = new Redirection( {
		progress: '<?php echo '<img src="'.$this->url().'/images/progress.gif" alt="loading" width="50" height="16"/>' ?>',
		ajaxurl: '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ) ?>',
		nonce: '<?php echo esc_js( wp_create_nonce( 'redirection-items' ) ); ?>',
		none_select: '<?php echo esc_js( __( 'No items have been selected', 'redirection' ) ); ?>',
		are_you_sure: '<?php echo esc_js( __( 'Are you sure?', 'redirection') ); ?>',
		page: <?php echo ($pager->current_page - 1) * $pager->per_page ?>
	});

	redirection.edit_items( 'redirect' );
});
</script>
