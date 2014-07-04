<?php

class Red_Rss_File extends Red_FileIO {
	function export( array $items ) {
		header( 'Content-type: text/xml; charset='.get_option( 'blog_charset' ), true );
		echo '<?xml version="1.0" encoding="'.get_option( 'blog_charset' ).'"?'.">\r\n";
?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/">
<channel>
	<title>Redirection <?php ' - '; bloginfo_rss( 'name' ); ?></title>
	<link><?php esc_url( bloginfo_rss( 'url' ) ) ?></link>
	<description><?php esc_html( bloginfo_rss( 'description' ) ) ?></description>
	<pubDate><?php echo esc_html( mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false ) ); ?></pubDate>
	<generator><?php echo esc_html( 'http://wordpress.org/?v=' ); bloginfo_rss( 'version' ); ?></generator>
	<language><?php echo esc_html( get_option( 'rss_language' ) ); ?></language>
<?php
	foreach ( (array)$items as $log ) : ?>
	<item>
		<title><?php echo esc_html( $log->url ); ?></title>
		<link><![CDATA[<?php echo esc_url( home_url() ); echo esc_url( $log->url ); ?>]]></link>
		<pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', $log->created, false); ?></pubDate>
		<guid isPermaLink="false"><?php echo esc_html( $log->id ); ?></guid>
		<description><?php echo esc_html( $log->url ); ?></description>
		<?php if ( $log->referrer ) : ?>
		<content:encoded><?php printf( __( 'Referred by %s' ), esc_html( $log->referrer ) ); ?></content:encoded>
		<?php endif; ?>
	</item>
	<?php endforeach; ?>
</channel>
</rss>
<?php
	}

	function load( $group, $data, $filename = '' ) {
	}
}
