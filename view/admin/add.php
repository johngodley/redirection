<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div id="added" style="display: none" class="updated">
	<p><?php _e ('Your redirection has been added.', 'redirection'); ?></p>
</div>

<div class="wrap" id="add" <?php if ($hidden) echo ' style="display: none"' ?>>
	<h2><?php _e ('Add new redirection', 'redirection') ?></h2>

	<form method="post" action="<?php echo $this->url ($_SERVER['REQUEST_URI']) ?>" onsubmit="add_redirection(this,<?php if ($add_to_screen) echo 'true'; else echo 'false' ?>);return false">
	<table width="100%">
	  <tr>
	    <th align="right" width="100"><?php _e ('Source URL', 'redirection') ?>:</th>
	    <td><input type="text" name="source" style="width: 95%" id="old"/></td>
	  </tr>
	  <tr>
	    <th align="right"><?php _e ('Match', 'redirection') ?>:</th>
	    <td>
	    <select name="match">
				<?php echo $this->select (Red_Match::all ()); ?>
			</select>
			
			<strong><?php _e ('Action', 'redirection'); ?>:</strong>
			<select name="action" onchange="return change_add_redirect (this)">
				<?php echo $this->select (Red_Item::actions (), 'url'); ?>
			</select>
			
			<label><?php _e ('Regular expression', 'redirection'); ?>: <input id="regex" type="checkbox" name="regex"/></label>
	    </td>
	  </tr>
	  <tr id="target">
	    <th align="right"><?php _e ('Target URL', 'redirection') ?>:</th>
	    <td><input type="text" name="target" style="width: 95%"/></td>
	  </tr>
		<?php if ($group == '') : ?>
		<tr>
			<th><?php _e ('Group', 'redirection'); ?>:</th>
			<td><select name="group"><?php echo $this->select (Red_Group::get_for_select (), isset ($_GET['group']) ? intval ($_GET['group']) : 0)?></select></td>
		</tr>
		<?php endif; ?>
	  <tr>
	    <th></th>
	    <td>
				<input type="submit" name="add" value="<?php _e ('Add Redirection', 'redirection') ?>" id="submit"/>
				<?php if ($group) : ?>
				<input type="hidden" name="group" value="<?php echo $group ?>"/>
				<?php endif; ?>
				
				<div id="error"></div>
			</td>
	  </tr>
	  </table>
	</form>
</div>
