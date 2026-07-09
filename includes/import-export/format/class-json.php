<?php

namespace Redirection\ImportExport\Format;

use Redirection\ImportExport\FormatHandler;
use Redirection\ImportExport\FileReader;
use Redirection\ImportExport\ImportGroup;
use Redirection\ImportExport\ImportRedirect;
use Redirection\ImportExport\Parser\JsonParser;

/**
 * @phpstan-import-type GroupExport from \Red_Group
 * @phpstan-import-type ImportResult from \Redirection\ImportExport\FormatHandler
 */
class Json extends FormatHandler {
	/**
	 * @var JsonParser
	 */
	private $parser;

	/**
	 * @var FileReader
	 */
	private $files;

	/**
	 * @param JsonParser|null $parser JSON parser.
	 * @param FileReader|null $files File reader.
	 */
	public function __construct( ?JsonParser $parser = null, ?FileReader $files = null ) {
		$this->parser = $parser ? $parser : new JsonParser();
		$this->files = $files ? $files : new FileReader();
	}

	public function force_download() {
		parent::force_download();

		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename="' . $this->export_filename( 'json' ) . '"' );
	}

	/**
	 * @param array<\Red_Item> $items
	 * @param array<GroupExport> $groups
	 * @return string
	 */
	public function get_data( array $items, array $groups ) {
		$details = $this->get_export_details();

		$data = [
			'plugin' => [
				'version' => $details['version'],
				'date' => $details['date'],
			],
			'redirects' => array_map(
				static function ( $item ) {
					return $item->to_json();
				},
				$items
			),
		];

		if ( count( $groups ) > 0 ) {
			$data['groups'] = $groups;
		}

		return wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . PHP_EOL;
	}

	/**
	 * @param ImportGroup $group Group resolver to import into.
	 * @param ImportRedirect $import_redirect Redirect saver.
	 * @param string $filename Path to the file to import.
	 * @param bool $is_dry_run Whether this is a dry run.
	 * @param array<string, mixed> $options Import options.
	 * @return ImportResult
	 */
	public function load( $group, $import_redirect, $filename, $is_dry_run, array $options = [] ) {
		$data = $this->files->read( $filename );
		if ( $data === false ) {
			return $this->get_import_result( $group, $import_redirect );
		}

		return $this->load_from_string( $group, $import_redirect, $data, $is_dry_run, $options );
	}

	/**
	 * @param ImportGroup $group Group resolver to import into.
	 * @param ImportRedirect $import_redirect Redirect saver.
	 * @param string $data JSON data to import.
	 * @param bool $is_dry_run Whether this is a dry run.
	 * @param array<string, mixed> $options Import options.
	 * @return ImportResult
	 */
	public function load_from_string( $group, $import_redirect, $data, $is_dry_run, array $options = [] ) {
		global $wpdb;

		$parsed = $this->parser->parse( $data );
		if ( $parsed === false ) {
			return $this->get_import_result( $group, $import_redirect );
		}

		$sections = isset( $options['import_sections'] ) && is_array( $options['import_sections'] ) ? $options['import_sections'] : [];
		$all_sections = count( $sections ) === 0;
		$result = [
			'logs_imported' => 0,
			'errors_imported' => 0,
			'settings_imported' => 0,
		];

		if ( ( $all_sections || in_array( 'settings', $sections, true ) ) && is_array( $parsed['settings'] ) ) {
			$settings = \Red_Options::filter_import_export_options( $parsed['settings'] );

			if ( ! $is_dry_run ) {
				\Red_Options::save( $settings );
			}

			$result['settings_imported'] = count( $settings );
		}

		if ( $all_sections || in_array( 'groups', $sections, true ) ) {
			$this->import_groups( $group, $parsed['groups'] );
		}

		if ( $all_sections || in_array( 'redirects', $sections, true ) ) {
			foreach ( $parsed['redirects'] as $item ) {
				$file_redirect_id = isset( $item['id'] ) ? intval( $item['id'], 10 ) : 0;
				$file_group_id = isset( $item['group_id'] ) ? intval( $item['group_id'], 10 ) : 0;

				unset( $item['id'] );

				$saved = $import_redirect->save(
					$item,
					$group,
					$file_group_id,
					isset( $parsed['groups'][ $file_group_id ] ) ? $parsed['groups'][ $file_group_id ] : null,
					$file_redirect_id
				);
				if ( $saved === false ) {
					continue;
				}

				$wpdb->queries = [];
				$wpdb->num_queries = 0;
			}
		}

		if ( $all_sections || in_array( 'logs', $sections, true ) ) {
			$result['logs_imported'] = $this->import_log_rows( $parsed['logs'], $is_dry_run );
		}

		if ( $all_sections || in_array( 'errors_404', $sections, true ) ) {
			$result['errors_imported'] = $this->import_404_rows( $parsed['errors_404'], $is_dry_run );
		}

		return array_merge( $this->get_import_result( $group, $import_redirect ), $result );
	}

