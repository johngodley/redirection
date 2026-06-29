<?php

namespace Redirection\ImportExport;

/**
 * Export redirects to a file format.
 *
 * @phpstan-import-type GroupJson from \Red_Group
 * @phpstan-import-type ExportResult from \Redirection\ImportExport\FormatHandler
 */
class ExportService {
	const PREVIEW_LIMIT = 20;

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
		return $this->export_scope( 'module', $module_name_or_id, $format );
	}

	/**
	 * @param 'all'|'module'|'group' $scope_type
	 * @param string|int $scope_value
	 * @param string $format
	 * @return array{data: string, total: int, exporter: FormatHandler}|false
	 */
	public function export_scope( $scope_type, $scope_value, $format ) {
		$exporter = $this->formats->create( $format );
		if ( $exporter === false ) {
			return false;
		}

		$data = $this->get_export_data_for_scope( $scope_type, $scope_value );
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
	 * @param 'all'|'module'|'group' $scope_type
	 * @param string|int $scope_value
	 * @param string $format
	 * @return array{total: int, estimated_size: int}|false
	 */
	public function preview_scope( $scope_type, $scope_value, $format ) {
		$exporter = $this->formats->create( $format );
		if ( $exporter === false ) {
			return false;
		}

		$data = $this->get_export_data_for_scope( $scope_type, $scope_value );
		if ( $data === false ) {
			return false;
		}

		return [
			'total' => count( $data['items'] ),
			'estimated_size' => $this->get_estimated_export_size( $exporter, $data['items'], $data['groups'] ),
		];
	}

	/**
	 * @param 'all'|'module'|'group' $scope_type
	 * @param string|int $scope_value
	 * @return array{groups: array<GroupJson>, items: array<\Red_Item>}|false
	 */
	private function get_export_data_for_scope( $scope_type, $scope_value ) {
		if ( $scope_type === 'all' || $scope_value === 'all' || $scope_value === 0 ) {
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

		if ( $scope_type === 'group' ) {
			$group = $this->groups->get( intval( $scope_value, 10 ) );
			if ( $group === false ) {
				return false;
			}

			return [
				'groups' => [ $group->to_json() ],
				'items' => $this->redirects->get_all_for_group( $group->get_id() ),
			];
		}

		$module = $this->get_module( $scope_value );
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

	/**
	 * @param FormatHandler $exporter
	 * @param array<\Red_Item> $items
	 * @param array<GroupJson> $groups
	 * @return int
	 */
	private function get_estimated_export_size( FormatHandler $exporter, array $items, array $groups ) {
		$total = count( $items );
		if ( $total === 0 ) {
			return 0;
		}

		if ( $total <= self::PREVIEW_LIMIT ) {
			return strlen( $exporter->get_data( $items, $groups ) );
		}

		$sample_items = array_slice( $items, 0, self::PREVIEW_LIMIT );
		$sample_groups = $this->get_groups_for_items( $sample_items, $groups );
		$base_size = strlen( $exporter->get_data( [], [] ) );
		$sample_size = strlen( $exporter->get_data( $sample_items, $sample_groups ) );
		$item_size = max( 0, $sample_size - $base_size ) / count( $sample_items );

		return intval( round( $base_size + ( $item_size * $total ) ), 10 );
	}

	/**
	 * @param array<\Red_Item> $items
	 * @param array<GroupJson> $groups
	 * @return array<GroupJson>
	 */
	private function get_groups_for_items( array $items, array $groups ) {
		$group_ids = array_map(
			static function ( \Red_Item $item ) {
				return $item->get_group_id();
			},
			$items
		);

		return array_values(
			array_filter(
				$groups,
				static function ( array $group ) use ( $group_ids ) {
					return in_array( intval( $group['id'], 10 ), $group_ids, true );
				}
			)
		);
	}
}
