<?php

require dirname( __FILE__ ) . '/../../fileio/csv.php';

class ExportCsvTest extends WP_UnitTestCase {
	public function testNoRegex() {
		$exporter = new Red_Csv_File();
		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source', 'action_data' => '/target', 'action_code' => 301 ) );
		$csv = $exporter->item_as_csv( $item );

		$this->assertEquals( '"/source","/target","0","url","301","url","0",""', $csv );
	}

	public function testRegex() {
		$exporter = new Red_Csv_File();
		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '/source', 'action_data' => '/target', 'action_code' => 301 ) );
		$csv = $exporter->item_as_csv( $item );

		$this->assertEquals( '"/source","/target","1","url","301","url","0",""', $csv );
	}

	public function testEscapeFormula() {
		$exporter = new Red_Csv_File();
		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '=/source', 'action_data' => '/target', 'action_code' => 301 ) );
		$csv = $exporter->item_as_csv( $item );

		$this->assertEquals( '"=/source","/target","0","url","301","url","0",""', $csv );
	}

	public function testEscapeCSVEmpty() {
		$exporter = new Red_Csv_File();

		$this->assertEquals( '""', $exporter->escape_csv( '' ) );
	}

	public function testEscapeCSVQuote() {
		$exporter = new Red_Csv_File();

		$this->assertEquals( '"\'"', $exporter->escape_csv( "'" ) );
		$this->assertEquals( '""""', $exporter->escape_csv( '"' ) );
	}
}
