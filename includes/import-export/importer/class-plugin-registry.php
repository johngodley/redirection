<?php

namespace Redirection\ImportExport\Importer;

use Redirection\ImportExport\FormatHandler;

/**
 * @phpstan-import-type ImportResult from FormatHandler
 */
class PluginRegistry {
	/**
	 * @var array<string, class-string<Plugin>>
	 */
	private const IMPORTERS = array(
		'wp-simple-redirect' => Simple301::class,
		'seo-redirection' => SeoRedirection::class,
		'safe-redirect-manager' => SafeRedirectManager::class,
		'wordpress-old-slugs' => WordpressOldSlugs::class,
		'rank-math' => RankMath::class,
		'quick-redirects' => QuickRedirects::class,
		'pretty-links' => PrettyLinks::class,
		'seopress' => Seopress::class,
		'slim-seo' => SlimSeo::class,
		'eps-301-redirects' => Eps301Redirects::class,
		'fake-redirection' => FakeRedirection::class,
	);

	/**
	 * @return list<array{id: string, name: string, description: string, source: string, preview_supported: bool, total: int}>
	 */
	public static function get_plugins(): array {
		$results = array();

		foreach ( array_keys( self::IMPORTERS ) as $importer_id ) {
			$importer = self::get_importer( $importer_id );
			if ( ! $importer instanceof Plugin ) {
				continue;
			}

			$data = $importer->get_data();
			if ( $data === false || $data['total'] === 0 ) {
				continue;
			}

			$results[] = array_merge(
				$data,
				[
					'preview_supported' => $importer->supports_preview(),
				]
			);
		}

		return $results;
	}

	/**
	 * Preview redirects for a plugin ID into a target group.
	 *
	 * @param string $plugin Importer identifier.
	 * @param int $group_id Target group ID.
	 * @param array<string, bool|string> $options Import options.
	 * @return ImportResult
	 */
	public static function preview( $plugin, $group_id, array $options = [] ) {
		$importer = self::get_importer( $plugin );
		if ( $importer !== false ) {
			return $importer->preview_plugin_results( $group_id, $options );
		}

		return [
			'created' => 0,
			'updated' => 0,
			'ignored' => 0,
			'groups_created' => 0,
			'groups_updated' => 0,
			'groups_ignored' => 0,
			'logs_imported' => 0,
			'errors_imported' => 0,
			'settings_imported' => 0,
			'preview' => [],
		];
	}

	/**
	 * Get an importer instance by ID.
	 *
	 * @param string $id Importer identifier.
	 * @return Plugin|false
	 */
	public static function get_importer( string $id ) {
		if ( ! isset( self::IMPORTERS[ $id ] ) ) {
			return false;
		}

		$class_name = self::IMPORTERS[ $id ];

		if ( ! class_exists( $class_name ) ) {
			return false;
		}

		return new $class_name();
	}

	/**
	 * Import all redirects for a plugin ID into a target group.
	 *
	 * @param string $plugin Importer identifier.
	 * @param int $group_id Target group ID.
	 * @param array<string, bool|string> $options Import options.
	 * @return ImportResult
	 */
	public static function import( $plugin, $group_id, array $options = [] ) {
		$importer = self::get_importer( $plugin );
		if ( $importer !== false ) {
			return $importer->import_plugin_results( $group_id, $options );
		}

		return [
			'created' => 0,
			'updated' => 0,
			'ignored' => 0,
			'groups_created' => 0,
			'groups_updated' => 0,
			'groups_ignored' => 0,
			'logs_imported' => 0,
			'errors_imported' => 0,
			'settings_imported' => 0,
			'preview' => [],
		];
	}
}
