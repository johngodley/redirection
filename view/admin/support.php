<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap supporter">
	<?php screen_icon(); ?>
	
	<h2><?php _e ('Redirection Support', 'redirection'); ?></h2>
	<?php $this->render_admin( 'submenu'  ); ?>
	
	<p style="clear: both">
		<?php _e( 'Redirection is free to use - life is wonderful and lovely!  However, it has required a great deal of time and effort to develop and if it has been useful you can help support this development by <strong>making a small donation</strong>.', 'redirection'); ?>
		<?php _e( 'This will act as an incentive for me to carry on developing, providing countless hours of support, and including new features and suggestions. You get some useful software and I get to carry on making it.  Everybody wins.', 'redirection'); ?>
	</p>
	
	<p><?php _e( 'If you are using this plugin in a commercial setup, or feel that it\'s been particularly useful, then you may want to consider a <strong>commercial donation</strong>.', 'redirection' )?>
	
	<ul class="donations">
		<li>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="admin@urbangiraffe.com">
				<input type="hidden" name="item_name" value="Redirection - Individual">
				<input type="hidden" name="amount" value="14.00">
				<input type="hidden" name="buyer_credit_promo_code" value="">
				<input type="hidden" name="buyer_credit_product_category" value="">
				<input type="hidden" name="buyer_credit_shipping_method" value="">
				<input type="hidden" name="buyer_credit_user_address_change" value="">
				<input type="hidden" name="no_shipping" value="1">
				<input type="hidden" name="return" value="http://urbangiraffe.com/plugins/redirection/">
				<input type="hidden" name="no_note" value="1">
				<input type="hidden" name="currency_code" value="USD">
				<input type="hidden" name="tax" value="0">
				<input type="hidden" name="lc" value="US">
				<input type="hidden" name="bn" value="PP-DonationsBF">
				<input type="image" style="border: none" src="<?php echo $this->url () ?>/images/donate.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!"/>
			</form>
			
			<p><strong>$14</strong><br/><?php _e( 'Individual<br/>Donation', 'redirection' ); ?></p>
		</li>
		<li>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="admin@urbangiraffe.com">
				<input type="hidden" name="item_name" value="Redirection - Commercial">
				<input type="hidden" name="buyer_credit_promo_code" value="">
				<input type="hidden" name="buyer_credit_product_category" value="">
				<input type="hidden" name="buyer_credit_shipping_method" value="">
				<input type="hidden" name="buyer_credit_user_address_change" value="">
				<input type="hidden" name="no_shipping" value="1">
				<input type="hidden" name="return" value="http://urbangiraffe.com/plugins/redirection/">
				<input type="hidden" name="no_note" value="1">
				<input type="hidden" name="currency_code" value="USD">
				<input type="hidden" name="tax" value="0">
				<input type="hidden" name="lc" value="US">
				<input type="hidden" name="bn" value="PP-DonationsBF">
				<input type="image" style="border: none" src="<?php echo $this->url () ?>/images/donate.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!"/>
			</form>
			<p><strong>$$$</strong><br/><?php _e( 'Commercial<br/>Donation', 'redirection' ); ?></p>
		</li>
	</ul>
	
	<h3 style="clear: both"><?php _e( 'Translations', 'redirection' )?></h3>
	
	<p><?php _e( 'If you\'re multi-lingual then you may want to consider donating a translation:', 'redirection' )?>
		
	<ul class="translators">
		<?php foreach( $this->locales() AS $language ) : ?>
			<li><?php echo esc_html( $language ); ?></li>
		<?php endforeach; ?>
	</ul>

	<p style="clear: both"><br/><?php _e( 'All translators will have a link to their website placed on the plugin homepage at <a href="http://urbangiraffe.com/plugins/redirection/">UrbanGiraffe</a>, in addition to being an individual supporter.', 'redirection' )?></p>
	<p><?php _e( 'Full details of producing a translation can be found in this <a href="http://urbangiraffe.com/articles/translating-wordpress-themes-and-plugins/">guide to translating WordPress plugins</a>.', 'redirection' )?>
</div>