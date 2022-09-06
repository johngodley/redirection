<?php

namespace Redirection\Importer;

use Redirection\Importer\Plugin;

class Plugin_Importer {
	public static function get_plugins() {
		$results = array();

		$importers = array(
			'wp-simple-redirect',
			'seo-redirection',
			'safe-redirect-manager',
			'wordpress-old-slugs',
			'rank-math',
			'quick-redirects',
			'pretty-links',
		);

		foreach ( $importers as $importer ) {
			$importer = self::get_importer( $importer );
			$results[] = $importer->get_data();
		}

		return array_values( array_filter( $results ) );
	}

	public static function get_importer( $id ) {
		if ( $id === 'wp-simple-redirect' ) {
			require_once __DIR__ . '/plugin-simple301.php';
			return new Plugin\Simple_301();
		}

		if ( $id === 'seo-redirection' ) {
			require_once __DIR__ . '/plugin-seo-redirection.php';
			return new Plugin\Seo_Redirection();
		}

		if ( $id === 'safe-redirect-manager' ) {
			require_once __DIR__ . '/plugin-safe-redirect.php';
			return new Plugin\SafeRedirectManager();
		}

		if ( $id === 'wordpress-old-slugs' ) {
			require_once __DIR__ . '/plugin-wordpress.php';
			return new Plugin\WordPress_Canonical();
		}

		if ( $id === 'rank-math' ) {
			require_once __DIR__ . '/plugin-rankmath.php';
			return new Plugin\Rank_Math();
		}

		if ( $id === 'quick-redirects' ) {
			require_once __DIR__ . '/plugin-quick-redirect.php';
			return new Plugin\Quick_Redirect();
		}

		if ( $id === 'pretty-links' ) {
			require_once __DIR__ . '/plugin-prettylinks.php';
			return new Plugin\PrettyLinks();
		}

		return false;
	}

	public static function import( $plugin, $group_id ) {
		$importer = self::get_importer( $plugin );
		if ( $importer ) {
			return $importer->import_plugin( $group_id );
		}

		return 0;
	}
}
