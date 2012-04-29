<?php if( !defined( 'ABSPATH' ) ) die( 'No direct access allowed' ); ?>
<td class="logo">
	<a href="edit.php?page=redirection.php&amp;sub=groups&amp;id=<?php echo $module->id ?>">
	<?php if( $module->type == 'apache' ) : ?>
	<img src="<?php echo $this->url() ?>/images/modules/apache.png" width="55" height="56" alt="Apache"/>
	<?php elseif( $module->type == 'wp' ) : ?>
	<img src="<?php echo $this->url() ?>/images/modules/wordpress.png" width="64" height="64" alt="Wordpress"/>
	<?php elseif( $module->type == '404' ) : ?>
	<img src="<?php echo $this->url() ?>/images/modules/404.png" width="64" height="64" alt="Wordpress"/>
	<?php endif; ?>
	</a>
</td>

<td colspan="5" class="edit">
	<form action="<?php echo admin_url( 'admin-ajax.php' ) ?>" method="post" accept-charset="utf-8">
		<table class="edit">
			<tr>
				<th><?php _e( 'Name', 'redirection' ); ?>:</th>
				<td><input type="text" name="name" value="<?php echo esc_attr( $module->name ) ?>" style="width: 95%"/></td>
			</tr>

			<?php $module->config(); ?>

			<tr>
				<th></th>
				<td>
					<input class="button-primary" type="submit" name="save" value="<?php _e( 'Save', 'redirection' ); ?>"/>
					<input class="button-secondary" type="submit" name="cancel" value="<?php _e( 'Cancel', 'redirection' ); ?>"/>
					<img class="loader" src="<?php echo $this->url(); ?>/images/progress.gif" alt="loading" width="50" height="16" style="display: none"/>

					<input type="hidden" name="action" value="red_module_save"/>
					<input type="hidden" name="id" value="<?php echo $module->id; ?>"/>
					<input type="hidden" name="_ajax_nonce" value="<?php echo wp_create_nonce( 'redirection-module_save_'.$module->id ); ?>"/>
				</td>
			</tr>
		</table>
	</form>
</td>
