<div style="background-color: #CFEBF7; border:1px solid #2580B2; margin:1em 5% 10px; padding:0pt 1em 0pt 1em;">
	<h3>Redirection News</h3>

	<?php foreach ($rss->items AS $item) : ?>
		<h4><a href='<?php echo wp_filter_kses($item['link']); ?>'><?php echo wp_specialchars($item['title']); ?></a> &#8212; <?php printf(__('%s ago'), human_time_diff(strtotime($item['pubdate'], time() ) ) ); ?></h4>
		<p><?php echo $item['description']; ?></p>
	<?php endforeach; ?>
</div>
