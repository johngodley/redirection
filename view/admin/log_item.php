<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<td width="16" class="center item">
	<input type="checkbox" class="check" name="checkall[]" value="<?php echo $log->id ?>"/>
</td>
<td style="width:9em">
	<a href="<?php echo admin_url( 'admin-ajax.php' ); ?>?action=red_log_show&amp;id=<?php echo $log->id ?>&amp;_ajax_nonce=<?php echo wp_create_nonce( 'redirection-log_'.$log->id )?>" class="show-log">
		<?php echo date (str_replace ('F', 'M', get_option ('date_format')), $log->created) ?>
	</a>
</td>
<td class="info">
	<a class="details" href="<?php echo esc_attr( $log->url ) ?>"><?php echo $log->show_url( $log->url ) ?></a>
</td>
<td>
	<?php if (strlen ($log->referrer) > 0) : ?>
	<a href="<?php echo esc_attr( $this->url ( $log->referrer) ) ?>"><?php echo $log->show_url( $log->referrer() ) ?></a>
	<?php endif; ?>
</td>
<td style="width:9em" class="center">
	<a target="_blank" href="<?php echo $lookup.esc_attr( $log->ip ) ?>"><?php echo esc_html( $log->ip ) ?></a>
</td>
<td style="width: 16px" class="lastcol">
	<a href="#add" class="add-log"><img src="<?php echo $this->url () ?>/images/add.png" width="16" height="16" alt="Add"/></a>
</td>
