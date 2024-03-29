<?php

include_once dirname( __FILE__ ) . '/../../fileio/csv.php';

class ImportCsvTest extends WP_UnitTestCase {
	public function testHeader() {
		$importer = new Red_Csv_File();
		$csv = $importer->csv_as_item( array( 'source', 'target' ), Red_Group::get( 1 ) );

		$this->assertFalse( $csv );
	}

	public function testSourceTarget() {
		$importer = new Red_Csv_File();
		$csv = $importer->csv_as_item( array( '/source', '/target', 0, 'url', '301', 'url', '2', '' ), Red_Group::get( 1 ) );
		$target = array(
			'url' => '/source',
			'action_data' => array( 'url' => '/target' ),
			'regex' => false,
			'group_id' => 1,
			'match_type' => 'url',
			'action_type' => 'url',
			'action_code' => 301,
			'status' => 'enabled',
		);

		$this->assertEquals( $target, $csv );
	}

	public function testSourceTargetRegex() {
		$importer = new Red_Csv_File();
		$csv = $importer->csv_as_item( array( '/source.*', '/target' ), Red_Group::get( 1 ) );

		$this->assertTrue( $csv['regex'] );
	}

	public function testSourceTargetRegexOverride() {
		$importer = new Red_Csv_File();
		$csv = $importer->csv_as_item( array( '/source', '/target', 1 ), Red_Group::get( 1 ) );

		$this->assertTrue( $csv['regex'] );
	}

	public function testRedirectCode() {
		$importer = new Red_Csv_File();
		$csv = $importer->csv_as_item( array( '/source', '/target', 0, 308 ), Red_Group::get( 1 ) );

		$this->assertEquals( 308, $csv['action_code'] );
	}

	public function testInvalidRedirectCode() {
		$importer = new Red_Csv_File();
		$csv = $importer->csv_as_item( array( '/source', '/target', 0, 666 ), Red_Group::get( 1 ) );

		$this->assertEquals( 301, $csv['action_code'] );
	}

	public function testCreateRedirect() {
		global $wpdb;

		$group = Red_Group::create( 'group', Red_Group::get( 1 ) );

		$file = fopen( 'php://memory', 'w+' );
		fwrite( $file, '"/old","/new","0","301","url","2",""' );
		rewind( $file );

		$importer = new Red_Csv_File();
		$count = $importer->load_from_file( $group->get_id(), $file, ',' );
		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		$this->assertEquals( 1, $count );
		$this->assertEquals( '/old', $redirect->url );
		$this->assertEquals( '/new', $redirect->action_data );
		$this->assertEquals( 301, $redirect->action_code );
	}

	public function testLineEndings() {
		global $wpdb;

		$group = Red_Group::create( 'group', Red_Group::get( 1 ) );

		// Changing it here isn't really testing the problem, but it doesnt work otherwise from the CLI (web is fine)
		ini_set( 'auto_detect_line_endings', true );
		$multi = file_get_contents( dirname( __FILE__ ) . '/fixtures/multiline-ending.csv' );

		$file = fopen( 'php://memory', 'w+' );

		fwrite( $file, $multi );
		rewind( $file );

		$importer = new Red_Csv_File();
		$count = $importer->load_from_file( $group->get_id(), $file, ',' );
		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		$this->assertEquals( 3, $count );
		$this->assertEquals( '/sign/SMEK', $redirect->url );
	}

	public function testSemicolon() {
		global $wpdb;

		$group = Red_Group::create( 'group', Red_Group::get( 1 ) );

		// Changing it here isn't really testing the problem, but it doesnt work otherwise from the CLI (web is fine)
		ini_set( 'auto_detect_line_endings', true );
		$multi = file_get_contents( dirname( __FILE__ ) . '/fixtures/semicolon.csv' );

		$file = fopen( 'php://memory', 'w+' );

		fwrite( $file, $multi );
		rewind( $file );

		$importer = new Red_Csv_File();
		$count = $importer->load_from_file( $group->get_id(), $file, ';' );
		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		$this->assertEquals( 3, $count );
		$this->assertEquals( '/sign/SMEK', $redirect->url );
	}
}
