<?php

class GroupActionsTest extends WP_UnitTestCase {
	private $fired = [];

	public function setUp(): void {
		if ( ! red_is_refactor_enabled() ) {
			$this->markTestSkipped( 'Cache invalidation is implemented on the refactor path only.' );
		}

		parent::setUp();

		$this->fired = [];

		add_action(
			'redirection_group_updated',
			function ( $id ) {
				$this->fired[] = [ 'updated', $id ];
			}
		);
		add_action(
			'redirection_group_deleted',
			function ( $id ) {
				$this->fired[] = [ 'deleted', $id ];
			}
		);
	}

	public function tearDown(): void {
		remove_all_actions( 'redirection_group_updated' );
		remove_all_actions( 'redirection_group_deleted' );

		parent::tearDown();
	}

	private function make_group() {
		return Red_Group::create( 'Cache test group', 1 );
	}

	public function testUpdateFiresAction() {
		$group = $this->make_group();

		$group->update( [ 'name' => 'Renamed', 'moduleId' => 1 ] );

		$this->assertSame( [ [ 'updated', $group->get_id() ] ], $this->fired );
	}

	public function testDisableFiresUpdatedAction() {
		$group = $this->make_group();

		$group->disable();

		$this->assertSame( [ [ 'updated', $group->get_id() ] ], $this->fired );
	}

	public function testEnableFiresUpdatedAction() {
		$group = $this->make_group();
		$group->disable();
		$this->fired = [];

		$group->enable();

		$this->assertSame( [ [ 'updated', $group->get_id() ] ], $this->fired );
	}

	public function testDeleteFiresAction() {
		$group = $this->make_group();
		$id = $group->get_id();

		$group->delete();

		$this->assertSame( [ [ 'deleted', $id ] ], $this->fired );
	}
}
