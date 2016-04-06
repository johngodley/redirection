<?php if ( ! defined( 'ABSPATH' ) ) die( 'No direct access allowed' ); ?>

<?php

$expiry = array(
	-1 => __( 'No logs', 'redirection' ),
	1  => __( 'A day', 'redirection' ),
	7  => __( 'A week', 'redirection' ),
	30 => __( 'A month', 'redirection' ),
	60 => __( 'Two months', 'redirection' ),
	0  => __( 'Keep forever', 'redirection' ),
);

?>

<div class="wrap">
	<?php screen_icon( ); ?>

  <h2><?php _e( 'Options', 'redirection' ) ?></h2>
	<?php $this->render( 'submenu', array( 'options' => $options ) ); ?>

  <form method="post" action="">
	<?php wp_nonce_field( 'redirection-update_options' ); ?>

	<table cellpadding="3" width="100%" class="form-table">
		<tr>
			<th align="right"><?php _e( 'Plugin Support', 'redirection' ); ?>:</th>
			<td>
				<input type="checkbox" name="support" <?php echo checked( $options['support'] ) ?> id="support"/>
				<label for="support"><span class="sub"><?php _e( 'I\'m a nice person and I have helped support the author of this plugin', 'redirection' ); ?></span></label>
			</td>
		</tr>
		<tr>
			<th align="right"><?php _e( 'Redirect Logs', 'redirection' ); ?>:</th>
			<td>
				<select name="expire_redirect">
					<?php echo $this->select( $expiry, $options['expire_redirect'] ); ?>
				</select>

				<?php _e( '(time to keep logs for)', 'redirection' ); ?>
			</td>
		</tr>
		<tr>
			<th align="right"><?php _e( '404 Logs', 'redirection' ); ?>:</th>
			<td>
				<select name="expire_404">
					<?php echo $this->select( $expiry, $options['expire_404'] ); ?>
				</select>

				<?php _e( '(time to keep logs for)', 'redirection' ); ?>
			</td>
		</tr>
		<tr>
			<th><?php _e( 'Monitor changes to posts', 'redirection' ); ?>:</th>
			<td>
				<select name="monitor_post">
					<option value="0"><?php _e( 'Don\'t monitor', 'redirection' ); ?></option>
					<?php echo $this->select( $groups, $options['monitor_post'] );?>
				</select>
			</td>
		</tr>
		<tr>
			<th align="right"><?php _e( 'RSS Token', 'redirection' ); ?>:</th>
			<td>
				<input class="regular-text" size="5" type="text" name="token" value="<?php echo esc_attr( $options['token'] ) ?>"/><br/>
				<span class="sub"><?php _e( 'A unique token allowing feed readers access to Redirection log RSS (leave blank to auto-generate)', 'redirection' ); ?></span>
			</td>
		</tr>
		<tr>
	      	<th valign="top" align="right"><?php _e( 'Auto-generate URL', 'redirection' ) ?>:</th>
	      	<td>
				<input type="text" name="auto_target" style="width: 65%" value="<?php echo esc_attr( $options['auto_target'] ) ?>"/>
				<br/>
				<span class="sub"><?php _e( 'This will be used to auto-generate a URL if no URL is given.  You can use the special tags <code>$dec$</code> or <code>$hex$</code> to have a unique ID inserted (either decimal or hex)', 'redirection' ); ?></span>
			</td>
	    </tr>
	</table>

  <input class="button-primary" type="submit" name="update" value="<?php _e( 'Update', 'redirection' ) ?>"/>

  </form>
</div>

<div class="wrap">
	<h2><?php _e( 'Import', 'redirection' ); ?></h2>

	<p><?php _e( 'Here you can import redirections from an existing <code>.htaccess</code> file, or a CSV file.', 'redirection' ); ?></p>

	<form action="" method="post" accept-charset="utf-8" enctype="multipart/form-data">
		<?php wp_nonce_field( 'redirection-import' ); ?>

		<input type="file" name="upload" value=""/>

		<?php _e( 'Import into', 'redirection' ); ?>: <select name="group">
			<?php echo $this->select( $groups );?>
		</select>
		<input class="button-primary" type="submit" name="import" value="<?php _e( 'Upload', 'redirection' ); ?>"/>
	</form>
</div>

<div class="wrap">
	<h2><?php _e( 'Delete Redirection', 'redirection' ); ?></h2>
	<p><?php _e( 'Selecting this option will delete all redirections, all logs, and any options associated with the Redirection plugin.  Make sure this is what you want to do.', 'redirection' ); ?></p>

	<form action="" method="post" accept-charset="utf-8">
			<?php wp_nonce_field( 'redirection-delete_plugin' ); ?>

			<input class="button-primary" type="submit" name="delete" value="<?php _e( 'Delete', 'redirection' ) ?>" onclick="return confirm( '<?php esc_attr_e( "Are you sure you want to delete everything?" );?>')"/>
	</form>
</div>
