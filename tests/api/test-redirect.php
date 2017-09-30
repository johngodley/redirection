<?php

class RedirectionApiRedirectTest extends WP_Ajax_UnitTestCase {
	public static $redirection;

	public function setUp() {
		parent::setUp();
		$this->group = Red_Group::create( 'group1', 1 );
		$this->group2 = Red_Group::create( 'group2', 1 );
	}

	private function get_redirect( $params = array() ) {
		return json_decode( self::$redirection->ajax_get_redirect( $params ) );
	}

	private function set_redirect( $params = array() ) {
		return json_decode( self::$redirection->ajax_set_redirect( $params ) );
	}

	private function do_bulk( $params = array() ) {
		return json_decode( self::$redirection->ajax_redirect_action( $params ) );
	}

	public static function setupBeforeClass() {
		self::$redirection = Redirection_Admin::init()->api;
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
		$this->assertEquals( '/test1', $result->items[ 0 ]->url );
		$this->assertEquals( '/test2', $result->items[ 1 ]->url );
	}

	private function isBthenA( $result ) {
		$this->assertEquals( '/test2', $result->items[ 0 ]->url );
		$this->assertEquals( '/test1', $result->items[ 1 ]->url );
	}

	private function setNonce() {
		$this->_setRole( 'administrator' );
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'wp_rest' );
	}

	public function testListNoParams() {
		$this->createAB();
		$result = $this->get_redirect();
		$this->isBthenA( $result );
	}

	public function testListBadOrderBy() {
		$this->createAB();
		$result = $this->get_redirect( array( 'orderBy' => 'cats' ) );
		$this->isBthenA( $result );
	}

	public function testListOrderBy() {
		$this->createAB();
		$result = $this->get_redirect( array( 'orderBy' => 'name' ) );
		$this->isBthenA( $result );
	}

	public function testListBadDirection() {
		$this->createAB();
		$result = $this->get_redirect( array( 'direction' => 'cats' ) );
		$this->isBthenA( $result );
	}

	public function testListDirection() {
		$this->createAB();
		$result = $this->get_redirect( array( 'direction' => 'asc' ) );
		$this->isAthenB( $result );
	}

	public function testListBadFilter() {
		$this->createAB();
		$result = $this->get_redirect( array( 'filterBy' => 'cats', 'filter' => 'nothing' ) );
		$this->assertEquals( array(), $result->items );
	}

	public function testListFilterName() {
		$this->createAB();
		$result = $this->get_redirect( array( 'filter' => 'test1' ) );
		$this->assertEquals( 1, $result->total );
	}

	public function testListFilterGroup() {
		$this->createAB();
		$this->createAB( 1, $this->group2->get_id(), false );

		$result = $this->get_redirect( array( 'filter' => $this->group2->get_id(), 'filterBy' => 'group' ) );
		$this->assertEquals( 1, $result->total );
	}

	public function testListBadPerPage() {
		$this->createAB( 5 );

		$result = $this->get_redirect( array( 'perPage' => 'cats' ) );
		$this->assertEquals( 5, count( $result->items ) );
	}

	public function testListPerPageMin() {
		$this->createAB( 20 );

		$result = $this->get_redirect( array( 'perPage' => 5 ) );
		$this->assertEquals( 5, count( $result->items ) );
	}

	public function testListPerPageMax() {
		$this->createAB( 201 );

		$result = $this->get_redirect( array( 'perPage' => 201 ) );
		$this->assertEquals( 200, count( $result->items ) );
	}

	public function testListPerPage() {
		$this->createAB( 20 );

		$result = $this->get_redirect( array( 'perPage' => 12 ) );
		$this->assertEquals( 12, count( $result->items ) );
	}

	public function testListBadPage() {
		$this->createAB( 20 );

		$result = $this->get_redirect( array( 'perPage' => 10, 'page' => 'cats' ) );
		$this->assertEquals( 10, count( $result->items ) );
		$this->assertEquals( '/test20', $result->items[ 0 ]->url );
	}

	public function testListPage() {
		$this->createAB( 30 );

		$result = $this->get_redirect( array( 'perPage' => 10, 'page' => 2 ) );
		$this->assertEquals( 10, count( $result->items ) );
		$this->assertEquals( '/test10', $result->items[ 0 ]->url );
	}

	public function testBadBulk() {
		$this->createAB();
		$redirect = Red_Item::create( array( 'url' => '/', 'group_id' => $this->group->get_id(), 'action_type' => 'url', 'match_type' => 'url' ) );

		$result = $this->do_bulk( array( 'bulk' => 'cats', 'items' => $redirect->get_id() ) );
		$this->assertEquals( 3, count( $result->items ) );

		$result = $this->do_bulk( array( 'bulk' => 'delete', 'items' => 'x' ) );
		$this->assertEquals( 3, count( $result->items ) );
	}

	public function testBulkDelete() {
		$this->createAB();
		$redirect = Red_Item::create( array( 'url' => '/', 'group_id' => $this->group->get_id(), 'action_type' => 'url', 'match_type' => 'url' ) );

		$result = $this->do_bulk( array( 'bulk' => 'delete', 'items' => $redirect->get_id() ) );
		$this->assertEquals( 2, count( $result->items ) );
	}

	public function testBulkDisable() {
		$this->createAB();
		$redirect = Red_Item::create( array( 'url' => '/', 'group_id' => $this->group->get_id(), 'action_type' => 'url', 'match_type' => 'url' ) );

		$result = $this->do_bulk( array( 'bulk' => 'disable', 'items' => $redirect->get_id() ) );
		$this->assertEquals( 3, count( $result->items ) );

		$redirect = Red_Item::get_by_id( $redirect->get_id() );
		$this->assertFalse( $redirect->is_enabled() );
	}

	public function testBulkEnable() {
		$this->createAB();
		$redirect = Red_Item::create( array( 'url' => '/', 'group_id' => $this->group->get_id(), 'action_type' => 'url', 'match_type' => 'url' ) );

		$result = $this->do_bulk( array( 'bulk' => 'enable', 'items' => $redirect->get_id() ) );
		$this->assertEquals( 3, count( $result->items ) );

		$redirect = Red_Item::get_by_id( $redirect->get_id() );
		$this->assertTrue( $redirect->is_enabled() );
	}

	public function testUpdateBadRedirect() {
		$this->createAB();

		$result = $this->set_redirect( array( 'id' => 'cats' ) );
		$this->assertTrue( isset( $result->error ) );
	}

	public function testUpdate() {
		$this->createAB();
		$redirect = Red_Item::create( array( 'url' => '/', 'group_id' => $this->group->get_id(), 'action_type' => 'url', 'match_type' => 'url' ) );

		$result = $this->set_redirect( array( 'id' => $redirect->get_id(), 'url' => '/cats', 'group_id' => $this->group->get_id(), 'action_type' => 'url', 'match_type' => 'url' ) );
		$redirect = Red_Item::get_by_id( $redirect->get_id() );

		$this->assertEquals( '/cats', $redirect->get_url() );
	}

	public function testCreateBadRedirect() {
		$this->createAB();

		$result = $this->set_redirect();
		$this->assertTrue( isset( $result->error ) );

		$result = $this->set_redirect( array( 'group_id' => 5, 'url' => 'yes', 'match_type' => 'url', 'action_type' => 'url' ) );
		$this->assertTrue( isset( $result->error ) );

		$result = $this->set_redirect( array( 'group_id' => 2, 'match_type' => 'url', 'action_type' => 'url' ) );
		$this->assertTrue( isset( $result->error ) );
	}

	public function testCreateRedirect() {
		$this->createAB();

		$result = $this->set_redirect( array( 'group_id' => 2, 'url' => 'yes', 'id' => 0, 'match_type' => 'url', 'action_type' => 'url' ) );
		$this->assertEquals( 3, count( $result->items ) );
	}
}
