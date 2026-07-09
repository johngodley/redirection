<?php

namespace Redirection\ImportExport;

/**
 * Resolve import groups for file importers.
 */
class ImportGroup {
	/**
	 * @var GroupRepository
	 */
	private $groups;

	/**
	 * @var int
	 */
	private $group_id;

	/**
	 * @var bool
	 */
	private $is_dry_run = false;

	/**
	 * @var 'import'|'ignore'|'update'
	 */
	private $duplicate_mode = 'import';

	/**
	 * @var array<int, \Red_Group|ImportPreviewGroup>
	 */
	private $group_map = [];

	/**
	 * @var int
	 */
	private $groups_created = 0;

	/**
	 * @var int
	 */
	private $groups_updated = 0;

	/**
	 * @var int
	 */
	private $groups_ignored = 0;

	/**
	 * @param int $group_id Selected group ID.
	 * @param array<string, bool|string|array<int, string>> $options Import options.
	 */
	public function __construct( $group_id, array $options = [], ?GroupRepository $groups = null ) {
		$this->group_id = intval( $group_id, 10 );
		$this->is_dry_run = isset( $options['dry_run'] ) ? $options['dry_run'] === true : false;
		if ( isset( $options['duplicate_mode'] ) && in_array( $options['duplicate_mode'], [ 'import', 'ignore', 'update' ], true ) ) {
			$this->duplicate_mode = $options['duplicate_mode'];
		}
		$this->groups = $groups ? $groups : new GroupRepository();
	}

	/**
	 * @param int|string $file_group_id Group ID referenced by the file.
	 * @param array<string, mixed>|null $group_data Group data from the file.
	 * @return \Red_Group|ImportPreviewGroup|false
	 */
	public function get_group( $file_group_id = 0, $group_data = null ) {
		if ( $this->group_id > 0 ) {
			if ( isset( $this->group_map[ $this->group_id ] ) ) {
				return $this->group_map[ $this->group_id ];
			}

			$existing = $this->groups->get( $this->group_id );
			if ( $existing !== false ) {
				$this->group_map[ $this->group_id ] = $existing;
				return $existing;
			}

			return $this->create_fallback_group( $this->group_id );
		}

		$file_group_id = intval( $file_group_id, 10 );
		if ( isset( $this->group_map[ $file_group_id ] ) ) {
			return $this->group_map[ $file_group_id ];
		}

		$existing = $file_group_id > 0 ? $this->groups->get( $file_group_id ) : false;
		if ( $existing !== false ) {
			$this->group_map[ $file_group_id ] = $existing;
			return $existing;
		}

		if ( $group_data !== null ) {
			$created = $this->create_group_from_data( $file_group_id, $group_data );
			if ( $created !== false ) {
				return $created;
			}
		}

		return $this->create_fallback_group( $file_group_id );
	}

	/**
	 * Import a group record from a file and track duplicate handling.
	 *
	 * @param int|string $file_group_id Group ID referenced by the file.
	 * @param array<string, mixed> $group_data Group data from the file.
	 * @return \Red_Group|ImportPreviewGroup|false
	 */
	public function import_group( $file_group_id, array $group_data ) {
		$file_group_id = intval( $file_group_id, 10 );
		if ( isset( $this->group_map[ $file_group_id ] ) ) {
			return $this->group_map[ $file_group_id ];
		}

		$existing = $file_group_id > 0 ? $this->groups->get( $file_group_id ) : false;
		if ( $existing !== false ) {
			return $this->get_matching_group( $file_group_id, $existing, $group_data );
		}

		if ( $this->duplicate_mode !== 'import' ) {
			$existing = $this->get_matching_group_by_name( $group_data );
			if ( $existing !== false ) {
				return $this->get_matching_group( $file_group_id, $existing, $group_data );
			}
		}

		return $this->create_group_from_data( $file_group_id, $group_data );
	}

	/**
	 * @param int $group_map_id Group mapping key.
	 * @return \Red_Group|ImportPreviewGroup|false
	 */
	private function create_fallback_group( $group_map_id ) {
		if ( $this->is_dry_run ) {
			$preview_group = $this->get_preview_group( $group_map_id, 'Group', 1, true );
			$this->group_map[ intval( $group_map_id, 10 ) ] = $preview_group;
			$this->groups_created++;

			return $preview_group;
		}

		$created = $this->groups->create( 'Group', 1 );
		if ( $created !== false ) {
			$this->group_map[ intval( $group_map_id, 10 ) ] = $created;
			$this->groups_created++;
			return $created;
		}

		return false;
	}

