<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php screen_icon(); ?>

	<h2><?php _e ('Redirection Support', 'redirection'); ?></h2>

	<?php $this->submenu (true); ?>

	<p style="clear: both"><?php _e ('Redirection has required a great deal of time and effort to develop.  If it\'s been useful to you then you can support this development by <strong>making a small donation of $8</strong>.  This will act as an incentive for me to carry on developing it, providing countless hours of support, and including any enhancements that are suggested.', 'redirection'); ?></p>
	
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_xclick"/>
		<input type="hidden" name="business" value="admin@urbangiraffe.com"/>
		<input type="hidden" name="item_name" value="Redirection"/>
		<input type="hidden" name="amount" value="8.00"/>
		<input type="hidden" name="buyer_credit_promo_code" value=""/>
		<input type="hidden" name="buyer_credit_product_category" value=""/>
		<input type="hidden" name="buyer_credit_shipping_method" value=""/>
		<input type="hidden" name="buyer_credit_user_address_change" value=""/>
		<input type="hidden" name="no_shipping" value="1"/>
		<input type="hidden" name="return" value="http://urbangiraffe.com/plugins/redirection/"/>
		<input type="hidden" name="no_note" value="1"/>
		<input type="hidden" name="currency_code" value="USD"/>
		<input type="hidden" name="tax" value="0"/>
		<input type="hidden" name="lc" value="US"/>
		<input type="hidden" name="bn" value="PP-DonationsBF"/>
		<input type="image" style="border: none; margin: 0 auto; text-align: center; display: block" src="<?php echo $this->url () ?>/images/donate.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!"/>
	</form>
	
	<p><?php _e ('Alternatively, if you are multi-lingual, do consider translating this into another language.  All the necessary localisation files are included and I\'ve written a <a href="http://urbangiraffe.com/articles/translating-wordpress-themes-and-plugins/">full guide to the translation process</a>.', 'redirection'); ?></p>
	
	<h3><?php _e ('Other plugins', 'redirection'); ?></h3>

	<p><?php _e ('You may also be interested in some of my other plugins:', 'redirection'); ?></p>
	
	<ul>
		<li><a href="http://urbangiraffe.com/plugins/headspace2/"><strong><?php _e ('HeadSpace', 'redirection'); ?></strong></a> - <?php _e ('The most complete SEO meta-data manager and all-round general purpose plugin for WordPress.  Replace five or six plugins with one single super-plugin!', 'redirection'); ?></li>
		<li><a href="http://urbangiraffe.com/plugins/search-unleashed/"><strong><?php _e ('Search Unleashed', 'redirection'); ?></strong></a> - <?php _e ('Attractive searches that go beyond the default WordPress search and increase the usefulness of your site.', 'redirection'); ?></li>
		<li><a href="http://urbangiraffe.com/plugins/sniplets/"><strong><?php _e ('Sniplets', 'redirection'); ?></strong></a> - <?php _e ('Very flexible and powerful text insertion that allows you to insert what you want, wherever you want it.', 'redirection'); ?></li>
	</ul>
</div>