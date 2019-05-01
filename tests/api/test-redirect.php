<?php

class RedirectionApiRedirectTest extends Redirection_Api_Test {
	public function setUp() {
		parent::setUp();
		$this->group = Red_Group::create( 'group1', 1 );
		$this->group2 = Red_Group::create( 'group2', 1 );
	}

	private function createAB( $total = 2, $group = false, $clear = true ) {
		if ( $clear ) {
			global $wpdb;

			$wpdb->query( "DELETE FROM {$wpdb->prefix}redirection_items" );
		}

		for ( $i = 0; $i < $total; $i++ ) {
			Red_Item::create( array(
				'url' => '/test'.( $i + 1 ),
				'group_id' => $group ? $group : $this->group->get_id(),
				'action_type' => 'url',
				'match_type' => 'url',
			) );
		}

		$this->setNonce();
	}

	private function isAthenB( $result ) {
		$this->assertEquals( '/test1', $result->data['items'][0]['url'] );
		$this->assertEquals( '/test2', $result->data['items'][1]['url'] );
	}

	private function isBthenA( $result ) {
		$this->assertEquals( '/test2', $result->data['items'][0]['url'] );
		$this->assertEquals( '/test1', $result->data['items'][1]['url'] );
	}

	public function testNoPermission() {
		$this->setUnauthorised();
		$result = $this->callApi( 'redirect' );

		$this->assertEquals( 403, $result->status );
		$this->assertEquals( 'rest_forbidden', $result->data['code'] );
	}

	public function testListNoParams() {
		$this->createAB();
		$result = $this->callApi( 'redirect' );
		$this->isBthenA( $result );
	}

