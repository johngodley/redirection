<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><td width="16">
	<input type="checkbox" name="select[]" value="<?php echo $log->id ?>"/>
</td>
<td style="width:9em"<?php $pager->sortable_class ('created') ?>>
	<a href="#" onclick="return toggle_log(<?php echo $log->id ?>)"><?php echo date (str_replace ('F', 'M', get_option ('date_format')), $log->created) ?></a>
</td>
<td id="info_<?php echo $log->id ?>"<?php $pager->sortable_class ('url') ?>>
	<a id="href_<?php echo $log->id ?>" href="<?php echo $log->url ?>" onclick="return toggle_log(<?php echo $log->id ?>)"><?php echo $log->show_url ($log->url) ?></a>
</td>
<td<?php $pager->sortable_class ('referrer') ?>>
	<?php if (strlen ($log->referrer) > 0) : ?>
	<a href="<?php echo $this->url ($log->referrer) ?>"><?php echo $log->show_url ($log->referrer ()) ?></a>
	<?php endif; ?>
</td>
<td style="width:7em" class="center<?php $pager->sortable_class ('ip', false) ?>">
	<a target="_blank" href="<?php echo $lookup.$log->ip ?>"><?php echo htmlspecialchars ($log->ip) ?></a>
</td>
<td style="width: 16px" class="lastcol">
	<a href="#" onclick="return add_log_item(<?php echo $log->id ?>)"><img src="<?php echo $this->url () ?>/images/add.png" width="16" height="16" alt="Add"/></a>
</td>