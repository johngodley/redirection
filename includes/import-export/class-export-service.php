<?php

namespace Redirection\ImportExport;

/**
 * Export redirects to a file format.
 *
 * @phpstan-import-type GroupJson from \Red_Group
 * @phpstan-import-type ExportResult from \Redirection\ImportExport\FormatHandler
 */
class ExportService {
	/**
	 * @var FormatFactory
	 */
	private $formats;

	/**
	 * @var GroupRepository
	 */
	private $groups;

	/**
	 * @var RedirectRepository
	 */
	private $redirects;

	/**
	 * @var ModuleRepository
	 */
	private $modules;

	/**
	 * @param FormatFactory|null $formats
	 * @param GroupRepository|null $groups
	 * @param RedirectRepository|null $redirects
	 * @param ModuleRepository|null $modules
	 */
	public function __construct( ?FormatFactory $formats = null, ?GroupRepository $groups = null, ?RedirectRepository $redirects = null, ?ModuleRepository $modules = null ) {
		$this->formats = $formats ? $formats : new FormatFactory();
		$this->groups = $groups ? $groups : new GroupRepository();
		$this->redirects = $redirects ? $redirects : new RedirectRepository();
		$this->modules = $modules ? $modules : new ModuleRepository();
	}

	/**
	 * @param string|int $module_name_or_id Module name, ID, or 'all' for all modules.
	 * @param string $format Export format (rss, csv, apache, nginx, json).
	 * @return array{data: string, total: int, exporter: FormatHandler}|false
	 */
	public function export( $module_name_or_id, $format ) {
		$exporter = $this->formats->create( $format );
		if ( $exporter === false ) {
			return false;
		}

		$data = $this->get_export_data( $module_name_or_id );
		if ( $data === false ) {
			return false;
		}

		return [
			'data' => $exporter->get_data( $data['items'], $data['groups'] ),
			'total' => count( $data['items'] ),
			'exporter' => $exporter,
		];
	}

	/**
	 * @param string|int $module_name_or_id
	 * @return array{groups: array<GroupJson>, items: array<\Red_Item>}|false
	 */
	private function get_export_data( $module_name_or_id ) {
		if ( $module_name_or_id === 'all' || $module_name_or_id === 0 ) {
			$groups = $this->groups->get_all();
			$items = $this->redirects->get_all();

			if ( is_array( $groups ) && is_array( $items ) ) {
				return [
					'groups' => $groups,
					'items' => $items,
				];
			}

			return false;
		}

		$module = $this->get_module( $module_name_or_id );
		if ( $module === false ) {
			return false;
		}

		$groups = $this->groups->get_all_for_module( $module->get_id() );
		$items = $this->redirects->get_all_for_module( $module->get_id() );

		if ( ! is_array( $groups ) || ! is_array( $items ) ) {
			return false;
		}

		return [
			'groups' => $groups,
			'items' => $items,
		];
	}

	/**
	 * @param string|int $module_name_or_id
	 * @return \Red_Module|false
	 */
	private function get_module( $module_name_or_id ) {
		$module_id = is_numeric( $module_name_or_id ) ? $module_name_or_id : $this->modules->get_id_for_name( $module_name_or_id );

		return $this->modules->get( intval( $module_id, 10 ) );
	}
}
