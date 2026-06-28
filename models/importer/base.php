<?php

/**
 * @phpstan-import-type ImportResult from \Redirection\ImportExport\FormatHandler
 * @phpstan-type ImporterInfo array{
 *   id: string,
 *   name: string,
 *   description: string,
 *   source: string,
 *   total: int
 * }
 */
abstract class Red_Plugin_Importer {
	/**
	 * @var array<string, string>
	 */
	private const IMPORTERS = array(
		'wp-simple-redirect' => 'Red_Simple301_Importer',
		'seo-redirection' => 'Red_SeoRedirection_Importer',
		'safe-redirect-manager' => 'Red_SafeRedirectManager_Importer',
		'wordpress-old-slugs' => 'Red_WordPressOldSlug_Importer',
		'rank-math' => 'Red_RankMath_Importer',
		'quick-redirects' => 'Red_QuickRedirect_Importer',
		'pretty-links' => 'Red_PrettyLinks_Importer',
		'seopress' => 'Red_SEOPress_Importer',
		'slim-seo' => 'Red_SlimSeo_Importer',
		'eps-301-redirects' => 'Red_EPS301Redirects_Importer',
		'fake-redirection' => 'Red_FakeRedirection_Importer',
	);

	/**
	 * @return ImportResult
	 */
	protected function get_empty_results() {
		return [
			'created' => 0,
			'updated' => 0,
			'ignored' => 0,
			'groups_created' => 0,
			'preview' => [],
		];
	}

	/**
	 * @return list<array{id: string, name: string, description: string, source: string, preview_supported: bool, total: int}>
	 */
	public static function get_plugins(): array {
		$results = array();

		foreach ( array_keys( self::IMPORTERS ) as $importer_id ) {
			$importer = self::get_importer( $importer_id );
			if ( ! $importer instanceof Red_Plugin_Importer ) {
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
			'preview' => [],
		];
	}

	/**
	 * Get an importer instance by ID.
	 *
	 * @param string $id Importer identifier.
	 * @return Red_Plugin_Importer|false
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
	 * @param string $plugin   Importer identifier.
	 * @param int    $group_id Target group ID.
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
			'preview' => [],
		];
	}

	/**
	 * Import using a specific importer instance and return result counts.
	 *
	 * @param int $group_id Target group ID.
	 * @param array<string, bool|string> $options Import options.
	 * @return ImportResult
	 */
	public function import_plugin_results( $group_id, array $options = [] ) {
		return $this->import_plugin( $group_id, $options );
	}

	/**
	 * Preview using a specific importer instance and return result counts plus rows.
	 *
	 * @param int $group_id Target group ID.
	 * @param array<string, bool|string> $options Import options.
	 * @return ImportResult
	 */
	public function preview_plugin_results( $group_id, array $options = [] ) {
		return $this->get_empty_results();
	}

	/**
	 * @param int $group_id Target group ID.
	 * @param array<string, bool|string> $options Import options.
	 * @param array<int, array<string, mixed>|false> $items Redirect items.
	 * @return ImportResult
	 */
	protected function preview_redirect_items( $group_id, array $options, array $items ) {
		$options['dry_run'] = true;
		return $this->process_redirect_items( $group_id, $options, $items );
	}

	/**
	 * @param int $group_id Target group ID.
	 * @param array<string, bool|string> $options Import options.
	 * @param array<int, array<string, mixed>|false> $items Redirect items.
	 * @return ImportResult
	 */
	protected function import_redirect_items( $group_id, array $options, array $items ) {
		$options['dry_run'] = false;
		return $this->process_redirect_items( $group_id, $options, $items );
	}

	/**
	 * @param int $group_id Target group ID.
	 * @param array<string, bool|string> $options Import options.
	 * @param array<int, array<string, mixed>|false> $items Redirect items.
	 * @return ImportResult
	 */
	protected function process_redirect_items( $group_id, array $options, array $items ) {
		$group = new \Redirection\ImportExport\ImportGroup( $group_id, $options );
		$import = new \Redirection\ImportExport\ImportRedirect( $options );

		foreach ( $items as $item ) {
			if ( $item === false ) {
				continue;
			}

			$import->save( $item, $group );
		}

		return [
			'created' => $import->get_created(),
			'updated' => $import->get_updated(),
			'ignored' => $import->get_ignored(),
			'groups_created' => $group->get_groups_created(),
			'preview' => $import->get_preview_items(),
		];
	}

	/**
	 * @return bool
	 */
	protected function supports_preview() {
		return false;
	}

	/**
	 * Import using a specific importer instance.
	 *
	 * @param int $group_id Target group ID.
	 * @param array<string, bool|string> $options Import options.
	 * @return ImportResult
	 */
	abstract public function import_plugin( $group_id, array $options = [] );

	/**
	 * Get importer summary data used by UI/CLI.
	 *
	 * @return ImporterInfo|false
	 */
	abstract public function get_data();
}
