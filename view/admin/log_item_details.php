<td colspan="5">
	<a href="#" onclick="hide_log(<?php echo $log->id ?>);return false"><?php echo date (get_option ('date_format'), $log->created) ?></a>
	 <?php _e ('at', 'redirection'); ?> <?php echo date (get_option ('time_format', $log->created)) ?><br/>
	<table>

		<tr>
			<th><a href="<?php echo $log->url ?>"><?php _e ('Source URL', 'redirection'); ?>:</a></th>
			<td><input style="width: 95%" type="text" name="something" readonly="readonly" value="<?php echo htmlspecialchars (urldecode ($log->url)) ?>"/></td>
		</tr>
		<tr>
			<th><a href="<?php echo $log->sent_to ?>"><?php _e ('Target URL', 'redirection'); ?>:</a></th>
			<td><input style="width: 95%" type="text" name="something" readonly="readonly" value="<?php echo htmlspecialchars ($log->sent_to) ?>"/></td>
		</tr>
		<tr>
			<th valign="top"><?php _e ('Redirected by', 'redirection'); ?>:</th>
			<td><?php echo $redirect->redirector->name () ?> <?php _e ('for', 'redirection'); ?>: <code><?php echo htmlspecialchars ($redirect->url) ?></code></td>
		</tr>
		<tr>
			<th>
				<?php if (strlen ($log->referrer) > 0) : ?>
				<a href="<?php echo $log->referrer ?>"><?php _e ('Referrer', 'redirection'); ?>:</a>
				<?php else : ?>
				<?php _e ('Referrer', 'redirection'); ?>:
				<?php endif; ?>
			</th>
			<td><input style="width: 95%" type="text" readonly="readonly" name="other" value="<?php echo htmlspecialchars ($log->referrer) ?>"/></td>
		</tr>
		<tr>
			<th><?php _e ('User Agent', 'redirection'); ?>:</th>
			<td><?php echo $log->agent ?></td>
		</tr>
		<tr>
			<th><?php _e ('IP', 'redirection'); ?>:</th>
			<td><a href="http://ws.arin.net/cgi-bin/whois.pl?queryinput=<?php echo $log->ip ?>"><?php echo htmlspecialchars ($log->ip) ?></a></td>
		</tr>
	</table>
</td>