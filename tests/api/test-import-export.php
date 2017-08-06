<?php

class ImportExportCsvTest extends WP_UnitTestCase {
	public function setUp() {
		global $wpdb;

		$wpdb->get_var( "TRUNCATE {$wpdb->prefix}redirection_items" );
		$wpdb->get_var( "TRUNCATE {$wpdb->prefix}redirection_groups" );
	}

	public function testBadCreate() {
		$exporter = Red_FileIO::create( 'monkey' );
		$this->assertFalse( $exporter );
	}

	public function testGoodCreate() {
		$types = array( 'rss', 'csv', 'apache', 'nginx', 'json' );

		foreach ( $types as $type ) {
			$exporter = Red_FileIO::create( $type );
			$this->assertTrue( $exporter !== false );
		}
	}

	public function testExportNameModule() {
		// Create 2 groups, one in apache, one in wordpress
		$group1 = Red_Group::create( 'group1', 1 );
		$group2 = Red_Group::create( 'group2', 2 );

		// Create 1 redirect in each group
		Red_Item::create( array( 'url' => '1', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group1->get_id() ) );
		Red_Item::create( array( 'url' => '1', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group2->get_id() ) );

		$results = Red_FileIO::export( 'apache', 'json' );

		// Expect 1 redirect and 1 group
		$this->assertEquals( 1, $results['total'] );
	}

	public function testExportAll() {
		// Create 2 groups, one in apache, one in wordpress
		$group1 = Red_Group::create( 'group1', 1 );
		$group2 = Red_Group::create( 'group2', 2 );

		// Create 1 redirect in each group
		Red_Item::create( array( 'url' => '1', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group1->get_id() ) );
		Red_Item::create( array( 'url' => '1', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group2->get_id() ) );

		$results = Red_FileIO::export( 'all', 'json' );

		// Expect 2 redirect and 2 group
		$this->assertEquals( 2, $results['total'] );
	}
}
