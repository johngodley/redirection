<?php

require_once dirname( __FILE__ ) . '/../../fileio/csv.php';

class ExportCsvTest extends WP_UnitTestCase {
	public function testNoRegex() {
		$exporter = new Red_Csv_File();
		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source', 'action_data' => '/target', 'action_code' => 301 ) );
		$csv = $exporter->item_as_csv( $item );

		$this->assertEquals( '"/source","/target",0,301,"url",0,"","active"', $csv );
	}

	public function testRegex() {
		$exporter = new Red_Csv_File();
		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '/source', 'action_data' => '/target', 'action_code' => 301 ) );
		$csv = $exporter->item_as_csv( $item );

		$this->assertEquals( '"/source","/target",1,301,"url",0,"","active"', $csv );
	}

	public function testEscapeFormula() {
		$exporter = new Red_Csv_File();
		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '=/source', 'action_data' => '/target', 'action_code' => 301 ) );
		$csv = $exporter->item_as_csv( $item );

		$this->assertEquals( '"=/source","/target",0,301,"url",0,"","active"', $csv );
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

		$result = $exporter->get_data( array( $item1, $item2 ), array() );

		$lines = array_filter( explode( PHP_EOL, $result ) );

		$this->assertEquals( 3, count( $lines ) );
		$this->assertEquals( 'source,target,regex,code,match,title,status', $lines[0] );
		$this->assertEquals( '"/source1","/target",0,301,"url",0,"","active"', $lines[1] );
		$this->assertEquals( '"/source2","/target",0,301,"url",0,"","active"', $lines[2] );
	}

	public function testExportReferrer() {
		$exporter = new Red_Csv_File();
		$target = serialize( array( 'referrer' => 'ref', 'url_from' => 'url1', 'url_notfrom' => 'url2', 'regex' => false ) );
		$item = new Red_Item( (object) array( 'match_type' => 'referrer', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source1', 'action_data' => $target, 'action_code' => 301 ) );
		$csv = $exporter->item_as_csv( $item );

		$this->assertEquals( '"/source1","/unknown",0,301,"url",0,"","active"', $csv );
	}

	public function testEnabled() {
		$exporter = new Red_Csv_File();
		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source', 'action_data' => '/target', 'action_code' => 301 ) );
		$item->enable();
		$csv = $exporter->item_as_csv( $item );

		$this->assertEquals( '"/source","/target",0,301,"url",0,"","active"', $csv );
	}

	public function testDisabled() {
		$exporter = new Red_Csv_File();
		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source', 'action_data' => '/target', 'action_code' => 301 ) );
		$item->disable();
		$csv = $exporter->item_as_csv( $item );

		$this->assertEquals( '"/source","/target",0,301,"url",0,"","disabled"', $csv );
	}

}
