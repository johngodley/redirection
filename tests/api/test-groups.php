<?php

class RedirectionApiGroupTest extends Redirection_Api_Test {
	public static $redirection;

	private function createAB( $total = 2, $module = 1, $clear = true ) {
		if ( $clear ) {
			global $wpdb;

			$wpdb->query( "DELETE FROM {$wpdb->prefix}redirection_groups" );
		}

		for ( $i = 0; $i < $total; $i++ ) {
			Group\Group::create( 'test' . ( $i + 1 ), $module );
		}

		$this->setNonce();
	}

	private function isAthenB( $result ) {
		$this->assertEquals( 'test1', $result->data['items'][0]['name'] );
		$this->assertEquals( 'test2', $result->data['items'][1]['name'] );
	}

	private function isBthenA( $result ) {
		$this->assertEquals( 'test2', $result->data['items'][0]['name'] );
		$this->assertEquals( 'test1', $result->data['items'][1]['name'] );
	}

	private function get_endpoints() {
		return [
			[ 'group', 'GET', [] ],
			[ 'group', 'POST', [ 'moduleId' => 1, 'name' => '1' ] ],
			[ 'group/1', 'POST', [ 'moduleId' => 1, 'name' => '1' ] ],
			[ 'bulk/group/delete', 'POST', [ 'items' => [ 1 ] ] ],
			[ 'bulk/group/enable', 'POST', [ 'items' => [ 1 ] ] ],
			[ 'bulk/group/disable', 'POST', [ 'items' => [ 1 ] ] ],
		];
	}

	public function testNoPermission() {
		$this->setUnauthorised();

		// None of these should work
		$this->check_endpoints( $this->get_endpoints() );
	}

	public function testEditorPermission() {
		// Everything else is 403
		$working = [
			Redirection_Capabilities::CAP_GROUP_MANAGE => [ [ 'group', 'GET' ] ],
			Redirection_Capabilities::CAP_REDIRECT_MANAGE => [ [ 'group', 'GET' ] ],
			Redirection_Capabilities::CAP_GROUP_ADD => [
				[ 'group', 'POST' ],
				[ 'group/1', 'POST' ],
				[ 'bulk/group/enable', 'POST' ],
				[ 'bulk/group/disable', 'POST' ],
			],
			Redirection_Capabilities::CAP_GROUP_DELETE => [ [ 'bulk/group/delete', 'POST' ] ],
		];

		$this->setEditor();

		foreach ( $working as $cap => $working_caps ) {
			$this->add_capability( $cap );
			$this->check_endpoints( $this->get_endpoints(), $working_caps );
			$this->clear_capability();
		}
	}

	public function testAdminPermission() {
		// All of these should work
		$this->check_endpoints( $this->get_endpoints(), $this->get_endpoints() );
	}

	public function testListNoParams() {
		$this->createAB();
		$result = $this->callApi( 'group' );
		$this->isBthenA( $result );
	}

