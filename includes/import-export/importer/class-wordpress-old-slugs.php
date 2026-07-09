<?php

namespace Redirection\ImportExport\Importer;

use Redirection\ImportExport\ImportGroup;
use Redirection\ImportExport\ImportRedirect;
use Redirection\ImportExport\FormatHandler;

/**
 * @phpstan-import-type ImportResult from FormatHandler
 * @phpstan-import-type ImporterInfo from Plugin
 */
class WordpressOldSlugs extends Plugin {
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
	 * @return ImportResult
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
			'groups_updated' => 0,
			'groups_ignored' => 0,
			'logs_imported' => 0,
			'errors_imported' => 0,
			'settings_imported' => 0,
			'preview' => $import->get_preview_items(),
		];
	}

	/**
	 * @return array<int, object{meta_id: int|string, post_id: int|string, meta_value: string}>
	 */
	private function get_redirect_rows() {
		global $wpdb;

		$post_types = $this->get_supported_post_types();
		if ( count( $post_types ) === 0 ) {
			return [];
		}

		$sql = "SELECT {$wpdb->postmeta}.* FROM {$wpdb->postmeta} INNER JOIN {$wpdb->posts} ON {$wpdb->posts}.ID={$wpdb->postmeta}.post_id " .
			"WHERE {$wpdb->postmeta}.meta_key = '_wp_old_slug' AND {$wpdb->postmeta}.meta_value != '' AND {$wpdb->posts}.post_status='publish' AND {$wpdb->posts}.post_type IN (" .
			implode( ', ', array_fill( 0, count( $post_types ), '%s' ) ) . ')';

		return $wpdb->get_results(
			$wpdb->prepare(
				$sql,
				$post_types
			)
		);
	}

	/**
	 * Build redirect data for a WordPress old slug row.
	 *
	 * @param object{meta_id: int|string, post_id: int|string, meta_value: string} $redirect Row from postmeta/posts join.
	 * @return array<string, mixed>|false
	 */
	private function get_item_for_redirect( $redirect ) {
		$new = get_permalink( intval( $redirect->post_id, 10 ) );
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

		$post_types = $this->get_supported_post_types();
		if ( count( $post_types ) === 0 ) {
			return false;
		}

		$sql = "SELECT COUNT(*) FROM {$wpdb->postmeta} INNER JOIN {$wpdb->posts} ON {$wpdb->posts}.ID={$wpdb->postmeta}.post_id WHERE {$wpdb->postmeta}.meta_key = '_wp_old_slug' AND {$wpdb->postmeta}.meta_value != '' AND {$wpdb->posts}.post_status='publish' AND {$wpdb->posts}.post_type IN (" .
			implode( ', ', array_fill( 0, count( $post_types ), '%s' ) ) . ')';

		$total = $wpdb->get_var(
			$wpdb->prepare(
				$sql,
				$post_types
			)
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

	/**
	 * Get post types that can provide old-slug redirects.
	 *
	 * @return array<int, string>
	 */
	private function get_supported_post_types() {
		$post_types = get_post_types( [ 'public' => true ], 'names' );

		unset( $post_types['attachment'] );

		return array_values( $post_types );
	}
}
