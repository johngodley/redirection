<?php

namespace Redirection\ImportExport\Format;

use Redirection\ImportExport\FormatHandler;
use Redirection\ImportExport\FileReader;
use Redirection\ImportExport\ImportGroup;
use Redirection\ImportExport\ImportRedirect;
use Redirection\ImportExport\Parser\JsonParser;

/**
 * @phpstan-import-type GroupJson from \Red_Group
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
	 * @param array<GroupJson> $groups
	 * @return string
	 */
	public function get_data( array $items, array $groups ) {
		$details = $this->get_export_details();

		$data = [
			'plugin' => [
				'version' => $details['version'],
				'date' => $details['date'],
			],
			'groups' => $groups,
			'redirects' => array_map(
				static function ( $item ) {
					return $item->to_json();
				},
				$items
			),
		];

		return wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . PHP_EOL;
	}

	/**
	 * @param ImportGroup $group Group resolver to import into.
	 * @param ImportRedirect $import_redirect Redirect saver.
	 * @param string $filename Path to the file to import.
	 * @param bool $is_dry_run Whether this is a dry run.
	 * @return ImportResult
	 */
	public function load( $group, $import_redirect, $filename, $is_dry_run ) {
		$data = $this->files->read( $filename );
		if ( $data === false ) {
			return $this->get_import_result( $group, $import_redirect );
		}

		return $this->load_from_string( $group, $import_redirect, $data, $is_dry_run );
	}

	/**
	 * @param ImportGroup $group Group resolver to import into.
	 * @param ImportRedirect $import_redirect Redirect saver.
	 * @param string $data JSON data to import.
	 * @param bool $is_dry_run Whether this is a dry run.
	 * @return ImportResult
	 */
	public function load_from_string( $group, $import_redirect, $data, $is_dry_run ) {
		global $wpdb;

		$parsed = $this->parser->parse( $data );
		if ( $parsed === false ) {
			return $this->get_import_result( $group, $import_redirect );
		}

		foreach ( $parsed['redirects'] as $item ) {
			$file_redirect_id = isset( $item['id'] ) ? intval( $item['id'], 10 ) : 0;

			unset( $item['id'] );

			$saved = $import_redirect->save(
				$item,
				$group,
				$item['group_id'],
				isset( $parsed['groups'][ intval( $item['group_id'], 10 ) ] ) ? $parsed['groups'][ intval( $item['group_id'], 10 ) ] : null,
				$file_redirect_id
			);
			if ( $saved === false ) {
				continue;
			}

			$wpdb->queries = [];
			$wpdb->num_queries = 0;
		}

		return $this->get_import_result( $group, $import_redirect );
	}
}
