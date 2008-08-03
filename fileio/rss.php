<?php

class Red_Rss_File extends Red_FileIO
{
	var $title;
	
	function collect ($module)
	{
		$pager = new RE_Pager ($_GET, $_SERVER['REQUEST_URI'], 'created', 'DESC', 'log');
		$pager->per_page = 100;
		
		$this->name  = $module->name;
		$this->items = RE_Log::get_by_module ($pager, $module->id);
	}
	
	function feed ()
	{
		$title = sprintf ('%s log', $this->name);

		header ('Content-type: text/xml; charset='.get_option ('blog_charset'), true);
		echo '<?xml version="1.0" encoding="'.get_option ('blog_charset').'"?'.">\r\n";
?>
<rss version="2.0" 
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/">
<channel>
	<title><?php echo $title.' - '; bloginfo_rss ('name'); ?></title>
	<link><?php bloginfo_rss('url') ?></link>
	<description><?php bloginfo_rss("description") ?></description>
	<pubDate><?php echo htmlspecialchars (mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false)); ?></pubDate>
	<generator><?php echo htmlspecialchars ('http://wordpress.org/?v='); bloginfo_rss ('version'); ?></generator>
	<language><?php echo get_option ('rss_language'); ?></language>
<?php
		if (count ($this->items) > 0)
		{
			foreach ($this->items as $log) : ?>
	<item>
		<title><![CDATA[<?php echo $log->url; ?>]]></title>
		<link><![CDATA[<?php bloginfo ('home'); echo $log->url; ?>]]></link>
		<pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', $log->created_at, false); ?></pubDate>
		<guid isPermaLink="false"><?php print($log->id); ?></guid>
		<description><![CDATA[<?php echo $log->url; ?>]]></description>
		<content:encoded><![CDATA[<?php if ($log->referrer) echo 'Referred by '.$log->referrer; ?>]]></content:encoded>
	</item>
		<?php endforeach; } ?>
</channel>
</rss>
<?php
		die();
	}
}
	
?>