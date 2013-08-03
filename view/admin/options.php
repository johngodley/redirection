<?php if( !defined( 'ABSPATH' ) ) die( 'No direct access allowed' ); ?>
<div class="wrap">
	<?php screen_icon( ); ?>
	<?php $this->render_admin('annoy' )?>

  <h2><?php _e( 'Options', 'redirection' ) ?></h2>
	<?php $this->render_admin( 'submenu', array( 'options' => $options ) ); ?>

  <form method="post" action="" style="clear: both">

	<?php wp_nonce_field( 'redirection-update_options' ); ?>

	  <table cellpadding="3" width="100%" class="form-table">
			<tr>
		      <th valign="top" align="right"><?php _e( 'Auto-generate URL', 'redirection' ) ?>:</th>
		      <td>
						<input type="text" name="auto_target" style="width: 95%" value="<?php echo esc_attr( $options['auto_target']  ) ?>"/>
						<br/>
						<span class="sub"><?php _e( 'This will be used to auto-generate a URL if no URL is given.  You can use the special tags $dec$ or $hex$ to have a unique ID inserted (either decimal or hex)', 'redirection' ); ?></span>

					</td>
		    </tr>
			<tr>
				<th align="right"><?php _e( 'Plugin Support', 'redirection' ); ?>:</th>
				<td>
					<input type="checkbox" name="support" <?php echo $this->checked( $options['support'] ) ?> id="support"/>
					<label for="support"><span class="sub"><?php _e( 'I\'m a nice person and I have helped support the author of this plugin', 'redirection' ); ?></span></label>
				</td>
			</tr>
			<tr>
				<th align="right"><?php _e( 'Logging', 'redirection' ); ?>:</th>
				<td>
					<input type="checkbox" name="log_redirections" <?php echo $this->checked( $options['log_redirections'] ) ?> id="log_redirections"/>
					<label for="log_redirections"><span class="sub"><?php _e( 'log redirected requests', 'redirection' ); ?></span></label><br />
					<input type="checkbox" name="log_404s" <?php echo $this->checked( $options['log_404s'] ) ?> id="log_404s"/>
					<label for="log_404s"><span class="sub"><?php _e( 'log 404 Not Found requests', 'redirection' ); ?></span></label><br />
					<?php _e( 'Uncheck one or both of these to turn off logging and reduce database load if your redirected URLs are hit very frequently, and/or your site is very busy and pages are often not found.', 'redirection'  ); ?>
				</td>
			</tr>
			<tr>
				<th align="right"><?php _e( 'Expire Logs', 'redirection' ); ?>:</th>
				<td>
					<input size="5" type="text" name="expire" value="<?php echo esc_attr( $options['expire']  ) ?>"/>
					<?php _e( 'days (enter 0 for no expiry)', 'redirection' ); ?>
				</td>
			</tr>
			<tr>
				<th align="right"><?php _e( 'RSS Token', 'redirection' ); ?>:</th>
				<td>
					<input class="regular-text" size="5" type="text" name="token" value="<?php echo esc_attr( $options['token']  ) ?>"/><br/>
					<?php _e( 'A unique token allowing feed readers access to Redirection RSS (leave blank to auto-generate)', 'redirection' ); ?>
				</td>
			</tr>
		</table>

		<h3><?php _e( 'URL Monitoring', 'redirection' ); ?></h3>
		<p><?php _e( 'You can have Redirection detect changes in URLs and have an automatic redirection created in a specific group.', 'redirection' ); ?></p>

		<table class="form-table">
			<tr>
				<th><?php _e( 'Post &amp; Page URLs', 'redirection' ); ?>:</th>
				<td>
					<select name="monitor_post">
						<option value="0"><?php _e( 'Don\'t monitor', 'redirection' ); ?></option>
						<?php echo $this->select( $groups, $options['monitor_post'] );?>
					</select>
					&mdash;
					<label for="create_url_for_new_posts"><?php _e( 'Monitor new posts', 'redirection' ); ?></label> <input type="checkbox" name="monitor_new_posts" <?php echo $this->checked( $options['monitor_new_posts'] ); ?> id="create_url_for_new_posts"/>
				</td>
			</tr>
			<!--
			<tr>
				<th><?php _e( 'Category URLs', 'redirection' ); ?>:</th>
				<td>
					<select name="monitor_category">
						<option value="0"><?php _e( 'Don\'t monitor', 'redirection' ); ?></option>
						<?php echo $this->select( $groups, $options['monitor_category'] );?>
					</select>
				</td>
			</tr>-->
	  </table>

  <input class="button-primary" type="submit" name="update" value="<?php _e( 'Update', 'redirection' ) ?>"/>

  </form>
</div>

<div class="wrap">
	<h2><?php _e( 'Import', 'redirection' ); ?></h2>

	<p><?php _e( 'Here you can import redirections from an existing .htaccess file, or a CSV file.', 'redirection' ); ?></p>

	<form action="" method="post" accept-charset="utf-8" enctype="multipart/form-data">
		<?php wp_nonce_field( 'redirection-import' ); ?>

		<input type="file" name="upload" value=""/>

		<?php _e( 'Import into', 'redirection' ); ?>: <select name="group">
			<?php echo $this->select( $groups );?>
		</select>
		<input class="button-primary" type="submit" name="import" value="<?php _e( 'Upload', 'redirection' ); ?>"/>
	</form>

	<p><?php _e( 'Note that the group is ignored when uploading an XML file.', 'redirection' ); ?></p>
</div>

<div class="wrap">
	<h2><?php _e( 'Delete Redirection', 'redirection' ); ?></h2>
	<p><?php _e( 'Selecting this option will delete all redirections, all logs, and any options associated with the Redirection plugin.  Make sure this is what you want to do.', 'redirection' ); ?></p>

	<form action="" method="post" accept-charset="utf-8">
			<?php wp_nonce_field( 'redirection-delete_plugin' ); ?>

			<input class="button-primary" type="submit" name="delete" value="<?php _e( 'Delete', 'redirection' ) ?>"/>
	</form>
</div>
