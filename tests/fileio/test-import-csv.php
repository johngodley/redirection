<?php

class ImportCsvTest extends WP_UnitTestCase {
	public function testHeader() {
		$exporter = new Red_Csv_File();
		$csv = $exporter->csv_as_item( array( 'source', 'target' ), 1 );

		$this->assertFalse( $csv );
	}

	public function testSourceTarget() {
		$exporter = new Red_Csv_File();
		$csv = $exporter->csv_as_item( array( '/source', '/target', 0, 'url', '301', 'url', '2', '' ), 1 );
		$target = array(
			'source' => '/source',
			'target' => '/target',
			'regex' => false,
			'group_id' => 1,
			'match' => 'url',
			'red_action' => 'url',
			'action_code' => 301,
		);

		$this->assertEquals( $target, $csv );
	}

	public function testSourceTargetRegex() {
		$exporter = new Red_Csv_File();
		$csv = $exporter->csv_as_item( array( '/source.*', '/target' ), 1 );

		$this->assertTrue( $csv['regex'] );
	}

	public function testSourceTargetRegexOverride() {
		$exporter = new Red_Csv_File();
		$csv = $exporter->csv_as_item( array( '/source', '/target', 1 ), 1 );

		$this->assertTrue( $csv['regex'] );
	}

	public function testRedirectCode() {
		$exporter = new Red_Csv_File();
		$csv = $exporter->csv_as_item( array( '/source', '/target', 0, 'url', 308 ), 1 );

		$this->assertEquals( 308, $csv['action_code'] );
	}

	public function testInvalidRedirectCode() {
		$exporter = new Red_Csv_File();
		$csv = $exporter->csv_as_item( array( '/source', '/target', 0, 'url', 666 ), 1 );

		$this->assertEquals( 301, $csv['action_code'] );
	}

	public function testCreateRedirect() {
		global $wpdb;

		$file = fopen( 'php://memory', 'w+' );
		fwrite( $file, '"/old","/new","0","url","301","url","2",""' );
		rewind( $file );

		$exporter = new Red_Csv_File();
		$count = $exporter->load_from_file( 1, $file );
		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		$this->assertEquals( 1, $count );
		$this->assertEquals( '/old', $redirect->url );
		$this->assertEquals( '/new', $redirect->action_data );
		$this->assertEquals( 301, $redirect->action_code );
	}

	public function testLineEndings() {
		global $wpdb;

		// Changing it here isn't really testing the problem, but it doesnt work otherwise from the CLI (web is fine)
		ini_set( 'auto_detect_line_endings', true );
		$multi = file_get_contents( dirname( __FILE__ ).'/fixtures/multiline-ending.csv' );

		$file = fopen( 'php://memory', 'w+' );

		fwrite( $file, $multi );
		rewind( $file );

		$exporter = new Red_Csv_File();
		$count = $exporter->load_from_file( 1, $file );
		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		$this->assertEquals( 3, $count );
		$this->assertEquals( '/sign/SMEK', $redirect->url );
	}
}
