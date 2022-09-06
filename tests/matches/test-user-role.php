<?php

require dirname( __FILE__ ) . '/../../matches/user-role.php';

class UserRoleMatchTest extends WP_UnitTestCase {
	public function testTargetSanitized() {
		$match = new Match\Role();
		$saved = array(
			'url_from' => '/some/url',
			'url_notfrom' => '/some/url',
			'role' => 'role',
		);
		$source = array(
			'url_from' => "/some/url\nsomethingelse1",
			'url_notfrom' => "/some/url\nsomethingelse2",
			'role' => 'role',
		);

		$this->assertEquals( $saved, $match->save( $source ) );
	}

	public function testBadData() {
		$match = new Match\Role();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'role' => '',
		);
		$this->assertEquals( $saved, $match->save( array( 'bad' => 'thing' ) ) );
	}

	public function testLoadBad() {
		$match = new Match\Role();
		$match->load( serialize( array( 'url_from' => 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', 'url_notfrom' => 'yes' ) ) );
		$this->assertEquals( 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', $match->url_from );
	}

	// public function testNoMatch() {
	// 	wp_set_current_user( 1 );

	// 	$action = function( $caps ) {
	// 		return $caps;
	// 	};
	// 	add_filter( 'user_has_cap', $action, 10, 2 );

	// 	$match = new Match\Role( serialize( array( 'url_from' => '', 'url_notfrom' => '', 'role' => 'special' ) ) );
	// 	$this->assertFalse( $match->is_match( '' ) );
	// }

	// public function testMatch() {
	// 	wp_set_current_user( 1 );

	// 	$action = function( $caps ) {
	// 		$caps['special'] = 1;
	// 		return $caps;
	// 	};
	// 	add_filter( 'user_has_cap', $action, 10, 2 );

	// 	$match = new Match\Role( serialize( array( 'url_from' => '', 'url_notfrom' => '', 'role' => 'special' ) ) );
	// 	$this->assertTrue( $match->is_match( '' ) );
	// }
}
