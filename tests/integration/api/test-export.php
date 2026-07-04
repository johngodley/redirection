<?php

class ImportExportCsvTest extends Redirection_Api_Test {
	public function setUp(): void {
		global $wpdb;

		$wpdb->get_var( "TRUNCATE {$wpdb->prefix}redirection_items" );
		$wpdb->get_var( "TRUNCATE {$wpdb->prefix}redirection_groups" );
		$wpdb->get_var( "TRUNCATE {$wpdb->prefix}redirection_logs" );
		$wpdb->get_var( "TRUNCATE {$wpdb->prefix}redirection_404" );
	}

	private function get_endpoints() {
		return [
			[ 'export/1/csv', 'GET', [] ],
		];
	}

	public function testNoPermission() {
		$this->setUnauthorised();
		$this->check_endpoints(
			[
				[ 'export/1/csv', 'GET', [] ],
				[ 'export/redirect', 'GET', [] ],
				[ 'export/log/json', 'GET', [] ],
				[ 'export/404/json', 'GET', [] ],
				[ 'export/group/json', 'GET', [] ],
				[ 'export/bundle', 'GET', [ 'types' => [ 'redirect' ], 'format' => 'json' ] ],
			]
		);
	}

	public function testEditorPermissionRedirectExportRequiresIoCapability() {
		$this->setEditor();

		$redirect_routes = [
			[ 'export/1/csv', [], 'GET' ],
			[ 'export/redirect', [ 'format' => 'json' ], 'GET' ],
			[ 'export/redirect/preview', [ 'format' => 'json' ], 'GET' ],
			[ 'export/bundle', [ 'types' => [ 'redirect' ], 'format' => 'json' ], 'GET' ],
			[ 'export/bundle/preview', [ 'types' => [ 'redirect' ], 'format' => 'json' ], 'GET' ],
		];

		foreach ( $redirect_routes as $route ) {
			$result = $this->callApi( $route[0], $route[1], $route[2] );
			$this->assertEquals( 403, $result->status );
			$this->assertEquals( 'rest_forbidden', $result->data['code'] );
		}

		$this->add_capability( Redirection_Capabilities::CAP_IO_MANAGE );

		foreach ( $redirect_routes as $route ) {
			$result = $this->callApi( $route[0], $route[1], $route[2] );
			$this->assertNotEquals( 403, $result->status, $route[0] );
		}

		$this->clear_capability();
	}

	public function testEditorPermissionRequiresMatchingExportCapability() {
		$this->setEditor();
		$this->add_capability( Redirection_Capabilities::CAP_IO_MANAGE );

		$group = $this->callApi( 'export/group/json' );
		$this->assertEquals( 403, $group->status );

		$log = $this->callApi( 'export/log/json' );
		$this->assertEquals( 403, $log->status );

		$error = $this->callApi( 'export/404/json' );
		$this->assertEquals( 403, $error->status );

		$setting = $this->callApi( 'export/bundle', [ 'types' => [ 'setting' ], 'format' => 'json' ] );
		$this->assertEquals( 403, $setting->status );

		$group_bundle = $this->callApi( 'export/bundle', [ 'types' => [ 'group' ], 'format' => 'json' ] );
		$this->assertEquals( 403, $group_bundle->status );

		$this->clear_capability();

		$this->add_capabilities( [ Redirection_Capabilities::CAP_IO_MANAGE, Redirection_Capabilities::CAP_GROUP_MANAGE ] );
		$group = $this->callApi( 'export/group/json' );
		$this->assertNotEquals( 403, $group->status );
		$group_bundle = $this->callApi( 'export/bundle', [ 'types' => [ 'group' ], 'format' => 'json' ] );
		$this->assertNotEquals( 403, $group_bundle->status );
		$this->clear_capability();

		$this->add_capabilities( [ Redirection_Capabilities::CAP_IO_MANAGE, Redirection_Capabilities::CAP_LOG_MANAGE ] );
		$log = $this->callApi( 'export/log/json' );
		$this->assertNotEquals( 403, $log->status );
		$log_bundle = $this->callApi( 'export/bundle', [ 'types' => [ 'log' ], 'format' => 'json' ] );
		$this->assertNotEquals( 403, $log_bundle->status );
		$mixed_bundle = $this->callApi( 'export/bundle', [ 'types' => [ 'log', 'setting' ], 'format' => 'json' ] );
		$this->assertEquals( 403, $mixed_bundle->status );
		$this->clear_capability();

		$this->add_capabilities( [ Redirection_Capabilities::CAP_IO_MANAGE, Redirection_Capabilities::CAP_404_MANAGE ] );
		$error = $this->callApi( 'export/404/json' );
		$this->assertNotEquals( 403, $error->status );
		$error_bundle = $this->callApi( 'export/bundle', [ 'types' => [ '404' ], 'format' => 'json' ] );
		$this->assertNotEquals( 403, $error_bundle->status );
		$this->clear_capability();

		$this->add_capabilities( [ Redirection_Capabilities::CAP_IO_MANAGE, Redirection_Capabilities::CAP_OPTION_MANAGE ] );
		$setting = $this->callApi( 'export/bundle', [ 'types' => [ 'setting' ], 'format' => 'json' ] );
		$this->assertNotEquals( 403, $setting->status );
		$this->clear_capability();
	}

