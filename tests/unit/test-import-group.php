<?php

require_once PLUGIN_PATH . '/tests/unit/stubs/class-test-import-group-item.php';
require_once PLUGIN_PATH . '/includes/import-export/class-group-repository.php';
require_once PLUGIN_PATH . '/includes/import-export/class-import-group.php';

use Redirection\ImportExport\GroupRepository;
use Redirection\ImportExport\ImportGroup;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ImportGroupUnitTest extends TestCase {
	private function get_repository() {
		return new class() extends GroupRepository {
			public $groups = [];
			public $created = [];

			public function get( $group_id ) {
				return $this->groups[ intval( $group_id, 10 ) ] ?? false;
			}

			public function create( $name, $module_id, $enabled = true ) {
				$id = count( $this->groups ) + 100;
				$group = new Test_Import_Group_Item( $id, $enabled );

				$this->groups[ $id ] = $group;
				$this->created[] = [
					'name' => $name,
					'module_id' => $module_id,
					'enabled' => $enabled,
				];

				return $group;
			}
		};
	}

	public function testReturnsConfiguredSelectedGroup() {
		$repository = $this->get_repository();
		$repository->groups[5] = new Test_Import_Group_Item( 5 );
		$import_group = new ImportGroup( 5, [], $repository );

		$resolved = $import_group->get_group( 999 );

		$this->assertEquals( 5, $resolved->get_id() );
		$this->assertCount( 0, $repository->created );
	}

	public function testCreatesFallbackGroupOncePerMissingFileGroupId() {
		$repository = $this->get_repository();
		$import_group = new ImportGroup( 0, [], $repository );

		$first = $import_group->get_group( 555 );
		$second = $import_group->get_group( 555 );

		$this->assertEquals( $first->get_id(), $second->get_id() );
		$this->assertCount( 1, $repository->created );
		$this->assertEquals( 1, $import_group->get_groups_created() );
	}

	public function testDryRunDoesNotCreateFallbackGroup() {
		$repository = $this->get_repository();
		$import_group = new ImportGroup( 0, [ 'dry_run' => true ], $repository );

		$this->assertFalse( $import_group->get_group( 555 ) );
		$this->assertCount( 0, $repository->created );
	}
}
