<?php

namespace Redirection\ImportExport\Importer;

use Redirection\ImportExport\FormatHandler;
use Redirection\ImportExport\ImportGroup;
use Redirection\ImportExport\ImportRedirect;

/**
 * @phpstan-import-type ImportResult from FormatHandler
 * @phpstan-import-type ImporterInfo from Plugin
 */
class SafeRedirectManager extends Plugin {
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
		$posts = $this->get_redirect_posts();
		$items = array();

		foreach ( $posts as $post ) {
			$items[] = $this->get_item_for_post( $post );
		}

		return $items;
	}

	/**
	 * Import redirects from Safe Redirect Manager.
	 *
	 * @param int $group_id Target group ID.
	 * @param array<string, bool|string> $options Import options.
	 * @return ImportResult
	 */
	public function import_plugin( $group_id, array $options = [] ) {
		$group = new ImportGroup( $group_id, $options );
		$options['dry_run'] = false;
		$import = new ImportRedirect( $options );

		foreach ( $this->get_redirect_posts() as $post ) {
			$item = $this->get_item_for_post( $post );

			if ( $item === false ) {
				continue;
			}

			$saved = $import->save( $item, $group );
			if ( $saved && ! empty( $options['delete_source'] ) && isset( $post['post_id'] ) ) {
				$this->delete_redirect_meta( intval( $post['post_id'], 10 ) );
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
	 * @return array<int, array<string, string|int>>
	 */
	private function get_redirect_posts() {
		global $wpdb;

		$redirects = $wpdb->get_results(
			"SELECT {$wpdb->prefix}postmeta.* FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta.post_id WHERE {$wpdb->prefix}postmeta.meta_key LIKE '_redirect_rule_%' AND {$wpdb->prefix}posts.post_status='publish'"
		);

		$by_post = array();
		foreach ( $redirects as $redirect ) {
			if ( ! isset( $by_post[ $redirect->post_id ] ) ) {
				$by_post[ $redirect->post_id ] = array(
					'post_id' => intval( $redirect->post_id, 10 ),
				);
			}

			$by_post[ $redirect->post_id ][ str_replace( '_redirect_rule_', '', $redirect->meta_key ) ] = $redirect->meta_value;
		}

		return array_values( $by_post );
	}

	/**
	 * Create a Redirection item from a collected SRM post meta map.
	 *
	 * @param array<string, int|string> $post Map of SRM fields for a single link.
	 * @return array<string, mixed>|false
	 */
	private function get_item_for_post( $post ) {
		return $this->mapper->safe_redirect_manager( $post );
	}

	/**
	 * @param int $post_id Post ID.
	 * @return void
	 */
	private function delete_redirect_meta( $post_id ) {
		delete_post_meta( $post_id, '_redirect_rule_from' );
		delete_post_meta( $post_id, '_redirect_rule_to' );
		delete_post_meta( $post_id, '_redirect_rule_status_code' );
		delete_post_meta( $post_id, '_redirect_rule_from_regex' );
	}

	/**
	 * Get importer summary for Safe Redirect Manager.
	 *
	 * @return ImporterInfo|false
	 */
	public function get_data() {
		global $wpdb;

		$total = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta.post_id WHERE {$wpdb->prefix}postmeta.meta_key = '_redirect_rule_from' AND {$wpdb->prefix}posts.post_status='publish'"
		);

		if ( $total !== null ) {
			return array(
				'id' => 'safe-redirect-manager',
				'name' => 'Safe Redirect Manager',
				'description' => __( 'Redirects created by Safe Redirect Manager.', 'redirection' ),
				'source' => __( 'WordPress posts and post meta', 'redirection' ),
				'total' => intval( $total, 10 ),
			);
		}

		return false;
	}
}
