<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><form action="" method="post" accept-charset="utf-8" onsubmit="save_redirect(<?php echo $redirect->id ?>, this); return false" style="padding: 3px">
	<h3><?php printf (__ ('%s by matching %s', 'redirection'), $redirect->actions ($redirect->action_type), $redirect->match->name ()) ?></h3>
	
<table class="edit">
	<tr>
		<th width="100"><a target="_blank" href="<?php echo $redirect->url ?>"><?php _e ('Source URL', 'redirection'); ?>:</a></th>
		<td>
			<input style="width: 85%" type="text" name="old" value="<?php echo htmlspecialchars ($redirect->url); ?>" id="original"/>
			<label><?php _e ('Regex', 'redirection'); ?>: <input type="checkbox" name="regex" <?php if ($redirect->regex == true) echo ' checked="checked"' ?>/></label>
		</td>
	</tr>

	<?php $redirect->match->show (); ?>

	<tr>
		<th></th>
		<td>
			<input type="submit" name="save" value="<?php _e ('Save', 'redirection'); ?>"/>
			<input type="submit" name="cancel" value="<?php _e ('Cancel', 'redirection'); ?>" onclick="cancel_redirect(<?php echo $redirect->id ?>);return false"/>
			
			<span id="info_<?php echo $redirect->id ?>"></span>
		</td>
	</tr>
</table>
</form>
