<?php

require_once PLUGIN_PATH . '/includes/import-export/class-format-handler.php';
require_once PLUGIN_PATH . '/includes/import-export/class-format-factory.php';
require_once PLUGIN_PATH . '/includes/import-export/class-export-details.php';
require_once PLUGIN_PATH . '/includes/import-export/class-file-reader.php';
require_once PLUGIN_PATH . '/includes/import-export/class-group-repository.php';
require_once PLUGIN_PATH . '/includes/import-export/class-redirect-repository.php';
require_once PLUGIN_PATH . '/includes/import-export/class-module-repository.php';
require_once PLUGIN_PATH . '/includes/import-export/class-htaccess-encoder.php';
require_once PLUGIN_PATH . '/includes/import-export/class-htaccess-target-builder.php';
require_once PLUGIN_PATH . '/includes/import-export/class-htaccess-rule-builder.php';
require_once PLUGIN_PATH . '/includes/import-export/class-htaccess.php';
require_once PLUGIN_PATH . '/includes/import-export/class-import-group.php';
require_once PLUGIN_PATH . '/includes/import-export/class-redirect-duplicate-matcher.php';
require_once PLUGIN_PATH . '/includes/import-export/class-import-redirect.php';
require_once PLUGIN_PATH . '/includes/import-export/class-import-service.php';
require_once PLUGIN_PATH . '/includes/import-export/class-export-service.php';
require_once PLUGIN_PATH . '/includes/import-export/parser/class-csv-parser.php';
require_once PLUGIN_PATH . '/includes/import-export/parser/class-json-parser.php';
require_once PLUGIN_PATH . '/includes/import-export/format/class-apache.php';
require_once PLUGIN_PATH . '/includes/import-export/format/class-csv.php';
require_once PLUGIN_PATH . '/includes/import-export/format/class-json.php';

use Redirection\ImportExport\ExportService;
use Redirection\ImportExport\Format\Apache;
use Redirection\ImportExport\Format\Csv;
use Redirection\ImportExport\Format\Json;
use Redirection\ImportExport\FormatHandler;
use Redirection\ImportExport\FormatFactory;
use Redirection\ImportExport\GroupRepository;
use Redirection\ImportExport\ImportService;
use Redirection\ImportExport\ImportGroup;
use Redirection\ImportExport\ImportRedirect;
use Redirection\ImportExport\ModuleRepository;
use Redirection\ImportExport\Parser\CsvParser;
use Redirection\ImportExport\Parser\JsonParser;
use Redirection\ImportExport\RedirectDuplicateMatcher;
use Redirection\ImportExport\RedirectRepository;

class ImportExportServiceTest extends TestCase {
	private function get_format( array $response ) {
		return new class( $response ) extends FormatHandler {
			private $response;
			public $calls = [];

			public function __construct( array $response ) {
				$this->response = $response;
			}

			public function get_data( array $items, array $groups ) {
				$this->calls[] = [ 'items' => $items, 'groups' => $groups ];
				return $this->response['data'];
			}

			public function load( $group, $redirect, $filename, $is_dry_run, array $options = [] ) {
				$this->calls[] = [
					'group' => $group,
					'redirect' => $redirect,
					'filename' => $filename,
					'is_dry_run' => $is_dry_run,
					'options' => $options,
				];
				return $this->response;
			}
		};
	}

	public function testImportServiceSelectsImporterFromFileExtension() {
		$csv = $this->get_format(
			[
				'created' => 1,
				'updated' => 0,
				'ignored' => 0,
				'groups_created' => 0,
			]
		);
		$formats = new class( $csv ) extends FormatFactory {
			public $csv;

			public function __construct( $csv ) {
				$this->csv = $csv;
			}

			public function create_importer_for_filename( $filename ) {
				return $this->csv;
			}
		};

		$service = new ImportService( $formats );
		$result = $service->import(
			5,
			[
				'name' => 'test.csv',
				'tmp_name' => '/tmp/test.csv',
				'type' => 'text/csv',
				'error' => 0,
				'size' => 123,
			],
			[
				'dry_run' => true,
			]
		);

		$this->assertEquals( 1, $result['created'] );
		$this->assertEquals( '/tmp/test.csv', $csv->calls[0]['filename'] );
		$this->assertTrue( $csv->calls[0]['is_dry_run'] );
	}

