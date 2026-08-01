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

	public function testDropdownIgnoresRedirectCount() {
		global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->prefix}redirection_groups" );
		$group = Red_Group::create( 'group with redirects', 1 );

		Red_Item::create(
			[
				'url' => 'url',
				'match_type' => 'url',
				'action_type' => 'url',
				'group_id' => $group->get_id(),
			]
		);

		$result = Red_Group::get_for_dropdown();

		$this->assertEquals( 0, $result['items'][0]['redirects'] );
	}

	public function testDropdownDoesNotQueryPerGroup() {
		global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->prefix}redirection_groups" );
		for ( $i = 0; $i < 10; $i++ ) {
			Red_Group::create( 'group' . $i, 1 );
		}

		$before = $wpdb->num_queries;
		Red_Group::get_for_dropdown();
		$queries = $wpdb->num_queries - $before;

		$this->assertLessThan( 5, $queries );
	}

	public function testDropdownLimit() {
		global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->prefix}redirection_groups" );

		$values = [];
		for ( $i = 0; $i < Red_Group::DROPDOWN_LIMIT + 5; $i++ ) {
			$values[] = $wpdb->prepare( '(%s, %d, %d, %s)', 'group' . $i, 1, $i, 'enabled' );
		}

		$wpdb->query( "INSERT INTO {$wpdb->prefix}redirection_groups (name, module_id, position, status) VALUES " . implode( ',', $values ) );

		$result = Red_Group::get_for_dropdown();

		$this->assertCount( Red_Group::DROPDOWN_LIMIT, $result['items'] );
	}
}
