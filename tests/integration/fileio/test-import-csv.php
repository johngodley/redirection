<?php

use Redirection\FileIO\Format\Csv;

class ImportCsvTest extends WP_UnitTestCase {
	public function testHeader() {
		$importer = new Csv();
		$csv = $importer->csv_as_item( [ 'source', 'target' ], Red_Group::get( 1 ) );

		$this->assertFalse( $csv );
	}

	public function testSourceTarget() {
		$importer = new Csv();
		$csv = $importer->csv_as_item( [ '/source', '/target', 0, 'url', '301', 'url', '2', '' ], Red_Group::get( 1 ) );
		$target = [
			'url' => '/source',
			'action_data' => [ 'url' => '/target' ],
			'regex' => false,
			'group_id' => 1,
			'match_type' => 'url',
			'action_type' => 'url',
			'action_code' => 301,
			'status' => 'enabled',
		];

		$this->assertEquals( $target, $csv );
	}

	public function testSourceTargetRegex() {
		$importer = new Csv();
		$csv = $importer->csv_as_item( [ '/source.*', '/target' ], Red_Group::get( 1 ) );

		$this->assertTrue( $csv['regex'] );
	}

	public function testSourceTargetRegexOverride() {
		$importer = new Csv();
		$csv = $importer->csv_as_item( [ '/source', '/target', 1 ], Red_Group::get( 1 ) );

		$this->assertTrue( $csv['regex'] );
	}

	public function testRedirectCode() {
		$importer = new Csv();
		$csv = $importer->csv_as_item( [ '/source', '/target', 0, 308 ], Red_Group::get( 1 ) );

		$this->assertEquals( 308, $csv['action_code'] );
	}

	public function testInvalidRedirectCode() {
		$importer = new Csv();
		$csv = $importer->csv_as_item( [ '/source', '/target', 0, 666 ], Red_Group::get( 1 ) );

		$this->assertEquals( 301, $csv['action_code'] );
	}

	public function testCreateRedirect() {
		global $wpdb;

		$group = Red_Group::create( 'group', Red_Group::get( 1 )->get_module_id() );

		$file = fopen( 'php://memory', 'w+' );
		fwrite( $file, '"/old","/new","0","301","url","2",""' );
		rewind( $file );

		$importer = new Csv();
		$count = $importer->load_from_file( $group->get_id(), $file, ',' );
		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		$this->assertEquals( 1, $count );
		$this->assertEquals( '/old', $redirect->url );
		$this->assertEquals( '/new', $redirect->action_data );
		$this->assertEquals( 301, $redirect->action_code );
	}

	public function testSemicolon() {
		global $wpdb;

		$group = Red_Group::create( 'group', Red_Group::get( 1 )->get_module_id() );

		// Changing it here isn't really testing the problem, but it doesnt work otherwise from the CLI (web is fine)
		$multi = file_get_contents( __DIR__ . '/fixtures/semicolon.csv' );

		$file = fopen( 'php://memory', 'w+' );

		fwrite( $file, $multi );
		rewind( $file );

		$importer = new Csv();
		$count = $importer->load_from_file( $group->get_id(), $file, ';' );
		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		$this->assertEquals( 3, $count );
		$this->assertEquals( '/sign/SMEK', $redirect->url );
	}
}
