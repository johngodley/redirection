<?php

class ImportExportRss extends WP_UnitTestCase {
	public function testExport() {
		$group1 = Red_Group::create( 'group1', 1 );
		$item = Red_Item::create( array( 'url' => '1', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group1->get_id() ) );

		$exporter = Red_FileIO::create( 'rss' );
		$xml = $exporter->get_data( array( $item ), array() );

		$this->assertTrue( strpos( $xml, '<title>/1</title>' ) !== false );
	}
}