	public function testExportServiceExportsAllModules() {
		$format = $this->get_format( [ 'data' => 'exported-data' ] );
		$formats = new class( $format ) extends FormatFactory {
			private $format;

			public function __construct( $format ) {
				$this->format = $format;
			}

			public function create( $type ) {
				return $type === 'json' ? $this->format : false;
			}
		};
		$groups = new class() extends GroupRepository {
			public function get_all_for_export() {
				return [
					[ 'id' => 1, 'name' => 'group', 'module_id' => 1, 'status' => 'enabled' ],
				];
			}
		};
		$redirects = new class() extends RedirectRepository {
			public function get_all() {
				return [
					(object) [ 'id' => 1 ],
					(object) [ 'id' => 2 ],
				];
			}
		};

		$service = new ExportService( $formats, $groups, $redirects, new ModuleRepository() );
		$result = $service->export( 'all', 'json' );

		$this->assertEquals( 'exported-data', $result['data'] );
		$this->assertEquals( 2, $result['total'] );
		$this->assertEquals( [], $format->calls[0]['groups'] );
	}

	public function testExportServiceExportsSelectedModule() {
		$format = $this->get_format( [ 'data' => 'module-export' ] );
		$formats = new class( $format ) extends FormatFactory {
			private $format;

			public function __construct( $format ) {
				$this->format = $format;
			}

			public function create( $type ) {
				return $type === 'csv' ? $this->format : false;
			}
		};
		$groups = new class() extends GroupRepository {
			public function get_all_for_module_export( $module_id ) {
				return [
					[ 'id' => $module_id, 'name' => 'group', 'module_id' => $module_id, 'status' => 'enabled' ],
				];
			}
		};
		$redirects = new class() extends RedirectRepository {
			public function get_all_for_module( $module_id ) {
				return [
					(object) [ 'id' => $module_id ],
				];
			}
		};
		$modules = new class() extends ModuleRepository {
			public function get_id_for_name( $name ) {
				return $name === 'wordpress' ? 7 : 0;
			}

			public function get( $module_id ) {
				return new class( $module_id ) {
					private $id;

					public function __construct( $id ) {
						$this->id = $id;
					}

					public function get_id() {
						return $this->id;
					}
				};
			}
		};

		$service = new ExportService( $formats, $groups, $redirects, $modules );
		$result = $service->export( 'wordpress', 'csv' );

		$this->assertEquals( 'module-export', $result['data'] );
		$this->assertEquals( 1, $result['total'] );
	}

	public function testCsvUsesInjectedParser() {
		$parser = new class() extends CsvParser {
			public function parse_row( array $csv, $group = null ) {
				unset( $group );

				return [
					'url' => 'injected',
					'action_data' => [ 'url' => '/target' ],
					'regex' => false,
					'group_id' => 0,
					'match_type' => 'url',
					'action_type' => 'url',
					'action_code' => 301,
					'status' => 'enabled',
				];
			}
		};
		$csv = new Csv( $parser );

		$item = $csv->csv_as_item( [ '/source', '/ignored' ] );

		$this->assertEquals( 'injected', $item['url'] );
	}

	public function testJsonUsesInjectedParser() {
		$parser = new class() extends JsonParser {
			public function parse( $data ) {
				unset( $data );

				return [
					'groups' => [],
					'redirects' => [],
					'settings' => null,
					'logs' => [],
					'errors_404' => [],
				];
			}
		};
		$json = new Json( $parser );
		$group = new ImportGroup(
			0,
			[],
			new class() extends GroupRepository {
				public function get( $group_id ) {
					unset( $group_id );
					return false;
				}

				public function create( $name, $module_id, $enabled = true ) {
					unset( $name, $module_id, $enabled );
					return false;
				}
			}
		);
		$redirect = new ImportRedirect(
			[],
			new class() extends RedirectDuplicateMatcher {
				public function get_existing_redirect( array $redirect, $file_redirect_id ) {
					unset( $redirect, $file_redirect_id );
					return false;
				}
			},
			new class() extends RedirectRepository {
				public function create( array $redirect ) {
					unset( $redirect );
					return false;
				}
			}
		);

		$result = $json->load_from_string( $group, $redirect, 'not-used', false );

		$this->assertEquals(
			[
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
			],
			$result
		);
	}

	public function testFormatFactoryCreatesImporterForTxtFiles() {
		$factory = new FormatFactory();
		$importer = $factory->create_importer_for_filename( 'redirects.txt' );

		$this->assertInstanceOf( Csv::class, $importer );
	}

	public function testFormatFactoryFallsBackToApacheImporterForUnknownExtensions() {
		$factory = new FormatFactory();
		$importer = $factory->create_importer_for_filename( 'redirects.rules' );

		$this->assertInstanceOf( Apache::class, $importer );
	}
}
