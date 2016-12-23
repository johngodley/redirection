<?php

class ImportCsvTest extends WP_UnitTestCase {
	public function testHeader() {
		$exporter = new Red_Csv_File();
		$csv = $exporter->csv_as_item( array( 'source', 'target' ), 1 );

		$this->assertFalse( $csv );
	}

	public function testSourceTarget() {
		$exporter = new Red_Csv_File();
		$csv = $exporter->csv_as_item( array( '/source', '/target' ), 1 );
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
		$csv = $exporter->csv_as_item( array( '/source', '/target', 0, 310 ), 1 );

		$this->assertEquals( 310, $csv['action_code'] );
	}
}
