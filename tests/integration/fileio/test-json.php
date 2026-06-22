<?php

use Redirection\FileIO\Format\Json;

class JsonTest extends WP_UnitTestCase {
	public function testExportEmpty() {
		$json = new Json();
		$data = json_decode( $json->get_data( [], [] ) );

		$this->assertTrue( empty( $data->groups ) );
		$this->assertTrue( empty( $data->redirects ) );
		$this->assertTrue( isset( $data->plugin->version ) );
	}

	public function testExportNew() {
		$json = new Json();
		$redirects = [ new Red_Item( (object) [ 'url' => 'source', 'match_type' => 'url', 'id' => 1, 'action_type' => 'url' ] ) ];
		$groups = [ ( new Red_Group( (object) [ 'name' => 'group', 'id' => 1 ] ) )->to_json() ];

		$data = json_decode( $json->get_data( $redirects, $groups ) );

		$this->assertEquals( 'source', $data->redirects[0]->url );
		$this->assertEquals( 1, $data->groups[0]->id );
	}

	public function testImportBad() {
		$json = new Json();
		$data = $json->load( 0, 'thing', 'x' );
		$this->assertEquals( 0, $data );
	}

	public function testImport() {
		global $wpdb;

		$import = [
			'groups' => [
				[
					'name' => 'groupx',
					'id' => 5005,
					'module_id' => 1,
					'enabled' => true,
				],
			],
			'redirects' => [
				[
					'url' => '/source1',
					'id' => 1,
					'group_id' => 5005,
					'match_type' => 'url',
					'action_type' => 'url',
					'action_data' => [ 'url' => '/test' ],
				],
			],
		];

		$json = new Json();
		$data = $json->load( 0, 'thing', wp_json_encode( $import ) );
		$this->assertEquals( 1, $data );

		$group = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_groups ORDER BY id DESC LIMIT 1" );
		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		$this->assertEquals( 'groupx', $group->name );
		$this->assertEquals( '/source1', $redirect->url );
		$this->assertEquals( $group->id, $redirect->group_id );
	}

	public function testImportUsesExistingGroupId() {
		global $wpdb;

		$group = Red_Group::create( 'existing', 1 );
		$import = [
			'groups' => [
				[
					'name' => 'ignored',
					'id' => $group->get_id(),
					'module_id' => 1,
					'enabled' => true,
				],
			],
			'redirects' => [
				[
					'url' => '/source-existing',
					'id' => 1,
					'group_id' => $group->get_id(),
					'match_type' => 'url',
					'action_type' => 'url',
					'action_data' => [ 'url' => '/target-existing' ],
				],
			],
		];

		$json = new Json();
		$data = $json->load( 0, 'thing', wp_json_encode( $import ) );
		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );
		$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_groups WHERE name='ignored'" );

		$this->assertEquals( 1, $data );
		$this->assertEquals( $group->get_id(), intval( $redirect->group_id, 10 ) );
		$this->assertCount( 0, $groups );
	}

	public function testImportCreatesOneFallbackGroupPerMissingGroupId() {
		global $wpdb;

		$before = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups WHERE name='Group'" ), 10 );
		$import = [
			'redirects' => [
				[
					'url' => '/source-missing-1',
					'id' => 1,
					'group_id' => 555,
					'match_type' => 'url',
					'action_type' => 'url',
					'action_data' => [ 'url' => '/target-missing-1' ],
				],
				[
					'url' => '/source-missing-2',
					'id' => 2,
					'group_id' => 555,
					'match_type' => 'url',
					'action_type' => 'url',
					'action_data' => [ 'url' => '/target-missing-2' ],
				],
			],
		];

		$json = new Json();
		$data = $json->load( 0, 'thing', wp_json_encode( $import ) );
		$redirects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_items WHERE url IN ('/source-missing-1','/source-missing-2') ORDER BY id ASC" );
		$after = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups WHERE name='Group'" ), 10 );

		$this->assertEquals( 2, $data );
		$this->assertCount( 2, $redirects );
		$this->assertEquals( $redirects[0]->group_id, $redirects[1]->group_id );
		$this->assertCount( 1, array_unique( array_map( 'intval', wp_list_pluck( $redirects, 'group_id' ) ) ) );
		$this->assertEquals( $before + 1, $after );
	}
}