	/**
	 * @param ImportGroup $group Group resolver.
	 * @param array<int, array<string, mixed>> $groups
	 * @return void
	 */
	private function import_groups( ImportGroup $group, array $groups ) {
		foreach ( $groups as $group_data ) {
			if ( ! isset( $group_data['id'], $group_data['name'], $group_data['module_id'] ) ) {
				continue;
			}

			$group->import_group( intval( $group_data['id'], 10 ), $group_data );
		}
	}

	/**
	 * @param array<int, array<string, mixed>> $rows
	 * @param bool $is_dry_run
	 * @return int
	 */
	private function import_log_rows( array $rows, $is_dry_run ) {
		global $wpdb;

		$count = 0;

		foreach ( $rows as $row ) {
			if ( ! isset( $row['url'] ) || ! isset( $row['ip'] ) ) {
				continue;
			}

			$count++;
			if ( $is_dry_run ) {
				continue;
			}

			$wpdb->insert(
				$wpdb->prefix . 'redirection_logs',
				[
					'created' => isset( $row['created'] ) ? sanitize_text_field( strval( $row['created'] ) ) : current_time( 'mysql' ),
					'url' => sanitize_text_field( strval( $row['url'] ) ),
					'agent' => isset( $row['agent'] ) ? sanitize_text_field( strval( $row['agent'] ) ) : '',
					'referrer' => isset( $row['referrer'] ) ? sanitize_text_field( strval( $row['referrer'] ) ) : '',
					'domain' => isset( $row['domain'] ) ? sanitize_text_field( strval( $row['domain'] ) ) : '',
					'ip' => sanitize_text_field( strval( $row['ip'] ) ),
					'http_code' => isset( $row['http_code'] ) ? intval( $row['http_code'], 10 ) : 0,
					'request_method' => isset( $row['request_method'] ) ? sanitize_text_field( strval( $row['request_method'] ) ) : '',
					'request_data' => isset( $row['request_data'] ) ? wp_json_encode( $row['request_data'], JSON_UNESCAPED_SLASHES ) : '',
					'redirection_id' => isset( $row['redirection_id'] ) ? intval( $row['redirection_id'], 10 ) : 0,
					'sent_to' => isset( $row['sent_to'] ) ? sanitize_text_field( strval( $row['sent_to'] ) ) : '',
					'redirect_by' => isset( $row['redirect_by_slug'] ) ? sanitize_text_field( strval( $row['redirect_by_slug'] ) ) : ( isset( $row['redirect_by'] ) ? sanitize_text_field( strtolower( strval( $row['redirect_by'] ) ) ) : '' ),
				]
			);
		}

		return $count;
	}

	/**
	 * @param array<int, array<string, mixed>> $rows
	 * @param bool $is_dry_run
	 * @return int
	 */
	private function import_404_rows( array $rows, $is_dry_run ) {
		global $wpdb;

		$count = 0;

		foreach ( $rows as $row ) {
			if ( ! isset( $row['url'] ) || ! isset( $row['ip'] ) ) {
				continue;
			}

			$count++;
			if ( $is_dry_run ) {
				continue;
			}

			$wpdb->insert(
				$wpdb->prefix . 'redirection_404',
				[
					'created' => isset( $row['created'] ) ? sanitize_text_field( strval( $row['created'] ) ) : current_time( 'mysql' ),
					'url' => sanitize_text_field( strval( $row['url'] ) ),
					'agent' => isset( $row['agent'] ) ? sanitize_text_field( strval( $row['agent'] ) ) : '',
					'referrer' => isset( $row['referrer'] ) ? sanitize_text_field( strval( $row['referrer'] ) ) : '',
					'domain' => isset( $row['domain'] ) ? sanitize_text_field( strval( $row['domain'] ) ) : '',
					'ip' => sanitize_text_field( strval( $row['ip'] ) ),
					'http_code' => isset( $row['http_code'] ) ? intval( $row['http_code'], 10 ) : 0,
					'request_method' => isset( $row['request_method'] ) ? sanitize_text_field( strval( $row['request_method'] ) ) : '',
					'request_data' => isset( $row['request_data'] ) ? wp_json_encode( $row['request_data'], JSON_UNESCAPED_SLASHES ) : '',
				]
			);
		}

		return $count;
	}
}
