<?php

namespace Redirection\ImportExport;

use Redirection\ImportExport\Format\Apache;
use Redirection\ImportExport\Format\Csv;
use Redirection\ImportExport\Format\Json;
use Redirection\ImportExport\Format\Nginx;
use Redirection\ImportExport\Format\Rss;

/**
 * Create file I/O handlers for a format.
 */
class FormatFactory {
	/**
	 * @var array<string, class-string<FormatHandler>>
	 */
	const FORMAT_MAP = [
		'rss' => Rss::class,
		'csv' => Csv::class,
		'apache' => Apache::class,
		'nginx' => Nginx::class,
		'json' => Json::class,
	];

	/**
	 * @var array<string, string>
	 */
	const IMPORTER_EXTENSIONS = [
		'csv' => 'csv',
		'txt' => 'csv',
		'json' => 'json',
	];

	/**
	 * @param string $type File format type (rss, csv, apache, nginx, json).
	 * @return FormatHandler|false
	 */
	public function create( $type ) {
		if ( ! isset( self::FORMAT_MAP[ $type ] ) ) {
			return false;
		}

		$class_name = self::FORMAT_MAP[ $type ];

		return new $class_name();
	}

	/**
	 * @param string $filename
	 * @return FormatHandler
	 */
	public function create_importer_for_filename( $filename ) {
		$parts = pathinfo( $filename );
		$extension = isset( $parts['extension'] ) ? strtolower( $parts['extension'] ) : '';
		$type = isset( self::IMPORTER_EXTENSIONS[ $extension ] ) ? self::IMPORTER_EXTENSIONS[ $extension ] : 'apache';
		$importer = $this->create( $type );

		return $importer !== false ? $importer : new Apache();
	}
}
