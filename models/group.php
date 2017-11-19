<?php

class Red_Group {
	private $items = 0;
	private $name;
	private $tracking;
	private $module_id;
	private $status;
	private $position;

	public function __construct( $values = ''  ) {
		if ( is_object( $values ) ) {
			foreach ( $values as $key => $value ) {
			 	$this->$key = $value;
			}

			$this->id = intval( $this->id, 10 );
			$this->module_id = intval( $this->module_id, 10 );
		}
	}

	public function get_name() {
		return $this->name;
	}

	public function get_id() {
		return $this->id;
	}

	public function is_enabled() {
		return $this->status === 'enabled' ? true : false;
	}

	static function get( $id ) {
		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT {$wpdb->prefix}redirection_groups.*,COUNT( {$wpdb->prefix}redirection_items.id ) AS items,SUM( {$wpdb->prefix}redirection_items.last_count ) AS redirects FROM {$wpdb->prefix}redirection_groups LEFT JOIN {$wpdb->prefix}redirection_items ON {$wpdb->prefix}redirection_items.group_id={$wpdb->prefix}redirection_groups.id WHERE {$wpdb->prefix}redirection_groups.id=%d GROUP BY {$wpdb->prefix}redirection_groups.id", $id ) );
		if ( $row ) {
			return new Red_Group( $row );
		}

		return false;
	}

	static function get_all() {
		global $wpdb;

		$data = array();
		$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_groups" );

		if ( $rows ) {
			foreach ( $rows as $row ) {
				$group = new Red_Group( $row );
				$data[] = $group->to_json();
			}
		}

		return $data;
	}

	static function get_all_for_module( $module_id ) {
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

	static function get_for_select() {
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

	static function create( $name, $module_id ) {
		global $wpdb;

		$name = trim( substr( $name, 0, 50 ) );
		$module_id = intval( $module_id, 10 );

		if ( $name !== '' && Red_Module::is_valid_id( $module_id ) ) {
			$position = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( * ) FROM {$wpdb->prefix}redirection_groups WHERE module_id=%d", $module_id ) );

			$data = array(
				'name'      => trim( $name ),
				'module_id' => intval( $module_id ),
				'position'  => intval( $position ),
			);

			$wpdb->insert( $wpdb->prefix.'redirection_groups', $data );

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

		$wpdb->update( $wpdb->prefix.'redirection_groups', array( 'name' => $this->name, 'module_id' => $this->module_id ), array( 'id' => intval( $this->id ) ) );

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

		if ( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups" ) === 0 )
			$wpdb->insert( $wpdb->prefix.'redirection_groups', array( 'name' => __( 'Redirections' ), 'module_id' => 1, 'position' => 0 ) );
	}

	public function get_total_redirects() {
		global $wpdb;

		return intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items WHERE group_id=%d", $this->id ) ), 10 );
	}

	public function enable() {
		global $wpdb;

		$wpdb->update( $wpdb->prefix.'redirection_groups', array( 'status' => 'enabled' ), array( 'id' => $this->id ) );
		$wpdb->update( $wpdb->prefix.'redirection_items', array( 'status' => 'enabled' ), array( 'group_id' => $this->id ) );

		Red_Module::flush( $this->id );
	}

	public function disable() {
		global $wpdb;

		$wpdb->update( $wpdb->prefix.'redirection_groups', array( 'status' => 'disabled' ), array( 'id' => $this->id ) );
		$wpdb->update( $wpdb->prefix.'redirection_items', array( 'status' => 'disabled' ), array( 'group_id' => $this->id ) );

		Red_Module::flush( $this->id );
	}

	public function get_module_id() {
		return $this->module_id;
	}

	public static function get_filtered( array $params ) {
		global $wpdb;

		$orderby = 'id';
		$direction = 'DESC';
		$limit = RED_DEFAULT_PER_PAGE;
		$offset = 0;
		$where = '';

		if ( isset( $params['orderBy'] ) && in_array( $params['orderBy'], array( 'name' ), true ) ) {
			$orderby = $params['orderBy'];
		}

		if ( isset( $params['direction'] ) && in_array( $params['direction'], array( 'asc', 'desc' ), true ) ) {
			$direction = strtoupper( $params['direction'] );
		}

		if ( isset( $params['filter'] ) && strlen( $params['filter'] ) > 0 ) {
			if ( isset( $params['filterBy'] ) && $params['filterBy'] === 'module' ) {
				$where = $wpdb->prepare( "WHERE module_id=%d", intval( $params['filter'], 10 ) );
			} else {
				$where = $wpdb->prepare( 'WHERE name LIKE %s', '%'.$wpdb->esc_like( trim( $params['filter'] ) ).'%' );
			}
		}

		if ( isset( $params['perPage'] ) ) {
			$limit = intval( $params['perPage'], 10 );
			$limit = min( RED_MAX_PER_PAGE, $limit );
			$limit = max( 5, $limit );
		}

		if ( isset( $params['page'] ) ) {
			$offset = intval( $params['page'], 10 );
			$offset = max( 0, $offset );
			$offset *= $limit;
		}

		$table = $wpdb->prefix.'redirection_groups';
		$sql = trim( "SELECT * FROM {$table} $where " ).$wpdb->prepare( " ORDER BY $orderby $direction LIMIT %d,%d", $offset, $limit );

		$rows = $wpdb->get_results( $sql );
		$total_items = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$table} ".$where ) );
		$items = array();

		foreach ( $rows as $row ) {
			$group = new Red_Group( $row );
			$items[] = $group->to_json();
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
}
