<div class="item">
	<strong><?php echo $redirect->redirector->name () ?></strong>
	<form action="" method="post" accept-charset="utf-8" onsubmit="save_redirect(<?php echo $redirect->id ?>, this); return false">
	<table>
		<tr>
			<th width="100"><a target="_blank" href="<?php echo $redirect->url ?>"><?php _e ('Source URL', 'redirection'); ?>:</a></th>
			<td><input style="width: 95%" type="text" name="old" value="<?php echo htmlspecialchars ($redirect->url); ?>" id="original"/></td>
		</tr>
		<tr>
	    <th align="right"><label for="regex_<?php echo $redirect->id  ?>"><?php _e ('Regex', 'redirection') ?></label>:</th>
	    <td><input id="regex_<?php echo $redirect->id  ?>" type="checkbox" name="regex" <?php if ($redirect->regex == true) echo ' checked="checked"' ?>/></td>
	  </tr>
	
		<?php $redirect->redirector->show (); ?>
		
<?php if ($log && strlen ($log->agent) > 0 && strlen ($log->ip) > 0) : ?>
		<tr>
			<th><?php _e ('Last User', 'redirection'); ?>:</th>
			<td><a href="http://www.google.com/search?q=<?php echo urlencode ($log->agent) ?>"><?php echo htmlspecialchars ($log->agent) ?></a> from <a href="http://ws.arin.net/cgi-bin/whois.pl?queryinput=<?php echo $log->ip ?>"><?php echo $log->ip ?></a></td>
		</tr>
<?php elseif ($log && strlen ($log->agent) > 0) : ?>
		<tr>
			<th><?php _e ('Last User', 'redirection'); ?>:</th>
			<td><a href="http://www.google.com/search?q=<?php echo urlencode ($log->agent) ?>"><?php echo htmlspecialchars ($log->agent) ?></a></td>
		</tr>
<?php elseif ($log && strlen ($log->ip) > 0) : ?>
		<tr>
			<th valign="top"><?php _e ('Last User', 'redirection'); ?>:</th>
			<td><a href="http://ws.arin.net/cgi-bin/whois.pl?queryinput=<?php echo $log->ip ?>"><?php echo $log->ip ?></a></td>
		</tr>
<?php endif; ?>
<?php if ($log && strlen ($log->referrer) > 0) : ?>
		<tr>
			<th><a title="<?php _e ('Open referrer', 'redirection'); ?>" target="_blank" href="<?php echo $log->referrer ?>"><?php _e ('Last Referrer', 'redirection'); ?>:</a></th>
			<td><input readonly="readonly" style="width: 95%"  type="text" name="referrer" value="<?php echo htmlspecialchars ($log->referrer) ?>"/></td>
		</tr>
<?php endif; ?>
		<tr>
			<th></th>
			<td><input type="submit" name="save" value="<?php _e ('Save', 'redirection'); ?>"/> <input type="submit" name="cancel" value="<?php _e ('Cancel', 'redirection'); ?>" onclick="cancel_redirect(<?php echo $redirect->id ?>);return false"/></td>
		</tr>
	</table>
	</form>
</div>