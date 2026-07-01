<?php

namespace Redirection\ImportExport;

/**
 * Import redirects from uploaded files.
 *
 * @phpstan-import-type ImportResult from \Redirection\ImportExport\FormatHandler
 */
class ImportService {
	/**
	 * @var FormatFactory
	 */
	private $formats;

	/**
	 * @param FormatFactory|null $formats
	 */
	public function __construct( ?FormatFactory $formats = null ) {
		$this->formats = $formats ? $formats : new FormatFactory();
	}

	/**
	 * @param int $group_id Group ID to import into.
	 * @param array{name: string, tmp_name: string, type: string, error: int, size: int} $file
	 * @param array<string, bool|string|array<int, string>> $options Import options.
	 * @return ImportResult
	 */
	public function import( $group_id, array $file, array $options = [] ) {
		$group = new ImportGroup( $group_id, $options );
		$redirect = new ImportRedirect( $options );
		$importer = null;

		if ( isset( $options['format'] ) && is_string( $options['format'] ) ) {
			$importer = $this->formats->create( $options['format'] );
		}

		if ( $importer === false || $importer === null ) {
			$importer = $this->formats->create_importer_for_filename( $file['name'] );
		}

		return $importer->load(
			$group,
			$redirect,
			$file['tmp_name'],
			isset( $options['dry_run'] ) ? $options['dry_run'] === true : false,
			$options
		);
	}
}
