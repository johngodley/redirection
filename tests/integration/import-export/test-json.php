<?php

use Redirection\ImportExport\ImportGroup;
use Redirection\ImportExport\ImportRedirect;
use Redirection\ImportExport\Format\Json;

class JsonTest extends WP_UnitTestCase {
	private function create_temp_file( $contents ) {
		$file = tempnam( sys_get_temp_dir(), 'red-json-' );
		file_put_contents( $file, $contents );
		return $file;
	}

	public function testExportEmpty() {
		$json = new Json();
		$data = json_decode( $json->get_data( [], [] ) );

		$this->assertObjectNotHasProperty( 'groups', $data );
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
		$file = $this->create_temp_file( 'x' );
		$data = $json->load( new ImportGroup( 0 ), new ImportRedirect(), $file, false );
		$this->assertEquals( 0, $data['created'] );
		$this->assertEquals( 0, $data['updated'] );
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
		$file = $this->create_temp_file( wp_json_encode( $import ) );
		$data = $json->load( new ImportGroup( 0 ), new ImportRedirect(), $file, false );
		$this->assertEquals( 1, $data['created'] );
		$this->assertEquals( 0, $data['updated'] );

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
		$file = $this->create_temp_file( wp_json_encode( $import ) );
		$data = $json->load( new ImportGroup( 0 ), new ImportRedirect(), $file, false );
		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );
		$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_groups WHERE name='ignored'" );

		$this->assertEquals( 1, $data['created'] );
		$this->assertEquals( 0, $data['updated'] );
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
		$file = $this->create_temp_file( wp_json_encode( $import ) );
		$data = $json->load( new ImportGroup( 0 ), new ImportRedirect(), $file, false );
		$redirects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_items WHERE url IN ('/source-missing-1','/source-missing-2') ORDER BY id ASC" );
		$after = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups WHERE name='Group'" ), 10 );

