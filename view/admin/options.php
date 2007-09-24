<div class="wrap">
  <h2><?php _e ('Options', 'redirection') ?></h2>
  <form method="post" action="<?php echo $this->url ($_SERVER['REQUEST_URI']) ?>">
  <table cellpadding="3" width="100%">
    <tr>
      <th width="260" align="right"><?php _e ('Create 301 when post slug changes', 'redirection') ?>:</th>
      <td><input type="checkbox" name="post_change" <?php if (get_option ('redirection_post') == 'true') echo 'checked="checked"'; ?>/></td>
    </tr>
    <tr>
      <th align="right"><?php _e ('Redirect index.php/index.html', 'redirection') ?>:</th>
      <td><input type="checkbox" name="index" <?php if (get_option ('redirection_index') == 'true') echo 'checked="checked"'; ?>/></td>
    </tr>
    <tr>
      <th align="right"><?php _e ('Root domain', 'redirection') ?>:</th>
      <td>
	      <select name="root">
      		<option value="no"<?php if (get_option ('redirection_root') == 'no') echo ' selected="selected"'; ?>><?php _e ('No', 'redirection'); ?></option>
      		<option value="nowww"<?php if (get_option ('redirection_root') == 'nowww') echo ' selected="selected"'; ?>><?php _e ('Strip www', 'redirection'); ?></option>
      		<option value="www"<?php if (get_option ('redirection_root') == 'www') echo ' selected="selected"'; ?>><?php _e ('Add www', 'redirection'); ?></option>
      	</select>
      </td>
    </tr>
		<tr>
      <th valign="top" align="right"><?php _e ('Auto-generate URL', 'redirection') ?>:</th>
      <td>
				<input type="text" name="redirection_auto_target" style="width: 95%" value="<?php echo get_option ('redirection_auto_target') ?>"/>
				<br/>
				<span class="sub">This will be used to auto-generate a URL if no URL is given.  You can use the special tags
					$dec$ or $hex$ to have a unique ID inserted (either decimal or hex)</span>

			</td>
    </tr>
		<tr>
      <th valign="top" align="right"><?php _e ('Globally redirect unknown 404 errors', 'redirection') ?>:</th>
      <td>
				<input type="text" name="404_redirect" style="width: 95%" value="<?php echo get_option ('redirection_global_404') ?>"/>
			</td>
    </tr>
		<tr>
			<th align="right" valign="top">IP Lookup Service:</th>
			<td>
				<input type="text" style="width: 95%" name="lookup" value="<?php echo get_option ('redirection_lookup') ?>" id="lookup"/><br/>
				<span class="sub">Example:</span>
					 	<ul>
							<li><code>http://geomaplookup.cinnamonthoughts.org/?ip=</code></li>
							<li><code>http://ws.arin.net/cgi-bin/whois.pl?queryinput=</code></li>
						</ul>
			</td>
		</tr>
		<tr>
      <th align="right"><?php _e ('Log 404 errors', 'redirection') ?>:</th>
      <td><input type="checkbox" name="redirection_404_log" <?php if (get_option ('redirection_404_log') == 'true') echo 'checked="checked"'; ?>/></td>
    </tr>
		<tr>
      <th align="right"><?php _e ('Check for updates', 'redirection') ?>:</th>
      <td><input type="checkbox" name="redirection_updates" <?php if (get_option ('redirection_updates') == 'true') echo 'checked="checked"'; ?>/></td>
    </tr>
  </table>

  <input type="submit" name="update" value="<?php _e ('Update', 'redirection') ?>"/>

  </form>
</div>

<div class="wrap">
	<h2>Delete Redirection</h2>
	<p>Selecting this option will delete all redirections, all logs, and any options associated with the Redirection plugin.  Make sure this is what you want to do.</p>

	<form action="<?php echo $this->url ($_SERVER['REQUEST_URI']) ?>" method="post" accept-charset="utf-8">
			<input type="submit" name="delete" value="<?php _e ('Delete', 'redirection') ?>"/>
	</form>
</div>