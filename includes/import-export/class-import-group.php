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
	 * @var array<int, \Red_Group>
	 */
	private $group_map = [];

	/**
	 * @var int
	 */
	private $groups_created = 0;

	/**
	 * @param int $group_id Selected group ID.
	 * @param array<string, bool|string|array<int, string>> $options Import options.
	 */
	public function __construct( $group_id, array $options = [], ?GroupRepository $groups = null ) {
		$this->group_id = intval( $group_id, 10 );
		$this->is_dry_run = isset( $options['dry_run'] ) ? $options['dry_run'] === true : false;
		$this->groups = $groups ? $groups : new GroupRepository();
	}

	/**
	 * @param int|string $file_group_id Group ID referenced by the file.
	 * @param array<string, mixed>|null $group_data Group data from the file.
	 * @return \Red_Group|false
	 */
	public function get_group( $file_group_id = 0, $group_data = null ) {
		if ( $this->group_id > 0 ) {
			$existing = $this->groups->get( $this->group_id );
			if ( $existing !== false ) {
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
			$created = $this->groups->create( $group_data['name'], $group_data['module_id'], $group_data['enabled'] ? true : false );
			if ( $created !== false ) {
				$this->group_map[ $file_group_id ] = $created;
				return $created;
			}
		}

		return $this->create_fallback_group( $file_group_id );
	}

	/**
	 * @param int $group_map_id Group mapping key.
	 * @return \Red_Group|false
	 */
	private function create_fallback_group( $group_map_id ) {
		if ( $this->is_dry_run ) {
			return false;
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
	 * @return int
	 */
	public function get_groups_created() {
		return $this->groups_created;
	}
}