		$this->assertEquals( 2, $data['created'] );
		$this->assertEquals( 0, $data['updated'] );
		$this->assertCount( 2, $redirects );
		$this->assertEquals( $redirects[0]->group_id, $redirects[1]->group_id );
		$this->assertCount( 1, array_unique( array_map( 'intval', wp_list_pluck( $redirects, 'group_id' ) ) ) );
		$this->assertEquals( $before + 1, $after );
	}

	public function testDryRunDoesNotCreateRedirects() {
		global $wpdb;

		$import = [
			'groups' => [
				[
					'name' => 'existing',
					'id' => 1,
					'module_id' => 1,
					'enabled' => true,
				],
			],
			'redirects' => [
				[
					'url' => '/source-dry-run',
					'id' => 1,
					'group_id' => 1,
					'match_type' => 'url',
					'action_type' => 'url',
					'action_data' => [ 'url' => '/target-dry-run' ],
				],
			],
		];

		$file = $this->create_temp_file( wp_json_encode( $import ) );
		$json = new Json();
		$data = $json->load( new ImportGroup( 0, [ 'dry_run' => true ] ), new ImportRedirect( [ 'dry_run' => true ] ), $file, true );
		$redirect = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items WHERE url='/source-dry-run'" );

		$this->assertEquals( 1, $data['created'] );
		$this->assertEquals( 0, $data['updated'] );
		$this->assertEquals( 0, intval( $redirect, 10 ) );
	}

	public function testImportSupportsSettingsAndLogsSections() {
		global $wpdb;

		$options = Red_Options::get();
		$import = [
			'settings' => [
				'https' => ! $options['https'],
				'flag_case' => ! $options['flag_case'],
			],
			'logs' => [
				[
					'created' => current_time( 'mysql' ),
					'url' => '/bundle-log',
					'ip' => '127.0.0.1',
					'referrer' => '/from',
					'agent' => 'tester',
				],
			],
			'errors_404' => [
				[
					'created' => current_time( 'mysql' ),
					'url' => '/bundle-404',
					'ip' => '127.0.0.2',
					'referrer' => '/from',
					'agent' => 'tester',
				],
			],
		];

		$json = new Json();
		$file = $this->create_temp_file( wp_json_encode( $import ) );
		$data = $json->load( new ImportGroup( 1 ), new ImportRedirect(), $file, false, [ 'import_sections' => [ 'settings', 'logs', 'errors_404' ] ] );

		$this->assertEquals( 2, $data['settings_imported'] );
		$this->assertEquals( 1, $data['logs_imported'] );
		$this->assertEquals( 1, $data['errors_imported'] );
		$this->assertEquals( ! $options['https'], Red_Options::get()['https'] );
		$this->assertEquals( ! $options['flag_case'], Red_Options::get()['flag_case'] );
		$this->assertEquals(
			1,
			intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_logs WHERE url='/bundle-log'" ), 10 )
		);
		$this->assertEquals(
			1,
			intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_404 WHERE url='/bundle-404'" ), 10 )
		);
	}

	public function testImportUpdatesExistingRedirectById() {
		$group = Red_Group::create( 'existing-group', 1 );
		$other_group = Red_Group::create( 'other-group', 1 );
		$existing = Red_Item::create(
			[
				'url' => '/json-source',
				'group_id' => $group->get_id(),
				'match_type' => 'url',
				'action_type' => 'url',
				'action_data' => [ 'url' => '/old-target' ],
			]
		);

		$import = [
			'groups' => [
				[
					'name' => 'other-group',
					'id' => $other_group->get_id(),
					'module_id' => 1,
					'enabled' => true,
				],
			],
			'redirects' => [
				[
					'url' => '/json-source-updated',
					'id' => $existing->get_id(),
					'group_id' => $other_group->get_id(),
					'match_type' => 'url',
					'action_type' => 'url',
					'action_data' => [ 'url' => '/new-target' ],
				],
			],
		];

		$json = new Json();
		$file = $this->create_temp_file( wp_json_encode( $import ) );
		$data = $json->load( new ImportGroup( 0 ), new ImportRedirect( [ 'duplicate_mode' => 'update' ] ), $file, false );
		$updated = Red_Item::get_by_id( $existing->get_id() );

		$this->assertEquals( 0, $data['created'] );
		$this->assertEquals( 1, $data['updated'] );
		$this->assertEquals( '/json-source-updated', $updated->get_url() );
		$this->assertEquals( $group->get_id(), $updated->get_group_id() );
		$this->assertEquals( [ 'url' => '/new-target' ], $updated->to_json()['action_data'] );
	}

	public function testImportIgnoresExistingRedirectById() {
		$group = Red_Group::create( 'existing-group', 1 );
		$other_group = Red_Group::create( 'other-group', 1 );
		$existing = Red_Item::create(
			[
				'url' => '/json-source',
				'group_id' => $group->get_id(),
				'match_type' => 'url',
				'action_type' => 'url',
				'action_data' => [ 'url' => '/old-target' ],
			]
		);

		$import = [
			'groups' => [
				[
					'name' => 'other-group',
					'id' => $other_group->get_id(),
					'module_id' => 1,
					'enabled' => true,
				],
			],
			'redirects' => [
				[
					'url' => '/json-source-updated',
					'id' => $existing->get_id(),
					'group_id' => $other_group->get_id(),
					'match_type' => 'url',
					'action_type' => 'url',
					'action_data' => [ 'url' => '/new-target' ],
				],
			],
		];

		$json = new Json();
		$file = $this->create_temp_file( wp_json_encode( $import ) );
		$data = $json->load( new ImportGroup( 0 ), new ImportRedirect( [ 'duplicate_mode' => 'ignore' ] ), $file, false );
		$updated = Red_Item::get_by_id( $existing->get_id() );

		$this->assertEquals( 0, $data['created'] );
		$this->assertEquals( 0, $data['updated'] );
		$this->assertEquals( '/json-source', $updated->get_url() );
		$this->assertEquals( $group->get_id(), $updated->get_group_id() );
		$this->assertEquals( [ 'url' => '/old-target' ], $updated->to_json()['action_data'] );
	}
}
