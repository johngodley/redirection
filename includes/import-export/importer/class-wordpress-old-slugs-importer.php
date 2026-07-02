<?php

namespace Redirection\ImportExport\Importer;

use Redirection\ImportExport\ImportGroup;
use Redirection\ImportExport\ImportRedirect;

/**
 * @phpstan-import-type ImporterInfo from PluginImporter
 */
class WordpressOldSlugsImporter extends PluginImporter {
	/**
	 * @var RedirectItemMapper
	 */
	private $mapper;

	public function __construct( ?RedirectItemMapper $mapper = null ) {
		$this->mapper = $mapper ? $mapper : new RedirectItemMapper();
	}

	/**
	 * @return bool
	 */
	public function supports_preview() {
		return true;
	}

	/**
	 * @return array<int, array<string, mixed>|false>
	 */
	protected function get_redirect_items() {
		$items = array();

		foreach ( $this->get_redirect_rows() as $redirect ) {
			$items[] = $this->get_item_for_redirect( $redirect );
		}

		return $items;
	}

	/**
	 * Import redirects for WordPress old slugs.
	 *
	 * @param int $group_id Target group ID.
	 * @param array<string, bool|string> $options Import options.
	 * @phpstan-return array{
	 *   created: int,
	 *   updated: int,
	 *   ignored: int,
	 *   groups_created: int,
	 *   preview: array<int, array{
	 *     source: string,
	 *     target: string,
	 *     code: int,
	 *     regex: bool,
	 *     group: string,
	 *     result: 'created'|'updated'|'ignored',
	 *     redirect_id?: int
	 *   }>
	 * }
	 * @return array{
	 *   created: int,
	 *   updated: int,
	 *   ignored: int,
	 *   groups_created: int,
	 *   preview: array<int, array{
	 *     source: string,
	 *     target: string,
	 *     code: int,
	 *     regex: bool,
	 *     group: string,
	 *     result: 'created'|'updated'|'ignored',
	 *     redirect_id?: int
	 *   }>
	 * }
	 */
	public function import_plugin( $group_id, array $options = [] ) {
		$group = new ImportGroup( $group_id, $options );
		$options['dry_run'] = false;
		$import = new ImportRedirect( $options );

		foreach ( $this->get_redirect_rows() as $redirect ) {
			$item = $this->get_item_for_redirect( $redirect );
			if ( $item === false ) {
				continue;
			}

			$saved = $import->save( $item, $group );
			if ( $saved && ! empty( $options['delete_source'] ) ) {
				delete_metadata_by_mid( 'post', intval( $redirect->meta_id, 10 ) );
			}
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
	 * @return array<int, object>
	 */
	private function get_redirect_rows() {
		global $wpdb;

		return $wpdb->get_results(
			"SELECT {$wpdb->prefix}postmeta.* FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta.post_id " .
			"WHERE {$wpdb->prefix}postmeta.meta_key = '_wp_old_slug' AND {$wpdb->prefix}postmeta.meta_value != '' AND {$wpdb->prefix}posts.post_status='publish' AND {$wpdb->prefix}posts.post_type IN ('page', 'post')"
		);
	}

	/**
	 * Build redirect data for a WordPress old slug row.
	 *
	 * @param stdClass $redirect Row from postmeta/posts join.
	 * @return array<string, mixed>|false
	 */
	private function get_item_for_redirect( $redirect ) {
		$new = get_permalink( $redirect->post_id );
		if ( $new === false ) {
			return false;
		}

		return $this->mapper->wordpress_old_slug( $new, $redirect->meta_value );
	}

	/**
	 * Get importer summary for WordPress old slugs.
	 *
	 * @return ImporterInfo|false
	 */
	public function get_data() {
		global $wpdb;

		$total = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta.post_id WHERE {$wpdb->prefix}postmeta.meta_key = '_wp_old_slug' AND {$wpdb->prefix}postmeta.meta_value != '' AND {$wpdb->prefix}posts.post_status='publish' AND {$wpdb->prefix}posts.post_type IN ('page', 'post')"
		);

		if ( $total !== null && intval( $total, 10 ) > 0 ) {
			return array(
				'id' => 'wordpress-old-slugs',
				'name' => __( 'WordPress permalink redirect', 'redirection' ),
				'description' => __( 'Redirects created by WordPress.', 'redirection' ),
				'source' => __( 'WordPress post meta', 'redirection' ),
				'total' => intval( $total, 10 ),
			);
		}

		return false;
	}
}
