<?php

/**
 * A group of redirects
 */
class Red_Group {
	/**
	 * Group ID
	 *
	 * @var integer
	 */
	private $id = 0;

	/**
	 * Group name
	 *
	 * @var String
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
	 * @var String
	 */
	private $status = 'enabled';

	/**
	 * Group position. Currently not used
	 *
	 * @var integer
	 */
	private $position = 0;

	/**
	 * Constructor
	 *
	 * @param String|Object $values Values.
	 */
	public function __construct( $values = '' ) {
		if ( is_object( $values ) ) {
			$this->name = $values->name;
			$this->id = intval( $values->id, 10 );

			if ( isset( $values->module_id ) ) {
				$this->module_id = intval( $values->module_id, 10 );
			}

			if ( isset( $values->status ) ) {
				$this->status = $values->status;
			}

			if ( isset( $values->position ) ) {
				$this->position = intval( $values->position, 10 );
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
	 * @return Red_Group|boolean
	 */
	public static function get( $id ) {
		static $groups = [];
		global $wpdb;

		if ( isset( $groups[ $id ] ) ) {
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
	 * @return Red_Group[]
	 */
	public static function get_all( $params = [] ) {
		global $wpdb;

		$where = '';
		if ( isset( $params['filterBy'] ) && is_array( $params['filterBy'] ) ) {
			$filters = new Red_Group_Filters( $params['filterBy'] );
			$where = $filters->get_as_sql();
		}

		$data = [];
		$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_groups $where" );

		if ( $rows ) {
			foreach ( $rows as $row ) {
				$group = new Red_Group( $row );
				$data[] = $group->to_json();
			}
		}

		return $data;
	}

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

	public static function get_for_select() {
		global $wpdb;

		$data = array();
		$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_groups" );

		if ( $rows ) {
			foreach ( $rows as $row ) {
				$module = Red_Module::get( $row->module_id );
				if ( $module ) {
					$data[ $module->get_name() ][ intval( $row->id, 10 ) ] = $row->name;
				}
			}
		}

		return $data;
	}

	public static function create( $name, $module_id, $enabled = true ) {
		global $wpdb;

		$name = trim( substr( $name, 0, 50 ) );
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

			return Red_Group::get( $wpdb->insert_id );
		}

		return false;
	}

	public function update( $data ) {
		global $wpdb;

		$old_id = $this->module_id;
		$this->name = trim( wp_kses( $data['name'], array() ) );

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

	public function get_total_redirects() {
		global $wpdb;

		return intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items WHERE group_id=%d", $this->id ) ), 10 );
	}

	public function enable() {
		global $wpdb;

		$wpdb->update( $wpdb->prefix . 'redirection_groups', array( 'status' => 'enabled' ), array( 'id' => $this->id ) );
		$wpdb->update( $wpdb->prefix . 'redirection_items', array( 'status' => 'enabled' ), array( 'group_id' => $this->id ) );

		Red_Module::flush( $this->id );
	}

	public function disable() {
		global $wpdb;

		$wpdb->update( $wpdb->prefix . 'redirection_groups', array( 'status' => 'disabled' ), array( 'id' => $this->id ) );
		$wpdb->update( $wpdb->prefix . 'redirection_items', array( 'status' => 'disabled' ), array( 'group_id' => $this->id ) );

		Red_Module::flush( $this->id );
	}

	public function get_module_id() {
		return $this->module_id;
	}

	public static function get_filtered( array $params ) {
		global $wpdb;

		$orderby = 'name';
		$direction = 'DESC';
		$limit = RED_DEFAULT_PER_PAGE;
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
			$limit = min( RED_MAX_PER_PAGE, $limit );
			$limit = max( 5, $limit );
		}

		if ( isset( $params['page'] ) ) {
			$offset = intval( $params['page'], 10 );
			$offset = max( 0, $offset );
			$offset *= $limit;
		}

		$rows = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}redirection_groups $where " . $wpdb->prepare( "ORDER BY $orderby $direction LIMIT %d,%d", $offset, $limit )
		);
		$total_items = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups " . $where ) );
		$items = array();

		$options = red_get_options();

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

	public static function delete_all( array $params ) {
		global $wpdb;

		$filters = new Red_Group_Filters( isset( $params['filterBy'] ) ? $params['filterBy'] : [] );
		$query = $filters->get_as_sql();

		$sql = "DELETE FROM {$wpdb->prefix}redirection_groups {$query}";

		// phpcs:ignore
		$wpdb->query( $sql );
	}

	public static function set_status_all( $action, array $params ) {
		global $wpdb;

		$filters = new Red_Group_Filters( isset( $params['filterBy'] ) ? $params['filterBy'] : [] );
		$query = $filters->get_as_sql();

		$sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}redirection_groups SET status=%s {$query}", $action === 'enable' ? 'enable' : 'disable' );

		// phpcs:ignore
		$wpdb->query( $sql );
	}
}

class Red_Group_Filters {
	private $filters = [];

	public function __construct( $filter_params ) {
		global $wpdb;

		foreach ( $filter_params as $filter_by => $filter ) {
			if ( $filter_by === 'status' ) {
				if ( $filter === 'enabled' ) {
					$this->filters[] = "status='enabled'";
				} else {
					$this->filters[] = "status='disabled'";
				}
			} elseif ( $filter_by === 'module' ) {
				$this->filters[] = $wpdb->prepare( 'module_id=%d', intval( $filter, 10 ) );
			} elseif ( $filter_by === 'name' ) {
				$this->filters[] = $wpdb->prepare( 'name LIKE %s', '%' . $wpdb->esc_like( trim( $filter ) ) . '%' );
			}
		}
	}

	public function get_as_sql() {
		if ( count( $this->filters ) > 0 ) {
			return ' WHERE ' . implode( ' AND ', $this->filters );
		}

		return '';
	}
}
