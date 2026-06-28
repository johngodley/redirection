<?php

require_once PLUGIN_PATH . '/includes/import-export/class-format-handler.php';
require_once PLUGIN_PATH . '/includes/import-export/class-export-details.php';
require_once PLUGIN_PATH . '/includes/import-export/class-file-reader.php';
require_once PLUGIN_PATH . '/includes/import-export/parser/class-csv-parser.php';
require_once PLUGIN_PATH . '/includes/import-export/format/class-csv.php';

use Redirection\ImportExport\Format\Csv;

class CsvFormatTest extends TestCase {
	private function get_item( array $args ) {
		return new class( $args ) {
			public $match = null;
			private $args;
			private $enabled = true;

			public function __construct( array $args ) {
				$this->args = $args;
				$this->enabled = $args['enabled'] ?? true;

				if ( isset( $args['match_data'] ) ) {
					$this->match = new class( $args ) {
						private $args;

						public function __construct( array $args ) {
							$this->args = $args;
						}

						public function get_data() {
							return $this->args['match_data'];
						}
					};
				}
			}

			public function get_url() {
				return $this->args['url'];
			}

			public function get_action_code() {
				return $this->args['action_code'] ?? 301;
			}

			public function get_action_type() {
				return $this->args['action_type'] ?? 'url';
			}

			public function get_hits() {
				return $this->args['hits'] ?? 0;
			}

			public function get_title() {
				return $this->args['title'] ?? '';
			}

			public function is_regex() {
				return $this->args['regex'] ?? false;
			}

			public function is_enabled() {
				return $this->enabled;
			}
		};
	}

	public function testItemAsCsvForStandardRedirect() {
		$csv = new Csv();
		$item = $this->get_item(
			[
				'url' => '/source',
				'regex' => false,
				'action_type' => 'url',
				'action_code' => 301,
				'match_data' => [ 'url' => '/target' ],
			]
		);

		$this->assertEquals( '"/source","/target",0,301,"url",0,"","active"', $csv->item_as_csv( $item ) );
	}

	public function testItemAsCsvForRegexRedirect() {
		$csv = new Csv();
		$item = $this->get_item(
			[
				'url' => '/source',
				'regex' => true,
				'action_type' => 'url',
				'action_code' => 301,
				'match_data' => [ 'url' => '/target' ],
			]
		);

		$this->assertEquals( '"/source","/target",1,301,"url",0,"","active"', $csv->item_as_csv( $item ) );
	}

	public function testItemAsCsvFallsBackToUnknownForNonUrlMatchData() {
		$csv = new Csv();
		$item = $this->get_item(
			[
				'url' => '/source1',
				'regex' => false,
				'action_type' => 'url',
				'action_code' => 301,
				'match_data' => [
					'referrer' => 'ref',
					'url_from' => 'url1',
					'url_notfrom' => 'url2',
				],
			]
		);

		$this->assertEquals( '"/source1","/unknown",0,301,"url",0,"","active"', $csv->item_as_csv( $item ) );
	}

	public function testItemAsCsvUsesDisabledStatus() {
		$csv = new Csv();
		$item = $this->get_item(
			[
				'url' => '/source',
				'action_code' => 301,
				'match_data' => [ 'url' => '/target' ],
				'enabled' => false,
			]
		);

		$this->assertEquals( '"/source","/target",0,301,"url",0,"","disabled"', $csv->item_as_csv( $item ) );
	}

	public function testEscapeCsvHandlesEmptyAndQuote() {
		$csv = new Csv();

		$this->assertEquals( '""', $csv->escape_csv( '' ) );
		$this->assertEquals( '"\'"', $csv->escape_csv( "'" ) );
		$this->assertEquals( '""""', $csv->escape_csv( '"' ) );
	}

	public function testGetDataIncludesHeaderAndRows() {
		$csv = new Csv();
		$item1 = $this->get_item(
			[
				'url' => '/source1',
				'action_code' => 301,
				'match_data' => [ 'url' => '/target' ],
			]
		);
		$item2 = $this->get_item(
			[
				'url' => '/source2',
				'action_code' => 301,
				'match_data' => [ 'url' => '/target' ],
			]
		);

		$result = $csv->get_data( [ $item1, $item2 ], [] );
		$lines = array_filter( explode( PHP_EOL, $result ) );

		$this->assertEquals( 'source,target,regex,code,type,hits,title,status', $lines[0] );
		$this->assertEquals( '"/source1","/target",0,301,"url",0,"","active"', $lines[1] );
		$this->assertEquals( '"/source2","/target",0,301,"url",0,"","active"', $lines[2] );
	}
}
