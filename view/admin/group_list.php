<?php if ( !defined( 'ABSPATH' ) ) die( 'No direct access allowed' ); ?>
<div class="wrap">
	<?php screen_icon(); ?>
	<?php $this->render_admin( 'annoy'  ); ?>

	<h2><?php _e( 'Groups for module', 'redirection' ); ?>: <a href="edit.php?page=redirection.php&amp;sub=modules"><?php echo esc_html( $module->name  ) ?></a></h2>

	<?php $this->render_admin( 'submenu'  ); ?>

	<div id="pager" class="pager">
		<form method="get" action="">
			<input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page']  ) ?>"/>
			<input type="hidden" name="curpage" value="<?php echo $pager->current_page() ?>"/>
			<input type="hidden" name="sub" value="<?php echo esc_attr( $_GET['sub']  ) ?>"/>

			<?php _e( 'Module', 'redirection' ); ?>:
			<select name="id">
				<?php echo $this->select( $modules, isset($_GET['id'] ) ? intval( $_GET['id'] ) : '' )?>
			</select>

			<?php _e( 'Search', 'redirection' ); ?>:
			<input type="text" class="search-input" name="search" value="<?php echo isset($_GET['search'] ) ? esc_attr( $_GET['search']  ) : '' ?>" style="font-size: 0.8em"/>

			<?php $pager->per_page( 'redirection' ); ?>

			<input class="button-secondary" type="submit" name="go" value="<?php _e( 'go', 'redirection' ) ?>"/>
		</form>
	</div>

	<br/>

	<?php if ( count( $groups ) > 0 ) : ?>
		<ul id="redirections_header" class="redirections_header">
			<li>
				<div class="tools" style="width: 6.5em">&nbsp;</div>
				<div class="count"><?php echo __( 'Hits', 'redirection' ) ?></div>
				<div class="item"><?php echo __( 'Name', 'redirection' )  ?></div>
			</li>
		</ul>

		<ul class="redirections" id="items">
		<?php if ( count( $groups ) > 0 ) : ?>
			<?php foreach ( $groups AS $group ) : ?>
			<li id="item_<?php echo $group->id ?>" class="<?php if ( $group->status == 'disabled' ) echo 'disabled' ?>"><?php $this->render_admin( 'group_item', array( 'group' => $group ) ) ?></li>
			<?php endforeach; ?>
		<?php endif; ?>
		</ul>

		<?php if ( $pager->total_pages() > 0 ) : ?>
		<div class="pagertools">
		<?php foreach ( $pager->area_pages() AS $page ) : ?>
			<?php echo $page ?>
		<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<?php if ( count( $groups ) > 0 ) : ?>
		<div class="pager pagertools">
			<a class="select-all" href="#select-all"><?php _e( 'Select All', 'redirection' ); ?></a> |
			<a class="toggle-all" href="#toggle-all"><?php _e( 'Toggle', 'redirection' ); ?></a> |
			<a class="reset-all"  href="#reset-all"><?php _e( 'Reset Hits', 'redirection' ); ?></a> |
			<a class="delete-all" href="#delete-all"><?php _e( 'Delete', 'redirection' ); ?></a> |

			<?php _e( 'Move To', 'redirection' ); ?>:
			<select name="move" id="move">
				<?php echo $this->select( $modules )?>
			</select>

			<input class="button-secondary move-all" type="submit" value="<?php _e( 'Go', 'redirection' ); ?>"/>
		</div>

		<div class="sort" id="sort">
			<img src="<?php echo $this->url() ?>/images/sort.png" width="16" height="16" alt="Sort"/>

			<a class="sort-on"   id="toggle_sort_on"  href="#"><?php _e( 're-order', 'redirection' ); ?></a>
			<a class="sort-save" id="toggle_sort_off"  href="#" style="display: none"><?php _e( 'save order', 'redirection' ); ?></a>
		</div>
		<?php endif; ?>

		<div id="loading" style="display: none">
			<img src="<?php echo $this->url() ?>/images/loading.gif" alt="loading" width="32" height="32"/>
		</div>

		<?php global $is_IE; if ( !$is_IE ) : ?>
		<div style="clear: both"></div>
		<?php endif; ?>

	<?php else :?>
	  <p id="none"><?php _e( 'You have no groups in this module.', 'redirection' ) ?></p>
	<?php endif; ?>
</div>

<div class="wrap">
	<h2><?php _e( 'Add Group', 'redirection' ); ?></h2>

	<form action="" method="post" accept-charset="utf-8">
		<?php wp_nonce_field( 'redirection-add_group' ); ?>
		<table class="form-table">
			<tr>
				<th width="50"><?php _e( 'Name', 'redirection' ); ?>:</th>
				<td><input size="40" class="regular-text" type="text" name="name" value=""/></td>
			</tr>
			<tr>
				<th width="50"></th>
				<td><input class="button-primary" type="submit" name="add" value="<?php _e( 'Add', 'redirection' ); ?>"/><input type="hidden" name="module_id" value="<?php echo $module->id ?>"/></td>
			</tr>
		</table>
	</form>
</div>

<script type="text/javascript">
jQuery(document ).ready( function() {
	var redirection = new Redirection( {
		progress: '<?php echo esc_js( '<img src="'.$this->url().'/images/progress.gif" alt="loading" width="50" height="16"/>'  ) ?>',
		ajaxurl: '<?php echo esc_js( admin_url( 'admin-ajax.php'  )  ) ?>',
		nonce: '<?php echo esc_js( wp_create_nonce( 'redirection-items'  )  ); ?>',
		none_select: '<?php echo esc_js( __( 'No items have been selected', 'redirection' ) ); ?>',
		are_you_sure: '<?php echo esc_js( __( 'Are you sure?', 'redirection') ); ?>',
		page: <?php echo( $pager->current_page - 1 ) * $pager->per_page ?>
	} );

	redirection.edit_items( 'group'  );
} );
</script>
