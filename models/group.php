<?php

require_once __DIR__ . '/group-filter.php';

/**
 * A group of redirects
 *
 * @phpstan-type GroupData object{
 *     id: int,
 *     name: string,
 *     module_id?: int,
 *     status?: string,
 *     position?: int
 * }
 * @phpstan-type GroupJson array{
 *     id: int,
 *     name: string,
 *     redirects: int,
 *     module_id: int,
 *     moduleName: string,
 *     enabled: bool,
 *     default?: bool
 * }
 * @phpstan-type GroupFilteredResult array{
 *     items: array<GroupJson>,
 *     total: int
 * }
 * @phpstan-type GroupSelectData array<string, array<int, string>>
 */
class Red_Group {
	const DEFAULT_PER_PAGE = 25;
	const MAX_PER_PAGE = 200;

	/**
	 * Group ID
	 *
	 * @var integer
	 */
	private $id = 0;

	/**
	 * Group name
	 *
	 * @var string
	 */
	private $name = '';

	/**
	 * Module ID
	 *
	 * @var integer
	 */
	private $module_id = 0;

	/**
	 * Group status - 'enabled' or 'disabled'
	 *
	 * @var string
	 */
	private $status = 'enabled';

	/**
	 * Constructor
	 *
	 * @param GroupData|string $values Values.
	 */
	public function __construct( $values = '' ) {
		if ( is_object( $values ) ) {
			$this->name = sanitize_text_field( $values->name );
			$this->id = intval( $values->id, 10 );

			if ( isset( $values->module_id ) ) {
				$this->module_id = intval( $values->module_id, 10 );
			}

			if ( isset( $values->status ) ) {
				$this->status = $values->status;
			}
		}
	}

	/**
	 * Get group name
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get group ID
	 *
	 * @return integer
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Is the group enabled or disabled?
	 *
	 * @return boolean
	 */
	public function is_enabled() {
		return $this->status === 'enabled' ? true : false;
	}

	/**
	 * Get a group given an ID
	 *
	 * @param integer $id Group ID.
	 * @param bool $clear Clear cache.
	 * @return Red_Group|false
	 */
	public static function get( $id, $clear = false ) {
		static $groups = [];
		global $wpdb;

		if ( isset( $groups[ $id ] ) && ! $clear ) {
			$row = $groups[ $id ];
		} else {
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT {$wpdb->prefix}redirection_groups.*,COUNT( {$wpdb->prefix}redirection_items.id ) AS items,SUM( {$wpdb->prefix}redirection_items.last_count ) AS redirects FROM {$wpdb->prefix}redirection_groups LEFT JOIN {$wpdb->prefix}redirection_items ON {$wpdb->prefix}redirection_items.group_id={$wpdb->prefix}redirection_groups.id WHERE {$wpdb->prefix}redirection_groups.id=%d GROUP BY {$wpdb->prefix}redirection_groups.id", $id ) );
		}

		if ( $row ) {
			$groups[ $id ] = $row;
			return new Red_Group( $row );
		}

		return false;
	}

	/**
	 * Get all groups
	 *
	 * @param array<string, mixed> $params Optional filter parameters.
	 * @return array<GroupJson>
	 */
	public static function get_all( $params = [] ) {
		global $wpdb;

		$where = '';
		if ( isset( $params['filterBy'] ) && is_array( $params['filterBy'] ) ) {
			$filters = new Red_Group_Filters( $params['filterBy'] );
			$where = $filters->get_as_sql();
		}

		$data = [];
		// phpcs:ignore
		$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_groups $where" );

		if ( $rows ) {
			foreach ( $rows as $row ) {
				$group = new Red_Group( $row );
				$data[] = $group->to_json();
			}
		}

		return $data;
	}

	/**
	 * Get all groups for a specific module
	 *
	 * @param int $module_id Module ID.
	 * @return array<GroupJson>
	 */
	public static function get_all_for_module( $module_id ) {
		global $wpdb;

		$data = array();
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}redirection_groups WHERE module_id=%d", $module_id ) );

