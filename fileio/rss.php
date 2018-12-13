<?php

class Red_Rss_File extends Red_FileIO {
	public function force_download() {
		header( 'Content-type: text/xml; charset=' . get_option( 'blog_charset' ), true );
	}

	public function get_data( array $items, array $groups ) {
		$xml = '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . ">\r\n";
		ob_start();
		?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/">
<channel>
	<title>Redirection - <?php bloginfo_rss( 'name' ); ?></title>
	<link><?php esc_url( bloginfo_rss( 'url' ) ); ?></link>
	<description><?php esc_html( bloginfo_rss( 'description' ) ); ?></description>
	<pubDate><?php echo esc_html( mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false ) ); ?></pubDate>
	<generator>
		<?php echo esc_html( 'http://wordpress.org/?v=' ); ?>
		<?php bloginfo_rss( 'version' ); ?>
	</generator>
	<language><?php echo esc_html( get_option( 'rss_language' ) ); ?></language>

		<?php foreach ( $items as $log ) : ?>
	<item>
		<title><?php echo esc_html( $log->get_url() ); ?></title>
		<link><![CDATA[<?php echo esc_url( home_url() ) . esc_url( $log->get_url() ); ?>]]></link>
		<pubDate><?php echo esc_html( date( 'D, d M Y H:i:s +0000', intval( $log->get_last_hit(), 10 ) ) ); ?></pubDate>
		<guid isPermaLink="false"><?php echo esc_html( $log->get_id() ); ?></guid>
		<description><?php echo esc_html( $log->get_url() ); ?></description>
	</item>
		<?php endforeach; ?>
</channel>
</rss>
		<?php
		$xml .= ob_get_contents();
		ob_end_clean();

		return $xml;
	}

	function load( $group, $data, $filename = '' ) {
	}
}
