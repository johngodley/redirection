<?php

namespace Redirection\ImportExport\Importer;

use Redirection\ImportExport\FormatHandler;
use Redirection\ImportExport\ImportGroup;
use Redirection\ImportExport\ImportRedirect;

/**
 * @phpstan-import-type ImportResult from FormatHandler
 * @phpstan-type ImporterInfo array{
 *   id: string,
 *   name: string,
 *   description: string,
 *   source: string,
 *   total: int
 * }
 */
abstract class Plugin {
	/**
	 * @return ImportResult
	 */
	protected function get_empty_results() {
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
		if ( ! $this->supports_preview() ) {
			return $this->get_empty_results();
		}

		return $this->preview_redirect_items( $group_id, $options, $this->get_redirect_items() );
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
		$group = new ImportGroup( $group_id, $options );
		$import = new ImportRedirect( $options );

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
			'groups_updated' => 0,
			'groups_ignored' => 0,
			'logs_imported' => 0,
			'errors_imported' => 0,
			'settings_imported' => 0,
			'preview' => $import->get_preview_items(),
		];
	}

	/**
	 * @return bool
	 */
	public function supports_preview() {
		return false;
	}

	/**
	 * Import using a specific importer instance.
	 *
	 * @param int $group_id Target group ID.
	 * @param array<string, bool|string> $options Import options.
	 * @return ImportResult
	 */
	public function import_plugin( $group_id, array $options = [] ) {
		return $this->import_redirect_items( $group_id, $options, $this->get_redirect_items() );
	}

	/**
	 * @return array<int, array<string, mixed>|false>
	 */
	protected function get_redirect_items() {
		return [];
	}

	/**
	 * Get importer summary data used by UI/CLI.
	 *
	 * @return ImporterInfo|false
	 */
	abstract public function get_data();
}