	// public function testExportNameModule() {
	//  // Create 2 groups, one in apache, one in WordPress
	//  $group1 = Red_Group::create( 'group1', 1 );
	//  $group2 = Red_Group::create( 'group2', 2 );

	//  // Create 1 redirect in each group
	//  Red_Item::create( array( 'url' => '1', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group1->get_id() ) );
	//  Red_Item::create( array( 'url' => '1', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group2->get_id() ) );

		//  // Expect 1 redirect and 1 group
	//  $this->assertEquals( 1, $results['total'] );
	// }

	// public function testExportAll() {
	//  // Create 2 groups, one in apache, one in wordpress
	//  $group1 = Red_Group::create( 'group1', 1 );
	//  $group2 = Red_Group::create( 'group2', 2 );

	//  // Create 1 redirect in each group
	//  Red_Item::create( array( 'url' => '1', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group1->get_id() ) );
	//  Red_Item::create( array( 'url' => '1', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group2->get_id() ) );

		//  // Expect 2 redirect and 2 group
	//  $this->assertEquals( 2, $results['total'] );
	// }

	// public function testBadModule() {
	//  $this->setNonce();
	//  $result = $this->callApi( 'export/cat/csv' );
	//  $this->assertEquals( 'rest_no_route', $result->data['code'] );
	// }

	// public function testBadFormat() {
	//  $this->setNonce();
	//  $result = $this->callApi( 'export/1/cat' );
	//  $this->assertEquals( 'rest_no_route', $result->data['code'] );
	// }

	public function testExportCSV() {
		$expected = 'source,target,regex,code,type,hits,title,status
"/1","/unknown",0,301,"url",0,"","active"';

		$group1 = Red_Group::create( 'group1', 1 );
		Red_Item::create( [ 'url' => '/1', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group1->get_id() ] );

		$this->setNonce();
		$result = $this->callApi( 'export/1/csv' );

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertEquals( $expected, trim( $result->data['data'] ) );
	}

	public function testRedirectPreviewByGroup() {
		$group1 = Red_Group::create( 'group1', 1 );
		$group2 = Red_Group::create( 'group2', 1 );

		Red_Item::create( [ 'url' => '/one', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group1->get_id() ] );
		Red_Item::create( [ 'url' => '/two', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group2->get_id() ] );

		$this->setNonce();
		$result = $this->callApi(
			'export/redirect/preview',
			[ 'scope_type' => 'group', 'scope_value' => $group1->get_id(), 'format' => 'json' ]
		);

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertGreaterThan( 0, $result->data['estimated_size'] );
	}

	public function testRedirectExportByGroup() {
		$group1 = Red_Group::create( 'group1', 1 );
		$group2 = Red_Group::create( 'group2', 1 );

		Red_Item::create( [ 'url' => '/one', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group1->get_id() ] );
		Red_Item::create( [ 'url' => '/two', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group2->get_id() ] );

		$this->setNonce();
		$result = $this->callApi(
			'export/redirect',
			[ 'scope_type' => 'group', 'scope_value' => $group1->get_id(), 'format' => 'json' ]
		);
		$json = json_decode( $result->data['data'] );

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertEquals( '/one', $json->redirects[0]->url );
		$this->assertObjectNotHasProperty( 'groups', $json );
	}

