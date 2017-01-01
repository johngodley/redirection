<?php if ( !defined( 'ABSPATH' ) ) die( 'No direct access allowed' ); ?>

<div class="wrap" id="add" <?php if ( $hidden ) echo ' style="display: none"' ?>>
	<h2><?php _e( 'Add new redirection', 'redirection' ) ?></h2>

	<div class="red-added">
		<p><?php _e( 'Your redirection has been added.', 'redirection' ); ?></p>
	</div>

	<form method="post" action="<?php echo admin_url( 'admin-ajax.php' ); ?>">
		<table width="100%">
			<tr>
				<th align="right" width="100"><?php _e( 'Source URL', 'redirection' ) ?>:</th>
				<td><input type="text" name="source" style="width: 95%" id="old"/></td>
			</tr>
			<tr>
				<th align="right"><?php _e( 'Match', 'redirection' ) ?>:</th>
				<td>
					<select name="match">
						<?php echo $this->select( Red_Match::all() ); ?>
					</select>

					<strong><?php _e( 'Action', 'redirection' ); ?>:</strong>
					<select name="red_action">
						<?php echo $this->select( Red_Item::actions(), 'url' ); ?>
					</select>

					<label><?php _e( 'Regular expression', 'redirection' ); ?>: <input id="regex" type="checkbox" name="regex"/></label>
				</td>
			</tr>
			<tr id="target">
				<th align="right"><?php _e( 'Target URL', 'redirection' ) ?>:</th>
				<td><input type="text" name="target" style="width: 95%"/></td>
			</tr>
			<tr>
				<th><?php _e( 'Group', 'redirection' ); ?>:</th>
				<td>
					<select name="group_id">
						<?php echo $this->select( Red_Group::get_for_select(), ( isset( $group ) ? intval( $group ) : '' ) )?>
					</select>
				</td>
			</tr>
			<tr>
				<th></th>
				<td>
					<input class="button-primary" type="submit" name="add" value="<?php esc_attr_e( 'Add Redirection', 'redirection' ) ?>" id="submit"/>
					<?php if ( isset( $group ) ) : ?>
					<input type="hidden" name="group" value="<?php echo esc_attr( $group ) ?>"/>
					<?php endif; ?>

					<input type="hidden" name="action" value="red_redirect_add"/>
					<?php wp_nonce_field( 'redirection-redirect_add' ) ?>

					<div class="red-error"></div>
				</td>
			</tr>
	  </table>
	</form>
</div>
