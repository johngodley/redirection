<?php

use Redirection\Group;

class GroupTest extends WP_UnitTestCase {
	public function testCreate() {
		$group = Group\Group::create( 'normal name', 1 );

		$this->assertTrue( $group !== false );
	}

	public function testLongName() {
		$group = Group\Group::create( str_repeat( 'a', 51 ), 1 );

		$this->assertTrue( $group !== false );
	}
}