	public function testRedirectExportByModule() {
		$wordpress_group = Red_Group::create( 'wordpress-group', 1 );
		$apache_group = Red_Group::create( 'apache-group', 2 );

		Red_Item::create( [ 'url' => '/wordpress', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $wordpress_group->get_id() ] );
		Red_Item::create( [ 'url' => '/apache', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $apache_group->get_id() ] );

		$this->setNonce();
		$result = $this->callApi(
			'export/redirect',
			[ 'scope_type' => 'module', 'scope_value' => 1, 'format' => 'json' ]
		);
		$json = json_decode( $result->data['data'] );

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertEquals( '/wordpress', $json->redirects[0]->url );
		$this->assertObjectNotHasProperty( 'groups', $json );
	}

	public function testRedirectExportJsonSupportsSelectedItems() {
		$group = Red_Group::create( 'selected-group', 1 );
		$first = Red_Item::create( [ 'url' => '/selected-one', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group->get_id() ] );
		$second = Red_Item::create( [ 'url' => '/selected-two', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group->get_id() ] );

		$this->setNonce();
		$result = $this->callApi(
			'export/redirect',
			[
				'items' => [ $second->get_id() ],
				'format' => 'json',
			]
		);
		$this->assertEquals( 200, $result->status, print_r( $result->data, true ) );
		$this->assertIsString( $result->data['data'], print_r( $result->data, true ) );
		$json = json_decode( $result->data['data'] );

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertCount( 1, $json->redirects );
		$this->assertEquals( '/selected-two', $json->redirects[0]->url );
		$this->assertObjectNotHasProperty( 'groups', $json );
		$this->assertNotEquals( $first->get_id(), $second->get_id() );
	}

	public function testRedirectExportCsvSupportsGlobalFilter() {
		$enabled_group = Red_Group::create( 'enabled-group', 1 );
		$disabled_group = Red_Group::create( 'disabled-group', 1 );

		Red_Item::create( [ 'url' => '/enabled-export', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $enabled_group->get_id() ] );
		Red_Item::create( [ 'url' => '/disabled-export', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $disabled_group->get_id(), 'status' => 'disabled' ] );

		$this->setNonce();
		$result = $this->callApi(
			'export/redirect',
			[
				'global' => true,
				'filterBy' => [
					'status' => 'disabled',
				],
				'format' => 'csv',
			]
		);

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertStringContainsString( '/disabled-export', $result->data['data'] );
		$this->assertStringNotContainsString( '/enabled-export', $result->data['data'] );
	}

	public function testRedirectLogPreview() {
		Red_Redirect_Log::create( 'domain', '/logged', '192.168.1.1', [ 'target' => '/target' ] );

		$this->setNonce();
		$result = $this->callApi( 'export/log/preview', [ 'format' => 'json' ] );

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertGreaterThan( 0, $result->data['estimated_size'] );
	}

	public function testRedirectLogJsonExport() {
		Red_Redirect_Log::create( 'domain', '/logged', '192.168.1.1', [ 'target' => '/target' ] );

		$this->setNonce();
		$result = $this->callApi( 'export/log/json' );
		$json = json_decode( $result->data['data'] );

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertEquals( '/logged', $json[0]->url );
		$this->assertEquals( '/target', $json[0]->sent_to );
	}

	public function testRedirectLogCsvExportUsesSelectedFields() {
		Red_Redirect_Log::create( 'domain', '/logged', '192.168.1.1', [ 'target' => '/target', 'agent' => 'tester' ] );

		$this->setNonce();
		$result = $this->callApi(
			'export/log/csv',
			[
				'global' => true,
				'displaySelected' => [ 'url', 'target', 'agent' ],
			]
		);

		$lines = explode( "\n", trim( $result->data['data'] ) );

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertEquals( 'source,target,agent', $lines[0] );
		$this->assertEquals( '/logged,/target,tester', $lines[1] );
	}

