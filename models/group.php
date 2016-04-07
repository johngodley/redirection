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
		if ( $row )
			return new Red_Group( $row );
		return false;
	}

	static function get_for_select() {
		global $wpdb;

		$data = array();
		$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_groups" );

		if ( $rows ) {
			foreach ( $rows as $row ) {
				$module = Red_Module::get( $row->module_id );
				if ( $module ) {
					$data[ $module->get_name() ][ $row->id ] = $row->name;
				}
			}
		}

		return $data;
	}

	static function create( $name, $module_id ) {
		global $wpdb;

		$name = trim( $name );

		if ( $name !== '' && $module_id > 0 ) {
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
		$this->name = trim( wp_kses( stripslashes( $data['name'] ), array() ) );

		if ( Red_Module::is_valid_id( intval( $data['module_id'] ) ) )
			$this->module_id = intval( $data['module_id'] );

		$wpdb->update( $wpdb->prefix.'redirection_groups', array( 'name' => $this->name, 'module_id' => $this->module_id ), array( 'id' => intval( $this->id ) ) );

		if ( $old_id !== $this->module_id ) {
			Red_Module::flush_by_module( $old_id );
			Red_Module::flush_by_module( $this->module_id );
		}
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

		return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items WHERE group_id=%d", $this->id ) );
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
}
