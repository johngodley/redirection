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
	 * @return object|false
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
	 * @param int $group_map_id Group mapping key.
	 * @return object|false
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
	 * @param int $group_map_id Group mapping key.
	 * @param array<string, mixed> $group_data Group data from the file.
	 * @return object|false
	 */
	private function create_group_from_data( $group_map_id, array $group_data ) {
		if ( ! isset( $group_data['name'], $group_data['module_id'] ) ) {
			return false;
		}

		$enabled = true;
		if ( isset( $group_data['enabled'] ) ) {
			$enabled = $group_data['enabled'] === true;
		} elseif ( isset( $group_data['status'] ) && is_string( $group_data['status'] ) ) {
			$enabled = $group_data['status'] !== 'disabled';
		}

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
	 * @param int $group_id Preview group ID.
	 * @param string $name Group name.
	 * @param int $module_id Module ID.
	 * @param bool $enabled Whether the group is enabled.
	 * @return object
	 */
	private function get_preview_group( $group_id, $name, $module_id, $enabled ) {
		return new class( $group_id, $name, $module_id, $enabled ) {
			private $id;
			private $name;
			private $module_id;
			private $enabled;

			public function __construct( $group_id, $name, $module_id, $enabled ) {
				$this->id = intval( $group_id, 10 );
				$this->name = $name;
				$this->module_id = intval( $module_id, 10 );
				$this->enabled = $enabled ? true : false;
			}

			public function get_id() {
				return $this->id;
			}

			public function get_name() {
				return $this->name;
			}

			public function get_module_id() {
				return $this->module_id;
			}

			public function is_enabled() {
				return $this->enabled;
			}
		};
	}

	/**
	 * @return int
	 */
	public function get_groups_created() {
		return $this->groups_created;
	}
}
