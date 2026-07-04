<?php

namespace Redirection\ImportExport\Format;

use Redirection\ImportExport\FormatHandler;
use Redirection\ImportExport\ImportRedirect;

/**
 * @phpstan-import-type GroupExport from \Red_Group
 * @phpstan-import-type ImportResult from \Redirection\ImportExport\FormatHandler
 */
class Rss extends FormatHandler {
	public function force_download() {
		header( 'Content-type: text/xml; charset=' . get_option( 'blog_charset' ), true );
	}

	/**
	 * @param array<\Red_Item>  $items
	 * @param array<GroupExport> $groups
	 * @return string
	 */
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
	<description><?php echo esc_html( get_bloginfo_rss( 'description' ) ); ?></description>
	<pubDate><?php echo esc_html( (string) mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'gmt' ), false ) ); ?></pubDate>
	<generator>
		<?php echo esc_html( 'http://wordpress.org/?v=' ); ?>
		<?php bloginfo_rss( 'version' ); ?>
	</generator>
	<language><?php echo esc_html( get_option( 'rss_language' ) ); ?></language>

		<?php foreach ( $items as $log ) : ?>
	<item>
		<title><?php echo esc_html( $log->get_url() ); ?></title>
		<link><![CDATA[<?php echo esc_url( home_url() ) . esc_url( $log->get_url() ); ?>]]></link>
		<pubDate><?php echo esc_html( gmdate( 'D, d M Y H:i:s +0000', intval( $log->get_last_hit(), 10 ) ) ); ?></pubDate>
		<guid isPermaLink="false"><?php echo esc_html( (string) $log->get_id() ); ?></guid>
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

	/**
	 * @param mixed $group Group resolver.
	 * @param ImportRedirect $redirect Redirect saver.
	 * @param string $filename Path to the file to import.
	 * @param bool $is_dry_run Whether this is a dry run.
	 * @return ImportResult
	 */
	public function load( $group, $redirect, $filename, $is_dry_run, array $options = [] ) {
		unset( $options );
		return $this->get_import_result( $group, $redirect );
	}
}
