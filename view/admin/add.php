<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap" id="add" <?php if ($hidden) echo ' style="display: none"' ?>>
	<h2><?php _e ('Add new redirection', 'redirection') ?></h2>

	<div id="added" style="display: none" class="updated-red">
		<p><?php _e ('Your redirection has been added.', 'redirection'); ?></p>
	</div>

	<form method="post" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" id="new-redirection">
	<table width="100%">
	  <tr>
	    <th align="right" width="100"><?php _e ('Source URL', 'redirection') ?>:</th>
	    <td><input type="text" name="source" style="width: 95%" id="old"/></td>
	  </tr>
	  <tr>
	    <th align="right"><?php _e ('Match', 'redirection') ?>:</th>
	    <td>
	    <select name="match">
				<?php echo $this->select (Red_Match::all ()); ?>
			</select>

			<strong><?php _e ('Action', 'redirection'); ?>:</strong>
			<select name="red_action" onchange="return change_add_redirect (this)">
				<?php echo $this->select (Red_Item::actions (), 'url'); ?>
			</select>

			<label><?php _e ('Regular expression', 'redirection'); ?>: <input id="regex" type="checkbox" name="regex"/></label>
	    </td>
	  </tr>
	  <tr id="target">
	    <th align="right"><?php _e ('Target URL', 'redirection') ?>:</th>
	    <td><input type="text" name="target" style="width: 95%"/></td>
	  </tr>
		<?php if (!isset($group)) : ?>
		<tr>
			<th><?php _e ('Group', 'redirection'); ?>:</th>
			<td><select name="group"><?php echo $this->select (Red_Group::get_for_select (), isset ($_GET['group']) ? intval ($_GET['group']) : 0)?></select></td>
		</tr>
		<?php endif; ?>
	  <tr>
	    <th></th>
	    <td>
				<input class="button-primary" type="submit" name="add" value="<?php _e ('Add Redirection', 'redirection') ?>" id="submit"/>
				<?php if (isset($group)) : ?>
				<input type="hidden" name="group" value="<?php echo esc_attr( $group ) ?>"/>
				<?php endif; ?>

				<input type="hidden" name="action" value="red_redirect_add"/>
				<input type="hidden" name="_ajax_nonce" value="<?php echo wp_create_nonce( 'redirection-redirect_add' ); ?>"/>

				<div id="error" style="margin-top: 10px"></div>
			</td>
	  </tr>
	  </table>
	</form>
</div>

<script type="text/javascript" charset="utf-8">
	jQuery( '#new-redirection' ).ajaxForm( {
		beforeSubmit: function () {
			jQuery( '#loading' ).show ();
		},
		success: function( response ) {
			jQuery( '#loading' ).hide ();

			if ( response.indexOf( 'fade error' ) != -1 )
      	jQuery( '#error' ).html (response);
	    else {
				<?php if ( isset( $add_to_screen ) ) : ?>
				jQuery( '#items' ).append( response );
				<?php endif; ?>

	      jQuery( '#error' ).hide();
	      jQuery( '#added' ).show();
	      jQuery( '#none' ).hide();

				redirection.edit_items( 'redirect' );
	    }
		}
	});
</script>
