<?php

use Redirection\ImportExport\FormatFactory;

class ImportExportRss extends WP_UnitTestCase {
	public function testExport() {
		// Mock bloginfo_rss('url') by filtering the siteurl option
		add_filter( 'option_siteurl', [ $this, 'mock_siteurl' ] );

		$group1 = Red_Group::create( 'group1', 1 );
		$item = Red_Item::create( [ 'url' => '/1', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group1->get_id() ] );

		$exporter = ( new FormatFactory() )->create( 'rss' );
		$xml = $exporter->get_data( [ $item ], [] );

		$this->assertTrue( strpos( $xml, '<title>/1</title>' ) !== false );

		remove_filter( 'option_siteurl', [ $this, 'mock_siteurl' ] );
	}

	public function mock_siteurl() {
		return 'http://example.com';
	}
}
