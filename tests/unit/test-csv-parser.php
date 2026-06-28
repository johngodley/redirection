<?php

use Redirection\ImportExport\Parser\CsvParser;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CsvParserTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		$this->load_parser_dependencies();
		Red_Group::reset();
	}

	private function load_parser_dependencies() {
		require_once PLUGIN_PATH . '/tests/unit/stubs/class-test-import-group-item.php';
		require_once PLUGIN_PATH . '/tests/unit/stubs/class-red-group.php';
		require_once PLUGIN_PATH . '/tests/unit/stubs/functions-http.php';
		require_once PLUGIN_PATH . '/includes/import-export/parser/class-csv-parser.php';
	}

	public function testParseRowReturnsRedirectPayload() {
		$parser = new CsvParser();
		Red_Group::$groups[9] = new Test_Import_Group_Item( 9 );
		$group = Red_Group::get( 9 );

		$item = $parser->parse_row( [ '/source', '/target', '0', '301' ], $group );

		$this->assertEquals( '/source', $item['url'] );
		$this->assertEquals( [ 'url' => '/target' ], $item['action_data'] );
		$this->assertFalse( $item['regex'] );
		$this->assertEquals( 9, $item['group_id'] );
		$this->assertEquals( 'url', $item['action_type'] );
		$this->assertEquals( 301, $item['action_code'] );
	}

	public function testParseRowDetectsRegexAndInvalidCodeFallback() {
		$parser = new CsvParser();

		$item = $parser->parse_row( [ '^/contact-us(/.*)?$', '/target', '1', '999' ] );

		$this->assertTrue( $item['regex'] );
		$this->assertEquals( 301, $item['action_code'] );
	}

	public function testParseRowSkipsHeader() {
		$parser = new CsvParser();

		$this->assertFalse( $parser->parse_row( [ 'source', 'target' ] ) );
	}

	public function testParseRowSkipsImportPageHeaderLabels() {
		$parser = new CsvParser();

		$this->assertFalse( $parser->parse_row( [ 'Source URL', 'Target URL' ] ) );
	}
}