	public function testListBadOrderBy() {
		$this->createAB();
		$result = $this->callApi( 'group', array( 'orderby' => 'cats' ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testListOrderBy() {
		$this->createAB();
		$result = $this->callApi( 'group', array( 'orderby' => 'name' ) );
		$this->isBthenA( $result );
	}

	public function testListBadDirection() {
		$this->createAB();
		$result = $this->callApi( 'group', array( 'direction' => 'cats' ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testListDirection() {
		$this->createAB();
		$result = $this->callApi( 'group', array( 'direction' => 'asc' ) );
		$this->isAthenB( $result );
	}

	public function testListBadFilter() {
		$this->createAB();
		$result = $this->callApi( 'group', array( 'filterBy' => 'cats' ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );

		$result = $this->callApi( 'group', [ 'filterBy' => [ 'cat' => 'thing' ] ] );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testListFilterName() {
		$this->createAB();
		$result = $this->callApi( 'group', [ 'filterBy' => [ 'name' => 'test1' ] ] );
		$this->assertEquals( 1, $result->data['total'] );
	}

	public function testListFilterStatus() {
		$this->createAB();
		$group = Group\Group::create( 'test', 1 );

		$result = $this->callApi( 'bulk/group/disable', array( 'items' => $group->get_id() ), 'POST' );
		$result = $this->callApi( 'group', [ 'filterBy' => [ 'status' => 'disabled' ] ] );

		$this->assertEquals( 1, $result->data['total'] );
	}

	public function testListFilterModule() {
		$this->createAB();
		$this->createAB( 1, 2, false );

		$result = $this->callApi( 'group', [ 'filterBy' => [ 'module' => 2 ] ] );
		$this->assertEquals( 1, $result->data['total'] );
	}

	public function testListBadPerPage() {
		$this->createAB( 5 );

		$result = $this->callApi( 'group', array( 'per_page' => 'cats' ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testListPerPageMin() {
		$this->createAB( 20 );

		$result = $this->callApi( 'group', array( 'per_page' => 5 ) );
		$this->assertEquals( 5, count( $result->data['items'] ) );
	}

	public function testListPerPageMax() {
		$this->createAB( 201 );

		$result = $this->callApi( 'group', array( 'per_page' => 201 ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testListPerPage() {
		$this->createAB( 20 );

		$result = $this->callApi( 'group', array( 'per_page' => 12 ) );
		$this->assertEquals( 12, count( $result->data['items'] ) );
	}

	public function testListBadPage() {
		$this->createAB( 20 );

		$result = $this->callApi( 'group', array( 'per_page' => 10, 'page' => 'cats' ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testListPage() {
		$this->createAB( 30 );

		$result = $this->callApi( 'group', array( 'per_page' => 10, 'page' => 2 ) );
		$this->assertEquals( 10, count( $result->data['items'] ) );
		$this->assertEquals( 'test18', $result->data['items'][0]['name'] );
	}

	public function testBadBulk() {
		$this->createAB();
		$group = Group\Group::create( 'test', 1 );

		$result = $this->callApi( 'bulk/group/cats', array( 'items' => $group->get_id() ), 'POST' );
		$this->assertEquals( 'rest_no_route', $result->data['code'] );

		// $result = $this->callApi( 'bulk/group/delete', array( 'items' => 'x' ), 'POST' );
		// $this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testBulkDelete() {
		$this->createAB();
		$group = Group\Group::create( 'test', 1 );

		$result = $this->callApi( 'bulk/group/delete', array( 'items' => $group->get_id() ), 'POST' );
		$this->assertEquals( 2, count( $result->data['items'] ) );
	}

	public function testBulkDisable() {
		$this->createAB();
		$group = Group\Group::create( 'test', 1 );

		$result = $this->callApi( 'bulk/group/disable', array( 'items' => $group->get_id() ), 'POST' );
		$this->assertEquals( 3, count( $result->data['items'] ) );

		$group = Group\Group::get( $group->get_id() );
		$this->assertFalse( $group->is_enabled() );
	}

	public function testBulkEnable() {
		$this->createAB();
		$group = Group\Group::create( 'test', 1 );

		$result = $this->callApi( 'bulk/group/enable', array( 'items' => $group->get_id() ), 'POST' );
		$this->assertEquals( 3, count( $result->data['items'] ) );

		$group = Group\Group::get( $group->get_id() );
		$this->assertTrue( $group->is_enabled() );
	}

	public function testUpdateBadModule() {
		$this->createAB();

		$result = $this->callApi( 'group/cats', array(), 'POST' );
		$this->assertEquals( 'rest_no_route', $result->data['code'] );
	}

	public function testUpdateBadName() {
		$this->createAB();
		$group = Group\Group::create( 'test', 1 );

		$result = $this->callApi( 'group/' . $group->get_id(), array(), 'POST' );
		$this->assertEquals( 400, $result->status );
	}

	public function testUpdate() {
		$this->createAB();
		$group = Group\Group::create( 'test', 1 );

		$result = $this->callApi( 'group/' . $group->get_id(), array( 'name' => 'cats', 'moduleId' => 2 ), 'POST' );

		$group = Group\Group::get( $group->get_id() );
		$this->assertEquals( 2, $group->get_module_id() );
		$this->assertEquals( 'cats', $group->get_name() );
	}

	public function testCreateBadGroup() {
		$this->createAB();

		$result = $this->callApi( 'group', array(), 'POST' );
		$this->assertEquals( 400, $result->status );

		$result = $this->callApi( 'group', array( 'moduleId' => 5, 'name' => 'yes' ), 'POST' );
		$this->assertEquals( 400, $result->status );

		$result = $this->callApi( 'group', array( 'moduleId' => 2 ), 'POST' );
		$this->assertEquals( 400, $result->status );
	}

	public function testCreateGroup() {
		$this->createAB();

		$result = $this->callApi( 'group', array( 'moduleId' => 2, 'name' => 'yes', 'id' => 0 ), 'POST' );
		$this->assertEquals( 3, count( $result->data['items'] ) );
	}
}
