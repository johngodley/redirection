<div id="added" style="display: none" class="updated">
	<p>Your redirection has been added.</p>
</div>

<div class="wrap" id="add">
	<h2><?php _e ('Add new redirection', 'redirection') ?></h2>
	
	<form method="post" action="<?php echo $this->url ($_SERVER['REQUEST_URI']) ?>" onsubmit="add_redirection(this,<?php if ($add_to_screen) echo 'true'; else echo 'false' ?>);return false">
	<table width="100%">
	  <tr>
	    <th align="right" width="100"><?php _e ('Source URL', 'redirection') ?>:</th>
	    <td><input type="text" name="old" style="width: 95%" id="old"/></td>
	  </tr>
	  <tr>
	    <th align="right"><?php _e ('Target URL', 'redirection') ?>:</th>
	    <td><input type="text" name="new" style="width: 95%"/></td>
	  </tr>
	  <tr>
	    <th align="right"><?php _e ('Type', 'redirection') ?>:</th>
	    <td>
	    <select name="redirector">
				<?php foreach ($redirectors->available AS $class => $redirector) : ?>
				<option value="<?php echo $class ?>"><?php echo htmlspecialchars ($redirector) ?></option>
				<?php endforeach; ?>
			</select>
			<strong><?php _e ('Method', 'redirection'); ?>:</strong>
			<select name="type">
				<option value="301">301 Permanent redirect</option>
				<option value="302">302 Moved</option>
				<option value="307">307 Temporary redirect</option>
				<option value="404">404 Not found</option>
				<option value="410">410 Gone</option>
				<option value="pass"><?php _e ('Pass-through', 'redirection'); ?></option>
			</select>
	    </td>
	  </tr>
	  <tr>
	    <th align="right"><label for="regex"><?php _e ('Regex', 'redirection') ?></label>:</th>
	    <td><input id="regex" type="checkbox" name="regex"/></td>
	  </tr>
	  <tr>
	    <th></th>
	    <td><input type="submit" name="add" value="<?php _e ('Add Redirection', 'redirection') ?>" id="submit"/></td>
	  </tr>
	  </table>
	</form>
</div>
