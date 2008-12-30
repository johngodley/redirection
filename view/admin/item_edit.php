<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<form method="post" accept-charset="utf-8" action="<?php echo $this->url (); ?>/ajax.php?id=<?php echo $redirect->id ?>&amp;cmd=save_redirect&amp;_ajax_nonce=<?php echo wp_create_nonce ('redirection-item_save')?>" id="redirect_form_<?php echo $redirect->id ?>" style="padding: 3px">
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
				<input class="button-secondary" type="submit" name="save" value="<?php _e ('Save', 'redirection'); ?>"/>
				<input class="button-secondary" type="submit" name="cancel" value="<?php _e ('Cancel', 'redirection'); ?>" onclick="return show_redirect(<?php echo $redirect->id ?>);"/>
			
				<span id="info_<?php echo $redirect->id ?>"></span>
			</td>
		</tr>
	</table>
</form>

<script type="text/javascript" charset="utf-8">
	 jQuery('#redirect_form_<?php echo $redirect->id ?>').ajaxForm ( { beforeSubmit: function ()
			{
  			jQuery('#info_<?php echo $redirect->id ?>').html (wp_progress);
			},
			success: function (response)
			{
				jQuery('#item_<?php echo $redirect->id ?>').html (response);
				editItems ('edit_redirect');
			}});
</script>