	/**
	 * @param array<string, mixed> $group_data Group data from the file.
	 * @return \Red_Group|false
	 */
	private function get_matching_group_by_name( array $group_data ) {
		if ( ! isset( $group_data['name'] ) ) {
			return false;
		}

		return $this->groups->get_by_name( strval( $group_data['name'] ) );
	}

	/**
	 * @param int $group_map_id Group mapping key.
	 * @param \Red_Group $existing Existing group.
	 * @param array<string, mixed>|null $group_data Group data from the file.
	 * @return \Red_Group|ImportPreviewGroup
	 */
	private function get_matching_group( $group_map_id, \Red_Group $existing, $group_data = null ) {
		if ( $this->duplicate_mode === 'update' && $group_data !== null ) {
			$updated = $this->update_group_from_data( $group_map_id, $existing, $group_data );
			if ( $updated !== false ) {
				return $updated;
			}
		}

		$this->group_map[ intval( $group_map_id, 10 ) ] = $existing;
		$this->groups_ignored++;
		return $existing;
	}

	/**
	 * @param int $group_map_id Group mapping key.
	 * @param array<string, mixed> $group_data Group data from the file.
	 * @return \Red_Group|ImportPreviewGroup|false
	 */
	private function create_group_from_data( $group_map_id, array $group_data ) {
		if ( ! isset( $group_data['name'], $group_data['module_id'] ) ) {
			return false;
		}

		$enabled = $this->get_enabled_from_data( $group_data );

		if ( $this->is_dry_run ) {
			$preview_group = $this->get_preview_group( $group_map_id, strval( $group_data['name'] ), intval( $group_data['module_id'], 10 ), $enabled );
			$this->group_map[ intval( $group_map_id, 10 ) ] = $preview_group;
			$this->groups_created++;

			return $preview_group;
		}

		$created = $this->groups->create( strval( $group_data['name'] ), intval( $group_data['module_id'], 10 ), $enabled );
		if ( $created !== false ) {
			$this->group_map[ intval( $group_map_id, 10 ) ] = $created;
			$this->groups_created++;
		}

		return $created;
	}

	/**
	 * @param int $group_map_id Group mapping key.
	 * @param \Red_Group $existing Existing group.
	 * @param array<string, mixed> $group_data Group data from the file.
	 * @return \Red_Group|ImportPreviewGroup|false
	 */
	private function update_group_from_data( $group_map_id, \Red_Group $existing, array $group_data ) {
		if ( ! isset( $group_data['name'], $group_data['module_id'] ) ) {
			return false;
		}

		$enabled = $this->get_enabled_from_data( $group_data );

		if ( $this->is_dry_run ) {
			$preview_group = $this->get_preview_group( $existing->get_id(), strval( $group_data['name'] ), intval( $group_data['module_id'], 10 ), $enabled );
			$this->group_map[ intval( $group_map_id, 10 ) ] = $preview_group;
			$this->groups_updated++;
			return $preview_group;
		}

		$updated = $this->groups->update( $existing, strval( $group_data['name'] ), intval( $group_data['module_id'], 10 ), $enabled );
		$this->group_map[ intval( $group_map_id, 10 ) ] = $updated;
		$this->groups_updated++;
		return $updated;
	}

	/**
	 * @param array<string, mixed> $group_data Group data from the file.
	 * @return bool
	 */
	private function get_enabled_from_data( array $group_data ) {
		if ( isset( $group_data['enabled'] ) ) {
			return $group_data['enabled'] === true;
		}

		if ( isset( $group_data['status'] ) && is_string( $group_data['status'] ) ) {
			return $group_data['status'] !== 'disabled';
		}

		return true;
	}

	/**
	 * @param int $group_id Preview group ID.
	 * @param string $name Group name.
	 * @param int $module_id Module ID.
	 * @param bool $enabled Whether the group is enabled.
	 * @return ImportPreviewGroup
	 */
	private function get_preview_group( $group_id, $name, $module_id, $enabled ) {
		return new ImportPreviewGroup( intval( $group_id, 10 ), $name, intval( $module_id, 10 ), $enabled ? true : false );
	}

	/**
	 * @return int
	 */
	public function get_groups_created() {
		return $this->groups_created;
	}

	/**
	 * @return int
	 */
	public function get_groups_updated() {
		return $this->groups_updated;
	}

	/**
	 * @return int
	 */
	public function get_groups_ignored() {
		return $this->groups_ignored;
	}
}
