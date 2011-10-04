<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<table style="width: 100%">
	<tr>
		<th><a href="<?php echo $log->url ?>"><?php _e ('Source URL', 'redirection'); ?>:</a></th>
		<td><input style="width: 95%" type="text" name="something" readonly="readonly" value="<?php echo esc_attr (urldecode ($log->url)) ?>"/></td>
	</tr>
	<?php if ($log->sent_to) : ?>
	<tr>
		<th><a href="<?php echo $log->sent_to ?>"><?php _e ('Redirect to', 'redirection'); ?>:</a></th>
		<td><input style="width: 95%" type="text" name="something" readonly="readonly" value="<?php echo esc_attr ($log->sent_to) ?>"/></td>
	</tr>
	<?php endif; ?>
	<?php if ($redirect && isset($redirect->method) && $redirect->method) : ?>
	<tr>
		<th><?php _e ('Redirected by', 'redirection'); ?>:</th>
		<td><?php echo $redirect->method->name () ?> <?php _e ('for', 'redirection'); ?>: <code><?php echo esc_html ($redirect->url) ?></code></td>
	</tr>
	<?php endif; ?>
	<tr>
		<th><?php _e ('User Agent', 'redirection'); ?>:</th>
		<td><input style="width: 95%" type="text" readonly="readonly" name="other" value="<?php echo esc_html ($log->agent) ?>"/></td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input class="button-secondary" type="submit" name="cancel" value="Cancel"/>
		</td>
	</tr>
</table>
