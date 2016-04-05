<?php if ( !defined( 'ABSPATH' ) ) die( 'No direct access allowed' ); ?>
<table class="edit" data-url="<?php echo admin_url( 'admin-ajax.php' ) ?>">
	<tr>
		<th width="100"><a target="_blank" href="<?php echo esc_url( $redirect->get_url() ) ?>"><?php _e( 'Source URL', 'redirection' ); ?>:</a></th>
		<td>
			<input style="width: 85%" type="text" name="old" value="<?php echo esc_attr( $redirect->get_url() ); ?>" id="original"/>
			<label><?php _e( 'Regex', 'redirection' ); ?>: <input type="checkbox" name="regex" <?php if ( $redirect->is_regex() ) echo ' checked="checked"' ?>/></label>
		</td>
	</tr>
	<tr class="advanced">
		<th width="100"><?php _e( 'Description', 'redirection' ); ?>:</th>
		<td>
			<input style="width: 85%" type="text" name="title" value="<?php echo esc_attr( $redirect->get_title() ); ?>"/>
			<span class="sub">(<?php _e( 'optional', 'redirection' ); ?>)</span>
		</td>
	</tr>
	<tr class="advanced">
		<th width="100"><?php _e( 'Group', 'redirection' ); ?>:</th>
		<td>
			<select name="group_id">
				<?php echo $this->select( Red_Group::get_for_select(), $redirect->get_group_id() );?>
			</select>
		</td>
	</tr>

	<?php $redirect->match->show(); ?>

	<tr>
		<th></th>
		<td>
			<div class="table-actions">
				<input class="button-primary" type="submit" name="save" value="<?php _e( 'Save', 'redirection' ); ?>"/>
				<input class="button-secondary" type="submit" name="cancel" value="<?php _e( 'Cancel', 'redirection' ); ?>"/>

				<a href="#" class="advanced-toggle" title="<?php esc_attr_e( 'Advanced Settings' ); ?>">
					<img src="https://s.w.org/images/core/emoji/72x72/2699.png"/>
				</a>

				<input type="hidden" name="action" value="red_redirect_save"/>
				<input type="hidden" name="id" value="<?php echo esc_attr( $redirect->get_id() ); ?>"/>
				<?php wp_nonce_field( 'redirection-redirect_save_'.$redirect->get_id() ) ?>

				<span id="info_<?php echo esc_attr( $redirect->get_id() ) ?>"></span>
			</div>
			<div class="table-loading">
				<div class="spinner"></div>
			</div>
		</td>
	</tr>
</table>
