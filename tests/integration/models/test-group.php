<?php

class GroupTest extends WP_UnitTestCase {
	public function testCreate() {
		$group = Red_Group::create( 'normal name', 1 );

		$this->assertTrue( $group !== false );
	}

	public function testLongName() {
		$group = Red_Group::create( str_repeat( 'a', 51 ), 1 );

		$this->assertTrue( $group !== false );
	}
}