	public function testRedirectLogJsonExportSelectedItems() {
		$id_one = Red_Redirect_Log::create( 'domain', '/one', '192.168.1.1', [ 'target' => '/target-one' ] );
		$id_two = Red_Redirect_Log::create( 'domain', '/two', '192.168.1.2', [ 'target' => '/target-two' ] );

		$this->setNonce();
		$result = $this->callApi(
			'export/log/json',
			[
				'items' => [ $id_two ],
				'displaySelected' => [ 'url', 'target' ],
			]
		);
		$json = json_decode( $result->data['data'] );

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertCount( 1, $json );
		$this->assertEquals( '/two', $json[0]->url );
		$this->assertEquals( '/target-two', $json[0]->target );
		$this->assertNotEquals( $id_one, $id_two );
	}

	public function test404LogJsonExport() {
		Red_404_Log::create( 'domain', '/missing', '192.168.1.1', [ 'agent' => 'tester' ] );

		$this->setNonce();
		$result = $this->callApi( 'export/404/json' );
		$json = json_decode( $result->data['data'] );

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertEquals( '/missing', $json[0]->url );
	}

	public function test404GroupedCsvExportUsesCurrentGrouping() {
		Red_404_Log::create( 'domain', '/missing-one', '192.168.1.1', [ 'agent' => 'tester' ] );
		Red_404_Log::create( 'domain', '/missing-two', '192.168.1.1', [ 'agent' => 'tester-two' ] );
		Red_404_Log::create( 'domain', '/missing-three', '192.168.1.2', [ 'agent' => 'tester-three' ] );

		$this->setNonce();
		$result = $this->callApi(
			'export/404/csv',
			[
				'groupBy' => 'ip',
				'items' => [ '192.168.1.1' ],
				'displaySelected' => [ 'ip', 'count' ],
			]
		);

		$lines = explode( "\n", trim( $result->data['data'] ) );

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertEquals( 'ip,count', $lines[0] );
		$this->assertEquals( '192.168.1.1,2', $lines[1] );
	}

	public function test404GroupedJsonExportIgnoresNonGroupedFields() {
		Red_404_Log::create( 'domain', '/missing-one', '192.168.1.1', [ 'agent' => 'tester' ] );
		Red_404_Log::create( 'domain', '/missing-two', '192.168.1.1', [ 'agent' => 'tester-two' ] );

		$this->setNonce();
		$result = $this->callApi(
			'export/404/json',
			[
				'groupBy' => 'ip',
				'items' => [ '192.168.1.1' ],
				'displaySelected' => [ 'ip', 'count', 'url', 'agent' ],
			]
		);
		$json = json_decode( $result->data['data'] );

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertObjectHasProperty( 'ip', $json[0] );
		$this->assertObjectHasProperty( 'count', $json[0] );
		$this->assertObjectNotHasProperty( 'url', $json[0] );
		$this->assertObjectNotHasProperty( 'agent', $json[0] );
	}

	public function test404LogPreview() {
		Red_404_Log::create( 'domain', '/missing', '192.168.1.1', [ 'referrer' => '/from' ] );

		$this->setNonce();
		$result = $this->callApi( 'export/404/preview', [ 'format' => 'json' ] );

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertGreaterThan( 0, $result->data['estimated_size'] );
	}

	public function testBundleExportJsonSupportsMixedTypes() {
		$group = Red_Group::create( 'bundle-group', 1 );
		Red_Item::create( [ 'url' => '/bundle-redirect', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group->get_id() ] );
		Red_Redirect_Log::create( 'domain', '/bundle-log', '192.168.1.1', [ 'target' => '/target' ] );
		Red_404_Log::create( 'domain', '/bundle-404', '192.168.1.2', [ 'agent' => 'tester' ] );

		$this->setNonce();
		$result = $this->callApi(
			'export/bundle',
			[
				'types' => [ 'redirect', 'log', '404', 'setting' ],
				'format' => 'json',
			]
		);
		$json = json_decode( $result->data['data'] );

		$this->assertEquals( '/bundle-redirect', $json->redirects[0]->url );
		$this->assertEquals( '/bundle-log', $json->logs[0]->url );
		$this->assertEquals( '/bundle-404', $json->errors_404[0]->url );
		$this->assertObjectHasProperty( 'settings', $json );
	}

