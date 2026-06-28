<?php

namespace Redirection\ImportExport;

/**
 * Repository wrapper for group lookups and creation.
 *
 * @phpstan-import-type GroupJson from \Red_Group
 */
class GroupRepository {
	/**
	 * @param int $group_id
	 * @return \Red_Group|false
	 */
	public function get( $group_id ) {
		return \Red_Group::get( intval( $group_id, 10 ) );
	}

	/**
	 * @param string $name
	 * @param int $module_id
	 * @param bool $enabled
	 * @return \Red_Group|false
	 */
	public function create( $name, $module_id, $enabled = true ) {
		return \Red_Group::create( $name, $module_id, $enabled );
	}

	/**
	 * @return array<GroupJson>|false
	 */
	public function get_all() {
		return \Red_Group::get_all();
	}

	/**
	 * @param int $module_id
	 * @return array<GroupJson>|false
	 */
	public function get_all_for_module( $module_id ) {
		return \Red_Group::get_all_for_module( intval( $module_id, 10 ) );
	}
}
