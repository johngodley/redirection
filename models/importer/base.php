<?php

/**
 * @phpstan-type ImporterInfo array{
 *   id: string,
 *   name: string,
 *   total: int
 * }
 */
abstract class Red_Plugin_Importer {
	/**
	 * @return list<array{id: string, name: string, total: int}>
	 */
	public static function get_plugins(): array {
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

		foreach ( $importers as $importer_id ) {
			$importer = self::get_importer( $importer_id );
			if ( ! $importer instanceof Red_Plugin_Importer ) {
				continue;
			}

			$data = $importer->get_data();
			if ( $data === false || $data['total'] === 0 ) {
				continue;
			}

			$results[] = $data;
		}

		return $results;
	}

	/**
	 * Get an importer instance by ID.
	 *
	 * @param string $id Importer identifier.
	 * @return Red_Plugin_Importer|false
	 */
	public static function get_importer( string $id ) {
		if ( $id === 'wp-simple-redirect' ) {
			return new Red_Simple301_Importer();
		}

		if ( $id === 'seo-redirection' ) {
			return new Red_SeoRedirection_Importer();
		}

		if ( $id === 'safe-redirect-manager' ) {
			return new Red_SafeRedirectManager_Importer();
		}

		if ( $id === 'wordpress-old-slugs' ) {
			return new Red_WordPressOldSlug_Importer();
		}

		if ( $id === 'rank-math' ) {
			return new Red_RankMath_Importer();
		}

		if ( $id === 'quick-redirects' ) {
			return new Red_QuickRedirect_Importer();
		}

		if ( $id === 'pretty-links' ) {
			return new Red_PrettyLinks_Importer();
		}

		return false;
	}

	/**
	 * Import all redirects for a plugin ID into a target group.
	 *
	 * @param string $plugin   Importer identifier.
	 * @param int    $group_id Target group ID.
	 * @return int Number of imported redirects.
	 */
	public static function import( $plugin, $group_id ) {
		$importer = self::get_importer( $plugin );
		if ( $importer !== false ) {
			return $importer->import_plugin( $group_id );
		}
		return 0;
	}

	/**
	 * Import using a specific importer instance.
	 *
	 * @param int $group_id Target group ID.
	 * @return int Number of imported redirects.
	 */
	abstract public function import_plugin( $group_id );

	/**
	 * Get importer summary data used by UI/CLI.
	 *
	 * @return ImporterInfo|false
	 */
	abstract public function get_data();
}
