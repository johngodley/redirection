<?php

namespace Redirection\ImportExport;

use Redirection\ImportExport\Sanitizer\CsvSanitizer;

/**
 * Export redirects to a file format.
 *
 * @phpstan-import-type GroupExport from \Red_Group
 * @phpstan-import-type ExportResult from \Redirection\ImportExport\FormatHandler
 */
class ExportService {
	const PREVIEW_LIMIT = 20;
	const BUNDLE_GROUPS = 'group';
	const BUNDLE_SETTINGS = 'setting';

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
			'data' => $exporter->get_data( $data['items'], $this->get_export_groups_for_format( $format, $data['groups'] ) ),
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
			'estimated_size' => $this->get_estimated_export_size( $exporter, $data['items'], $this->get_export_groups_for_format( $format, $data['groups'] ) ),
		];
	}

	/**
	 * @param array<int, string> $types
	 * @param array{scope_type?: 'all'|'module'|'group', scope_value?: string|int} $options
	 * @param string $format
	 * @return array{data: string, total: int}|false
	 */
	public function export_bundle( array $types, array $options, $format ) {
		$types = $this->normalize_bundle_types( $types );
		if ( count( $types ) === 0 ) {
			return false;
		}

		if ( $format === 'csv' && count( $types ) === 1 && $types[0] === self::BUNDLE_GROUPS ) {
			$groups = $this->groups->get_all_for_export();

			return [
				'data' => $this->get_groups_csv( $groups ),
				'total' => count( $groups ),
			];
		}

		if ( $format !== 'json' ) {
			return false;
		}

		$bundle = $this->get_bundle_export_data( $types, $options );

		return [
			'data' => $this->encode_json_export( $bundle['data'] ),
			'total' => $bundle['total'],
		];
	}

	/**
	 * @param array<int, string> $types
	 * @param array{scope_type?: 'all'|'module'|'group', scope_value?: string|int} $options
	 * @param string $format
	 * @return array{total: int, estimated_size: int}|false
	 */
	public function preview_bundle( array $types, array $options, $format ) {
		$export = $this->export_bundle( $types, $options, $format );

		if ( $export === false ) {
			return false;
		}

		return [
			'total' => $export['total'],
			'estimated_size' => strlen( $export['data'] ),
		];
	}

	/**
	 * @param string $format
	 * @param array<string, mixed> $params
	 * @return array{data: string, total: int}|false
	 */
	public function export_groups( $format, array $params = [] ) {
		if ( ! in_array( $format, [ 'csv', 'json' ], true ) ) {
			return false;
		}

		$groups = $this->groups->get_filtered_for_export( $params );

		if ( $format === 'csv' ) {
			return [
				'data' => $this->get_groups_csv( $groups ),
				'total' => count( $groups ),
			];
		}

		return [
			'data' => $this->get_groups_json( $groups ),
			'total' => count( $groups ),
		];
	}

	/**
	 * @param string $format
	 * @param array<string, mixed> $params
	 * @return array{data: string, total: int}|false
	 */
	public function export_redirects( $format, array $params = [] ) {
		$exporter = $this->formats->create( $format );
		if ( $exporter === false ) {
			return false;
		}

		$items = $this->redirects->get_filtered_for_export( $params );
		$groups = $this->get_groups_for_redirect_items( $items );

		return [
			'data' => $exporter->get_data( $items, $this->get_export_groups_for_format( $format, $groups ) ),
			'total' => count( $items ),
		];
	}

	/**
	 * @param 'all'|'module'|'group' $scope_type
	 * @param string|int $scope_value
	 * @return array{groups: array<GroupExport>, items: array<\Red_Item>}|false
	 */
	private function get_export_data_for_scope( $scope_type, $scope_value ) {
		if ( $scope_type === 'all' || $scope_value === 'all' || $scope_value === 0 ) {
			$groups = $this->groups->get_all_for_export();
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
			$group = $this->groups->get_export( intval( $scope_value, 10 ) );
			if ( $group === false ) {
				return false;
			}

			return [
				'groups' => [ $group ],
				'items' => $this->redirects->get_all_for_group( intval( $scope_value, 10 ) ),
			];
		}

		$module = $this->get_module( $scope_value );
		if ( $module === false ) {
			return false;
		}

		$groups = $this->groups->get_all_for_module_export( $module->get_id() );
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
	 * @param array<GroupExport> $groups
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
	 * @param array<GroupExport> $groups
	 * @return array<GroupExport>
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

	/**
	 * @param array<\Red_Item> $items
	 * @return array<GroupExport>
	 */
	private function get_groups_for_redirect_items( array $items ) {
		$groups = [];
		$seen = [];

		foreach ( $items as $item ) {
			$group_id = $item->get_group_id();

			if ( isset( $seen[ $group_id ] ) ) {
				continue;
			}

			$group = $this->groups->get_export( $group_id );
			if ( $group !== false ) {
				$groups[] = $group;
				$seen[ $group_id ] = true;
			}
		}

		return $groups;
	}

	/**
	 * Redirect-only JSON exports should not include group data.
	 *
	 * @param string $format
	 * @param array<GroupExport> $groups
	 * @return array<GroupExport>
	 */
	private function get_export_groups_for_format( $format, array $groups ) {
		if ( $format === 'json' ) {
			return [];
		}

		return $groups;
	}

	/**
	 * @param array<int, string> $types
	 * @return array<int, string>
	 */
	private function normalize_bundle_types( array $types ) {
		$allowed = [ 'redirect', 'log', '404', self::BUNDLE_GROUPS, self::BUNDLE_SETTINGS ];

		return array_values(
			array_unique(
				array_values(
					array_filter(
						array_map( 'strval', $types ),
						static function ( $type ) use ( $allowed ) {
							return in_array( $type, $allowed, true );
						}
					)
				)
			)
		);
	}

	/**
	 * @param array<int, string> $types
	 * @param array{scope_type?: 'all'|'module'|'group', scope_value?: string|int} $options
	 * @return array{data: array<string, mixed>, total: int}
	 */
	private function get_bundle_export_data( array $types, array $options ) {
		$data = $this->get_bundle_export_header();
		$total = 0;
		$redirect_scope_type = isset( $options['scope_type'] ) ? $options['scope_type'] : 'all';
		$redirect_scope_value = isset( $options['scope_value'] ) ? $options['scope_value'] : 'all';

		$this->append_bundle_redirects( $data, $total, $types, $redirect_scope_type, $redirect_scope_value );
		$this->append_bundle_groups( $data, $total, $types );
		$this->append_bundle_logs( $data, $total, $types );
		$this->append_bundle_404s( $data, $total, $types );
		$this->append_bundle_settings( $data, $total, $types );

		return [
			'data' => $data,
			'total' => $total,
		];
	}

	/**
	 * @param array<GroupExport> $groups
	 * @return string
	 */
	private function get_groups_csv( array $groups ) {
		$rows = [];

		foreach ( $groups as $group ) {
			$rows[] = [
				intval( $group['id'], 10 ),
				strval( $group['name'] ),
				intval( $group['module_id'], 10 ),
				strval( $group['status'] ),
			];
		}

		return $this->build_csv_data( [ 'id', 'name', 'module_id', 'status' ], $rows );
	}

	/**
	 * @param array<GroupExport> $groups
	 * @return string
	 */
	private function get_groups_json( array $groups ) {
		return $this->encode_json_export(
			array_merge(
				$this->get_bundle_export_header(),
				[
					'groups' => $groups,
				]
			)
		);
	}

	/**
	 * @return array{plugin: array{version: string, date: string}}
	 */
	private function get_bundle_export_header() {
		$details = ExportDetails::get();

		return [
			'plugin' => [
				'version' => $details['version'],
				'date' => $details['date'],
			],
		];
	}

	/**
	 * @param array<string, mixed> $data
	 * @return string
	 */
	private function encode_json_export( array $data ) {
		return wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . PHP_EOL;
	}

	/**
	 * @param array<string, mixed> $data
	 * @param int $total
	 * @param array<int, string> $types
	 * @param 'all'|'module'|'group' $redirect_scope_type
	 * @param string|int $redirect_scope_value
	 * @return void
	 */
	private function append_bundle_redirects( array &$data, int &$total, array $types, $redirect_scope_type, $redirect_scope_value ) {
		if ( ! in_array( 'redirect', $types, true ) ) {
			return;
		}

		$redirect_data = $this->get_export_data_for_scope( $redirect_scope_type, $redirect_scope_value );
		if ( $redirect_data === false ) {
			return;
		}

		$data['groups'] = $redirect_data['groups'];
		$data['redirects'] = array_map(
			static function ( \Red_Item $item ) {
				return $item->to_json();
			},
			$redirect_data['items']
		);
		$total += count( $redirect_data['items'] );

		if ( in_array( self::BUNDLE_GROUPS, $types, true ) ) {
			$total += count( $redirect_data['groups'] );
		}
	}

	/**
	 * @param array<string, mixed> $data
	 * @param int $total
	 * @param array<int, string> $types
	 * @return void
	 */
	private function append_bundle_groups( array &$data, int &$total, array $types ) {
		if ( in_array( 'redirect', $types, true ) || ! in_array( self::BUNDLE_GROUPS, $types, true ) ) {
			return;
		}

		$groups = $this->groups->get_all_for_export();
		$data['groups'] = $groups;
		$total += count( $groups );
	}

	/**
	 * @param array<string, mixed> $data
	 * @param int $total
	 * @param array<int, string> $types
	 * @return void
	 */
	private function append_bundle_logs( array &$data, int &$total, array $types ) {
		if ( ! in_array( 'log', $types, true ) ) {
			return;
		}

		$data['logs'] = \Red_Redirect_Log::get_export_bundle_rows();
		$total += count( $data['logs'] );
	}

	/**
	 * @param array<string, mixed> $data
	 * @param int $total
	 * @param array<int, string> $types
	 * @return void
	 */
	private function append_bundle_404s( array &$data, int &$total, array $types ) {
		if ( ! in_array( '404', $types, true ) ) {
			return;
		}

		$data['errors_404'] = \Red_404_Log::get_export_bundle_rows();
		$total += count( $data['errors_404'] );
	}

	/**
	 * @param array<string, mixed> $data
	 * @param int $total
	 * @param array<int, string> $types
	 * @return void
	 */
	private function append_bundle_settings( array &$data, int &$total, array $types ) {
		if ( ! in_array( self::BUNDLE_SETTINGS, $types, true ) ) {
			return;
		}

		$data['settings'] = \Red_Options::get_import_export_options();
		$total += count( $data['settings'] );
	}

	/**
	 * @param array<int, string> $header
	 * @param array<int, array<int, string|int>> $rows
	 * @return string
	 */
	private function build_csv_data( array $header, array $rows ) {
		$sanitizer = new CsvSanitizer();

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Temporary in-memory export buffer
		$stdout = fopen( 'php://temp', 'w+' );
		if ( $stdout === false ) {
			return '';
		}

		fputcsv( $stdout, $header );

		foreach ( $rows as $row ) {
			fputcsv( $stdout, array_map( [ $sanitizer, 'escape' ], $row ) );
		}

			rewind( $stdout );

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread -- Temporary in-memory export buffer
			$data = stream_get_contents( $stdout );
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Temporary in-memory export buffer
			fclose( $stdout );

		return $data === false ? '' : $data;
	}
}
