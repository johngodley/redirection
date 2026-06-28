<?php

use Redirection\ImportExport\ImportGroup;
use Redirection\ImportExport\ImportRedirect;
use Redirection\ImportExport\Format\Csv;

class ImportCsvTest extends WP_UnitTestCase {
	public function testCreateRedirect() {
		global $wpdb;

		$group = Red_Group::create( 'group', Red_Group::get( 1 )->get_module_id() );

		$file = fopen( 'php://memory', 'w+' );
		fwrite( $file, '"/old","/new","0","301","url","2",""' );
		rewind( $file );

		$importer = new Csv();
		$count = $importer->load_from_file( new ImportGroup( $group->get_id() ), $file, ',' );
		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		$this->assertEquals( 1, $count );
		$this->assertEquals( '/old', $redirect->url );
		$this->assertEquals( '/new', $redirect->action_data );
		$this->assertEquals( 301, $redirect->action_code );
	}

	public function testSemicolon() {
		global $wpdb;

		$group = Red_Group::create( 'group', Red_Group::get( 1 )->get_module_id() );

		// Changing it here isn't really testing the problem, but it doesnt work otherwise from the CLI (web is fine)
		$multi = file_get_contents( __DIR__ . '/fixtures/semicolon.csv' );

		$file = fopen( 'php://memory', 'w+' );

		fwrite( $file, $multi );
		rewind( $file );

		$importer = new Csv();
		$count = $importer->load_from_file( new ImportGroup( $group->get_id() ), $file, ';' );
		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		$this->assertEquals( 3, $count );
		$this->assertEquals( '/sign/SMEK', $redirect->url );
	}

	public function testDeduplicateUpdatesExistingRedirect() {
		$existing_group = Red_Group::create( 'existing', 1 );
		$import_group = Red_Group::create( 'import', 1 );
		$existing = Red_Item::create(
			[
				'url' => '/old',
				'group_id' => $existing_group->get_id(),
				'match_type' => 'url',
				'action_type' => 'url',
				'action_data' => [ 'url' => '/first' ],
			]
		);

		$file = fopen( 'php://memory', 'w+' );
		fwrite( $file, '"/old","/new","0","301","url","2",""' );
		rewind( $file );

		$importer = new Csv();
		$redirect = new ImportRedirect( [ 'duplicate_mode' => 'update' ] );
		$count = $importer->load_from_file( new ImportGroup( $import_group->get_id() ), $file, ',', false, $redirect );
		$updated = Red_Item::get_by_id( $existing->get_id() );

		$this->assertEquals( 1, $count );
		$this->assertEquals( 0, $redirect->get_created() );
		$this->assertEquals( 1, $redirect->get_updated() );
		$this->assertEquals( $existing_group->get_id(), $updated->get_group_id() );
		$this->assertEquals( [ 'url' => '/new' ], $updated->to_json()['action_data'] );
	}

	public function testIgnoreDuplicatesLeavesExistingRedirectUnchanged() {
		$existing_group = Red_Group::create( 'existing', 1 );
		$import_group = Red_Group::create( 'import', 1 );
		$existing = Red_Item::create(
			[
				'url' => '/old',
				'group_id' => $existing_group->get_id(),
				'match_type' => 'url',
				'action_type' => 'url',
				'action_data' => [ 'url' => '/first' ],
			]
		);

		$file = fopen( 'php://memory', 'w+' );
		fwrite( $file, '"/old","/new","0","301","url","2",""' );
		rewind( $file );

		$importer = new Csv();
		$redirect = new ImportRedirect( [ 'duplicate_mode' => 'ignore' ] );
		$count = $importer->load_from_file( new ImportGroup( $import_group->get_id() ), $file, ',', false, $redirect );
		$updated = Red_Item::get_by_id( $existing->get_id() );

		$this->assertEquals( 0, $count );
		$this->assertEquals( 0, $redirect->get_created() );
		$this->assertEquals( 0, $redirect->get_updated() );
		$this->assertEquals( 1, $redirect->get_ignored() );
		$this->assertEquals( $existing_group->get_id(), $updated->get_group_id() );
		$this->assertEquals( [ 'url' => '/first' ], $updated->to_json()['action_data'] );
	}
}