	public function testBundleExportGroupsCsv() {
		Red_Group::create( 'csv-group', 1 );

		$this->setNonce();
		$result = $this->callApi(
			'export/bundle',
			[
				'types' => [ 'group' ],
				'format' => 'csv',
			]
		);

		$this->assertStringContainsString( 'id,name,module_id,status', $result->data['data'] );
		$this->assertStringContainsString( 'csv-group', $result->data['data'] );
	}

	public function testBundleExportGroupsJsonUsesStoredFields() {
		$group = Red_Group::create( 'json-group', 1 );

		$this->setNonce();
		$result = $this->callApi(
			'export/bundle',
			[
				'types' => [ 'group' ],
				'format' => 'json',
			]
		);
		$json = json_decode( $result->data['data'] );

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertEquals( $group->get_id(), $json->groups[0]->id );
		$this->assertEquals( 'json-group', $json->groups[0]->name );
		$this->assertEquals( 1, $json->groups[0]->module_id );
		$this->assertEquals( 'enabled', $json->groups[0]->status );
		$this->assertObjectNotHasProperty( 'moduleName', $json->groups[0] );
		$this->assertObjectNotHasProperty( 'redirects', $json->groups[0] );
	}

	public function testGroupExportJsonSupportsSelectedItems() {
		$first = Red_Group::create( 'first-group', 1 );
		$second = Red_Group::create( 'second-group', 2 );

		$this->setNonce();
		$result = $this->callApi(
			'export/group/json',
			[
				'items' => [ $second->get_id() ],
			]
		);
		$this->assertEquals( 200, $result->status, print_r( $result->data, true ) );
		$this->assertIsString( $result->data['data'], print_r( $result->data, true ) );
		$json = json_decode( $result->data['data'] );

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertCount( 1, $json->groups );
		$this->assertEquals( $second->get_id(), $json->groups[0]->id );
		$this->assertEquals( 'second-group', $json->groups[0]->name );
		$this->assertNotEquals( $first->get_id(), $second->get_id() );
	}

	public function testGroupExportCsvSupportsGlobalFilter() {
		Red_Group::create( 'enabled-group', 1 );
		Red_Group::create( 'disabled-group', 1, false );
		$this->assertCount( 1, Red_Group::get_all( [ 'filterBy' => [ 'status' => 'disabled' ] ] ) );

		$this->setNonce();
		$result = $this->callApi(
			'export/group/csv',
			[
				'global' => true,
				'filterBy' => [
					'status' => 'disabled',
				],
			]
		);

		$this->assertEquals( 1, $result->data['total'] );
		$this->assertStringContainsString( 'disabled-group', $result->data['data'] );
		$this->assertStringNotContainsString( 'enabled-group', $result->data['data'] );
	}

	// public function testExportJSON() {
	//  $group1 = Red_Group::create( 'group1', 1 );
	//  Red_Item::create( array( 'url' => '1', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group1->get_id() ) );

	//  $this->setNonce();
	//  $result = $this->callApi( 'export/1/json' );

	//  $json = json_decode( $result->data['data'] );
	//  $this->assertEquals( 1, $result->data['total'] );
	//  $this->assertEquals( 1, $json->redirects[0]->id );
	// }

	// public function testExportNginx() {
	//  $group1 = Red_Group::create( 'group1', 1 );
	//  Red_Item::create( array( 'url' => '1', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group1->get_id() ) );

	//  $this->setNonce();
	//  $result = $this->callApi( 'export/1/nginx' );

	//  $this->assertEquals( 1, $result->data['total'] );
	//  $this->assertTrue( strpos( $result->data['data'], 'rewrite (?i)^/1$' ) !== false );
	// }

	// public function testExportApache() {
	//  $group1 = Red_Group::create( 'group1', 1 );
	//  Red_Item::create( array( 'url' => '1', 'match_type' => 'url', 'action_type' => 'url', 'group_id' => $group1->get_id() ) );

	//  $this->setNonce();
	//  $result = $this->callApi( 'export/1/apache' );

	//  $this->assertEquals( 1, $result->data['total'] );
	//  $this->assertTrue( strpos( $result->data['data'], 'RewriteRule ^1/?$' ) !== false );
	// }
}