	public function testListBadOrderBy() {
		$this->createAB();
		$result = $this->callApi( 'redirect', [ 'orderby' => 'cats' ] );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testListOrderBy() {
		$this->createAB();
		$result = $this->callApi( 'redirect', [ 'orderby' => 'url' ] );
		$this->isBthenA( $result );
	}

	public function testListBadDirection() {
		$this->createAB();
		$result = $this->callApi( 'redirect', [ 'direction' => 'cats' ] );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testListDirection() {
		$this->createAB();
		$result = $this->callApi( 'redirect', [ 'direction' => 'asc' ] );
		$this->isAthenB( $result );
	}

	public function testListBadFilter() {
		$this->createAB();
		$result = $this->callApi( 'redirect', [ 'filterBy' => 'cats', 'filter' => 'nothing' ] );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testListFilterName() {
		$this->createAB();
		$result = $this->callApi( 'redirect', [ 'filter' => 'test1' ] );
		$this->assertEquals( 1, $result->data['total'] );
	}

	public function testListFilterGroup() {
		$this->createAB();
		$this->createAB( 1, $this->group2->get_id(), false );

		$result = $this->callApi( 'redirect',  array( 'filter' => (string)$this->group2->get_id(), 'filterBy' => 'group' ) );
		$this->assertEquals( 1, $result->data['total'] );
	}

	public function testListBadPerPage() {
		$this->createAB( 5 );

		$result = $this->callApi( 'redirect',  array( 'per_page' => 'cats' ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testListPerPageMin() {
		$this->createAB( 20 );

		$result = $this->callApi( 'redirect',  array( 'per_page' => 5 ) );
		$this->assertEquals( 5, count( $result->data['items'] ) );
	}

	public function testListPerPageMax() {
		$this->createAB( 201 );

		$result = $this->callApi( 'redirect',  array( 'per_page' => 201 ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testListPerPage() {
		$this->createAB( 20 );

		$result = $this->callApi( 'redirect',  array( 'per_page' => 12 ) );
		$this->assertEquals( 12, count( $result->data['items'] ) );
	}

	public function testListBadPage() {
		$this->createAB( 20 );

		$result = $this->callApi( 'redirect',  array( 'per_page' => 10, 'page' => 'cats' ) );
		$this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testListPage() {
		$this->createAB( 30 );

		$result = $this->callApi( 'redirect',  array( 'per_page' => 10, 'page' => 2 ) );
		$this->assertEquals( 10, count( $result->data['items'] ) );
		$this->assertEquals( '/test10', $result->data['items'][ 0 ]['url'] );
	}

	public function testBadBulk() {
		$this->createAB();
		$redirect = Red_Item::create( array( 'url' => '/', 'group_id' => $this->group->get_id(), 'action_type' => 'url', 'match_type' => 'url' ) );

		$result = $this->callApi( 'bulk/redirect/cats', array( 'items' => $redirect->get_id() ), 'POST' );
		$this->assertEquals( 'rest_no_route', $result->data['code'] );

		// $result = $this->callApi( 'bulk/redirect/delete', array( 'items' => 'x' ), 'POST' );
		// $this->assertEquals( 'rest_invalid_param', $result->data['code'] );
	}

	public function testBulkDelete() {
		$this->createAB();
		$redirect = Red_Item::create( array( 'url' => '/', 'group_id' => $this->group->get_id(), 'action_type' => 'url', 'match_type' => 'url' ) );

		$result = $this->callApi( 'bulk/redirect/delete', array( 'items' => $redirect->get_id() ), 'POST' );
		$this->assertEquals( 2, count( $result->data['items'] ) );
	}

	public function testBulkDisable() {
		$this->createAB();
		$redirect = Red_Item::create( array( 'url' => '/', 'group_id' => $this->group->get_id(), 'action_type' => 'url', 'match_type' => 'url' ) );

		$result = $this->callApi( 'bulk/redirect/disable', array( 'items' => $redirect->get_id() ), 'POST' );
		$this->assertEquals( 3, count( $result->data['items'] ) );

		$redirect = Red_Item::get_by_id( $redirect->get_id() );
		$this->assertFalse( $redirect->is_enabled() );
	}

	public function testBulkEnable() {
		$this->createAB();
		$redirect = Red_Item::create( array( 'url' => '/', 'group_id' => $this->group->get_id(), 'action_type' => 'url', 'match_type' => 'url' ) );

		$result = $this->callApi( 'bulk/redirect/enable', array( 'items' => $redirect->get_id() ), 'POST' );
		$this->assertEquals( 3, count( $result->data['items'] ) );

		$redirect = Red_Item::get_by_id( $redirect->get_id() );
		$this->assertTrue( $redirect->is_enabled() );
	}

	public function testUpdateBadRedirect() {
		$this->createAB();

		$result = $this->callApi( 'redirect/cats', array(), 'POST' );
		$this->assertEquals( 'rest_no_route', $result->data['code'] );
	}

	public function testUpdate() {
		$this->createAB();
		$redirect = Red_Item::create( array( 'url' => '/', 'group_id' => $this->group->get_id(), 'action_type' => 'url', 'match_type' => 'url' ) );

		$result = $this->callApi( 'redirect/' . $redirect->get_id(), array( 'url' => '/cats', 'group_id' => $this->group->get_id(), 'action_type' => 'url', 'match_type' => 'url' ), 'POST' );
		$redirect = Red_Item::get_by_id( $redirect->get_id() );

		$this->assertEquals( '/cats', $redirect->get_url() );
	}

	public function testCreateBadRedirect() {
		$this->createAB();

		$result = $this->callApi( 'redirect', array( 'group_id' => 5, 'url' => 'yes', 'match_type' => 'url', 'action_type' => 'url' ), 'POST' );
		$this->assertEquals( 400, $result->status );
	}

	public function testCreateRedirect() {
		$this->createAB();

		$result = $this->callApi( 'redirect', array( 'group_id' => $this->group->get_id(), 'url' => 'yes', 'id' => 0, 'match_type' => 'url', 'action_type' => 'url' ), 'POST' );
		$this->assertEquals( 3, count( $result->data['items'] ) );
	}
}
