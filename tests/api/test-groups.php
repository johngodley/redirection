<?php

class RedirectionApiGroupTest extends WP_Ajax_UnitTestCase {
	public static $redirection;

	private function get_group( $params = array() ) {
		return json_decode( self::$redirection->ajax_get_group( $params ) );
	}

	private function set_group( $params = array() ) {
		return json_decode( self::$redirection->ajax_set_group( $params ) );
	}

	private function do_bulk( $params = array() ) {
		return json_decode( self::$redirection->ajax_group_action( $params ) );
	}

	public static function setupBeforeClass() {
		self::$redirection = Redirection_Admin::init()->api;
	}

	private function createAB( $total = 2, $module = 1, $clear = true ) {
		if ( $clear ) {
			global $wpdb;

			$wpdb->query( "DELETE FROM {$wpdb->prefix}redirection_groups" );
		}

		for ( $i = 0; $i < $total; $i++ ) {
			Red_Group::create( 'test'.( $i + 1 ), $module );
		}

		$this->setNonce();
	}

	private function isAthenB( $result ) {
		$this->assertEquals( 'test1', $result->items[ 0 ]->name );
		$this->assertEquals( 'test2', $result->items[ 1 ]->name );
	}

	private function isBthenA( $result ) {
		$this->assertEquals( 'test2', $result->items[ 0 ]->name );
		$this->assertEquals( 'test1', $result->items[ 1 ]->name );
	}

	private function setNonce() {
		$this->_setRole( 'administrator' );
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'wp_rest' );
	}

	public function testListNoParams() {
		$this->createAB();
		$result = $this->get_group();
		$this->isBthenA( $result );
	}

	public function testListBadOrderBy() {
		$this->createAB();
		$result = $this->get_group( array( 'orderBy' => 'cats' ) );
		$this->isBthenA( $result );
	}

	public function testListOrderBy() {
		$this->createAB();
		$result = $this->get_group( array( 'orderBy' => 'name' ) );
		$this->isBthenA( $result );
	}

	public function testListBadDirection() {
		$this->createAB();
		$result = $this->get_group( array( 'direction' => 'cats' ) );
		$this->isBthenA( $result );
	}

	public function testListDirection() {
		$this->createAB();
		$result = $this->get_group( array( 'direction' => 'asc' ) );
		$this->isAthenB( $result );
	}

	public function testListBadFilter() {
		$this->createAB();
		$result = $this->get_group( array( 'filterBy' => 'cats', 'filter' => 'nothing' ) );
		$this->assertEquals( array(), $result->items );
	}

	public function testListFilterName() {
		$this->createAB();
		$result = $this->get_group( array( 'filter' => 'test1' ) );
		$this->assertEquals( 1, $result->total );
	}

	public function testListFilterModule() {
		$this->createAB();
		$this->createAB( 1, 2, false );

		$result = $this->get_group( array( 'filter' => 2, 'filterBy' => 'module' ) );
		$this->assertEquals( 1, $result->total );
	}

	public function testListBadPerPage() {
		$this->createAB( 5 );

		$result = $this->get_group( array( 'perPage' => 'cats' ) );
		$this->assertEquals( 5, count( $result->items ) );
	}

	public function testListPerPageMin() {
		$this->createAB( 20 );

		$result = $this->get_group( array( 'perPage' => 5 ) );
		$this->assertEquals( 5, count( $result->items ) );
	}

	public function testListPerPageMax() {
		$this->createAB( 101 );

		$result = $this->get_group( array( 'perPage' => 101 ) );
		$this->assertEquals( 100, count( $result->items ) );
	}

	public function testListPerPage() {
		$this->createAB( 20 );

		$result = $this->get_group( array( 'perPage' => 12 ) );
		$this->assertEquals( 12, count( $result->items ) );
	}

	public function testListBadPage() {
		$this->createAB( 20 );

		$result = $this->get_group( array( 'perPage' => 10, 'page' => 'cats' ) );
		$this->assertEquals( 10, count( $result->items ) );
		$this->assertEquals( 'test20', $result->items[ 0 ]->name );
	}

	public function testListPage() {
		$this->createAB( 30 );

		$result = $this->get_group( array( 'perPage' => 10, 'page' => 2 ) );
		$this->assertEquals( 10, count( $result->items ) );
		$this->assertEquals( 'test10', $result->items[ 0 ]->name );
	}

	public function testBadBulk() {
		$this->createAB();
		$group = Red_Group::create( 'test', 1 );

		$result = $this->do_bulk( array( 'bulk' => 'cats', 'items' => $group->get_id() ) );
		$this->assertEquals( 3, count( $result->items ) );

		$result = $this->do_bulk( array( 'bulk' => 'delete', 'items' => 'x' ) );
		$this->assertEquals( 3, count( $result->items ) );
	}

	public function testBulkDelete() {
		$this->createAB();
		$group = Red_Group::create( 'test', 1 );

		$result = $this->do_bulk( array( 'bulk' => 'delete', 'items' => $group->get_id() ) );
		$this->assertEquals( 2, count( $result->items ) );
	}

	public function testBulkDisable() {
		$this->createAB();
		$group = Red_Group::create( 'test', 1 );

		$result = $this->do_bulk( array( 'bulk' => 'disable', 'items' => $group->get_id() ) );
		$this->assertEquals( 3, count( $result->items ) );

		$group = Red_Group::get( $group->get_id() );
		$this->assertFalse( $group->is_enabled() );
	}

	public function testBulkEnable() {
		$this->createAB();
		$group = Red_Group::create( 'test', 1 );

		$result = $this->do_bulk( array( 'bulk' => 'enable', 'items' => $group->get_id() ) );
		$this->assertEquals( 3, count( $result->items ) );

		$group = Red_Group::get( $group->get_id() );
		$this->assertTrue( $group->is_enabled() );
	}

	public function testUpdateBadModule() {
		$this->createAB();

		$result = $this->set_group( array( 'id' => 'cats' ) );
		$this->assertTrue( isset( $result->error ) );
	}

	public function testUpdateBadName() {
		$this->createAB();
		$group = Red_Group::create( 'test', 1 );

		$result = $this->set_group( array( 'id' => $group->get_id() ) );
		$this->assertTrue( isset( $result->error ) );
	}

	public function testUpdate() {
		$this->createAB();
		$group = Red_Group::create( 'test', 1 );

		$result = $this->set_group( array( 'id' => $group->get_id(), 'name' => 'cats', 'moduleId' => 2 ) );

		$group = Red_Group::get( $group->get_id() );
		$this->assertEquals( 2, $group->get_module_id() );
		$this->assertEquals( 'cats', $group->get_name() );
	}

	public function testCreateBadGroup() {
		$this->createAB();

		$result = $this->set_group();
		$this->assertTrue( isset( $result->error ) );

		$result = $this->set_group( array( 'moduleId' => 5, 'name' => 'yes' ) );
		$this->assertTrue( isset( $result->error ) );

		$result = $this->set_group( array( 'moduleId' => 2 ) );
		$this->assertTrue( isset( $result->error ) );
	}

	public function testCreateGroup() {
		$this->createAB();

		$result = $this->set_group( array( 'moduleId' => 2, 'name' => 'yes', 'id' => 0 ) );
		$this->assertEquals( 3, count( $result->items ) );
	}
}