		if ( $rows ) {
			foreach ( $rows as $row ) {
				$group = new Red_Group( $row );
				$data[] = $group->to_json();
			}
		}

		return $data;
	}

	/**
	 * Get groups formatted for select dropdown
	 *
	 * @return GroupSelectData
	 */
	public static function get_for_select() {
		global $wpdb;

		$data = array();
		$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_groups" );

		if ( $rows ) {
			foreach ( $rows as $row ) {
				$module = Red_Module::get( $row->module_id );

				if ( $module !== false ) {
					$data[ $module->get_name() ][ intval( $row->id, 10 ) ] = $row->name;
				}
			}
		}

		return $data;
	}

	/**
	 * Create a new group
	 *
	 * @param string $name Group name.
	 * @param int $module_id Module ID.
	 * @param bool $enabled Whether the group is enabled.
	 * @return Red_Group|false
	 */
	public static function create( $name, $module_id, $enabled = true ) {
		global $wpdb;

		$name = trim( wp_kses( sanitize_text_field( $name ), 'strip' ) );
		$name = substr( $name, 0, 50 );
		$module_id = intval( $module_id, 10 );

		if ( $name !== '' && Red_Module::is_valid_id( $module_id ) ) {
			$position = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( * ) FROM {$wpdb->prefix}redirection_groups WHERE module_id=%d", $module_id ) );

			$data = array(
				'name'      => trim( $name ),
				'module_id' => intval( $module_id ),
				'position'  => intval( $position ),
				'status'    => $enabled ? 'enabled' : 'disabled',
			);

			$wpdb->insert( $wpdb->prefix . 'redirection_groups', $data );

			return self::get( $wpdb->insert_id );
		}

		return false;
	}

	/**
	 * Update group details
	 *
	 * @param array<string, mixed> $data Update data.
	 * @return bool
	 */
	public function update( $data ) {
		global $wpdb;

		$old_id = $this->module_id;
		$this->name = trim( wp_kses( sanitize_text_field( $data['name'] ), 'strip' ) );
		$this->name = substr( $this->name, 0, 50 );

		if ( Red_Module::is_valid_id( intval( $data['moduleId'], 10 ) ) ) {
			$this->module_id = intval( $data['moduleId'], 10 );
		}

		$wpdb->update( $wpdb->prefix . 'redirection_groups', array( 'name' => $this->name, 'module_id' => $this->module_id ), array( 'id' => intval( $this->id ) ) );

		if ( $old_id !== $this->module_id ) {
			Red_Module::flush_by_module( $old_id );
			Red_Module::flush_by_module( $this->module_id );
		}

		return true;
	}

	/**
	 * Delete this group and all its redirects
	 *
	 * @return void
	 */
	public function delete() {
		global $wpdb;

		// Delete all items in this group
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_items WHERE group_id=%d", $this->id ) );

		Red_Module::flush( $this->id );

		// Delete the group
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_groups WHERE id=%d", $this->id ) );

		if ( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups" ) === 0 ) {
			$wpdb->insert( $wpdb->prefix . 'redirection_groups', array( 'name' => __( 'Redirections', 'redirection' ), 'module_id' => 1, 'position' => 0 ) );
		}
	}

	/**
	 * Get total number of redirects in this group
	 *
	 * @return int
	 */
	public function get_total_redirects() {
		global $wpdb;

		return intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items WHERE group_id=%d", $this->id ) ), 10 );
	}

	/**
	 * Enable this group and all its redirects
	 *
	 * @return void
	 */
	public function enable() {
		global $wpdb;

		$wpdb->update( $wpdb->prefix . 'redirection_groups', array( 'status' => 'enabled' ), array( 'id' => $this->id ) );
		$wpdb->update( $wpdb->prefix . 'redirection_items', array( 'status' => 'enabled' ), array( 'group_id' => $this->id ) );

		Red_Module::flush( $this->id );
	}

	/**
	 * Disable this group and all its redirects
	 *
	 * @return void
	 */
	public function disable() {
		global $wpdb;

		$wpdb->update( $wpdb->prefix . 'redirection_groups', array( 'status' => 'disabled' ), array( 'id' => $this->id ) );
		$wpdb->update( $wpdb->prefix . 'redirection_items', array( 'status' => 'disabled' ), array( 'group_id' => $this->id ) );

		Red_Module::flush( $this->id );
	}

	/**
	 * Get the module ID for this group
	 *
	 * @return int
	 */
	public function get_module_id() {
		return $this->module_id;
	}

	/**
	 * Get filtered groups with pagination
	 *
	 * @param array<string, mixed> $params Filter and pagination parameters.
	 * @return GroupFilteredResult
	 */
	public static function get_filtered( array $params ) {
		global $wpdb;

		$orderby = 'name';
		$direction = 'DESC';
		$limit = self::DEFAULT_PER_PAGE;
		$offset = 0;
		$where = '';

		if ( isset( $params['orderby'] ) && in_array( $params['orderby'], array( 'name', 'id' ), true ) ) {
			$orderby = $params['orderby'];
		}

		if ( isset( $params['direction'] ) && in_array( $params['direction'], array( 'asc', 'desc' ), true ) ) {
			$direction = strtoupper( $params['direction'] );
		}

		if ( isset( $params['filterBy'] ) && is_array( $params['filterBy'] ) ) {
			$filters = new Red_Group_Filters( $params['filterBy'] );
			$where = $filters->get_as_sql();
		}

		if ( isset( $params['per_page'] ) ) {
			$limit = intval( $params['per_page'], 10 );
			$limit = min( self::MAX_PER_PAGE, $limit );
			$limit = max( 5, $limit );
		}

		if ( isset( $params['page'] ) ) {
			$offset = intval( $params['page'], 10 );
			$offset = max( 0, $offset );
			$offset *= $limit;
		}

		$rows = $wpdb->get_results(
			// phpcs:ignore
			"SELECT * FROM {$wpdb->prefix}redirection_groups $where " . $wpdb->prepare( "ORDER BY $orderby $direction LIMIT %d,%d", $offset, $limit )
		);
		$total_items = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups " . $where ) );
		$items = array();

		$options = Red_Options::get();

		foreach ( $rows as $row ) {
			$group = new Red_Group( $row );
			$group_json = $group->to_json();

			if ( $group->get_id() === $options['last_group_id'] ) {
				$group_json['default'] = true;
			}

			$items[] = $group_json;
		}

		return array(
			'items' => $items,
			'total' => intval( $total_items, 10 ),
		);
	}

	/**
	 * Convert group to JSON representation
	 *
	 * @return GroupJson
	 */
	public function to_json() {
		$module = Red_Module::get( $this->get_module_id() );

		return array(
			'id' => $this->get_id(),
			'name' => $this->get_name(),
			'redirects' => $this->get_total_redirects(),
			'module_id' => $this->get_module_id(),
			'moduleName' => $module ? $module->get_name() : '',
			'enabled' => $this->is_enabled(),
		);
	}

	/**
	 * Delete all groups matching filters
	 *
	 * @param array<string, mixed> $params Filter parameters.
	 * @return void
	 */
	public static function delete_all( array $params ) {
		global $wpdb;

		$filters = new Red_Group_Filters( isset( $params['filterBy'] ) ? $params['filterBy'] : [] );
		$query = $filters->get_as_sql();

		$sql = "DELETE FROM {$wpdb->prefix}redirection_groups {$query}";

		// phpcs:ignore
		$wpdb->query( $sql );
	}

	/**
	 * Set status for all groups matching filters
	 *
	 * @param string $action Action to perform ('enable' or 'disable').
	 * @param array<string, mixed> $params Filter parameters.
	 * @return void
	 */
	public static function set_status_all( $action, array $params ) {
		global $wpdb;

		$filters = new Red_Group_Filters( isset( $params['filterBy'] ) ? $params['filterBy'] : [] );
		$query = $filters->get_as_sql();

		// phpcs:ignore
		$sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}redirection_groups SET status=%s {$query}", $action === 'enable' ? 'enable' : 'disable' );

		// phpcs:ignore
		$wpdb->query( $sql );
	}
}
