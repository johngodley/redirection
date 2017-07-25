<?php

require_once dirname( __FILE__ ) . '/../../fileio/csv.php';

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

	public function testMultipleLines() {
		$exporter = new Red_Csv_File();
		$item1 = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source1', 'action_data' => '/target', 'action_code' => 301 ) );
		$item2 = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source2', 'action_data' => '/target', 'action_code' => 301 ) );

		$temp = fopen( 'php://memory', 'w+' );

		$exporter->output_to_file( $temp, array( $item1, $item2 ) );
		rewind($temp);
		$result = stream_get_contents( $temp );

		$lines = array_filter( explode( PHP_EOL, $result ) );

		$this->assertEquals( 3, count( $lines ) );
		$this->assertEquals( 'source,target,regex,type,code,match,hits,title', $lines[0] );
		$this->assertEquals( '"/source1","/target","0","url","301","url","0",""', $lines[1] );
		$this->assertEquals( '"/source2","/target","0","url","301","url","0",""', $lines[2] );
	}

	public function testExportReferrer() {
		$exporter = new Red_Csv_File();
		$target = serialize( array( 'referrer' => 'ref', 'url_from' => 'url1', 'url_notfrom' => 'url2' ) );
		$item = new Red_Item( (object) array( 'match_type' => 'referrer', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source1', 'action_data' => $target, 'action_code' => 301 ) );
		$csv = $exporter->item_as_csv( $item );

		$this->assertEquals( '"/source1","*","0","url","301","url","0",""', $csv );
	}
}
