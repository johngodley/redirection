<?php

class ImportExportCsvTest extends Redirection_Api_Test {
	public function setUp() {
		global $wpdb;

		$wpdb->get_var( "TRUNCATE {$wpdb->prefix}redirection_items" );
		$wpdb->get_var( "TRUNCATE {$wpdb->prefix}redirection_groups" );
	}

	public function testNoPermission() {
		$this->setUnauthorised();

		$result = $this->callApi( 'export/1/csv' );
		$this->assertEquals( 403, $result->status );
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

	public function testBadModule() {
		$this->setNonce();
		$result = $this->callApi( 'export/cat/csv' );
		$this->assertEquals( 'rest_no_route', $result->data['code'] );
	}

	public function testBadFormat() {
		$this->setNonce();
		$result = $this->callApi( 'export/1/cat' );
		$this->assertEquals( 'rest_no_route', $result->data['code'] );
	}

	public function testExportCSV() {
		$expected = 'source,target,regex,type,code,match,hits,title
"/1","*","0","url","301","url","0",""';

		$group1 = Red_Group::create( 'group1', 1 );
		Red_Item::create( array( 'url' => '1', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group1->get_id() ) );

		$this->setNonce();
		$result = $this->callApi( 'export/1/csv' );

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertEquals( $expected, trim( $result->data['data'] ) );
	}

	public function testExportJSON() {
		$group1 = Red_Group::create( 'group1', 1 );
		Red_Item::create( array( 'url' => '1', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group1->get_id() ) );

		$this->setNonce();
		$result = $this->callApi( 'export/1/json' );

		$json = json_decode( $result->data['data'] );
		$this->assertEquals( 1, $result->data['total'] );
		$this->assertEquals( 1, $json->redirects[0]->id );
	}

	public function testExportNginx() {
		$group1 = Red_Group::create( 'group1', 1 );
		Red_Item::create( array( 'url' => '1', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group1->get_id() ) );

		$this->setNonce();
		$result = $this->callApi( 'export/1/nginx' );

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertTrue( strpos( $result->data, 'rewrite ^1$' ) !== false );
	}

	public function testExportApache() {
		$group1 = Red_Group::create( 'group1', 1 );
		Red_Item::create( array( 'url' => '1', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group1->get_id() ) );

		$this->setNonce();
		$result = $this->callApi( 'export/1/apache' );

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertTrue( strpos( $result->data, 'RewriteRule ^1$' ) !== false );
	}
}
