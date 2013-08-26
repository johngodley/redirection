<?php

class Red_Group {
	function Red_Group(  $values = ''  )	{
		if ( is_object( $values ) ) {
			foreach ( $values AS $key => $value ) {
			 	$this->$key = $value;
			}
		}
	}

	/**
	 * Get list of groups
	 */
	static function get( $id ) {
		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT {$wpdb->prefix}redirection_groups.*,COUNT( {$wpdb->prefix}redirection_items.id ) AS items,SUM( {$wpdb->prefix}redirection_items.last_count ) AS redirects FROM {$wpdb->prefix}redirection_groups LEFT JOIN {$wpdb->prefix}redirection_items ON {$wpdb->prefix}redirection_items.group_id={$wpdb->prefix}redirection_groups.id WHERE {$wpdb->prefix}redirection_groups.id=%d GROUP BY {$wpdb->prefix}redirection_groups.id", $id ) );
		if ( $row )
			return new Red_Group( $row );
		return false;
	}

	static function get_for_module( $module ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS {$wpdb->prefix}redirection_groups.* FROM {$wpdb->prefix}redirection_groups WHERE {$wpdb->prefix}redirection_groups.module_id=%d", $module );

		$rows = $wpdb->get_results( $sql );
		$items = array();
		if ( count( $rows ) > 0 )	{
			foreach( $rows AS $row ) {
				$items[] = new Red_Group( $row );
			}
		}

		return $items;
	}

	/**
	 * Get all groups with number of items in each group
	 * DBW
	 */
	static function get_all( $module, $pager )	{
		global $wpdb;

		$sql  = $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS {$wpdb->prefix}redirection_groups.*,COUNT( {$wpdb->prefix}redirection_items.id ) AS items,SUM( {$wpdb->prefix}redirection_items.last_count ) AS redirects FROM {$wpdb->prefix}redirection_groups LEFT JOIN {$wpdb->prefix}redirection_items ON {$wpdb->prefix}redirection_items.group_id={$wpdb->prefix}redirection_groups.id WHERE {$wpdb->prefix}redirection_groups.module_id=%d", $module );
		$sql .= str_replace( 'WHERE', 'AND', $pager->to_limits( '', array( 'name' ), '', "GROUP BY {$wpdb->prefix}redirection_groups.id" ) );

		$rows = $wpdb->get_results( $sql );
		$pager->set_total( $wpdb->get_var( "SELECT FOUND_ROWS()" ) );
		$items = array();
		if ( count( $rows ) > 0 ) {
			foreach( $rows AS $row ) {
				$items[] = new Red_Group( $row );
			}
		}

		return $items;
	}

	/**
	 * Get list of groups
	 * DBW
	 */
	static function get_for_select() {
		global $wpdb;

		$data = array();
		$rows = $wpdb->get_results( "SELECT {$wpdb->prefix}redirection_modules.name AS module_name,{$wpdb->prefix}redirection_groups.name AS group_name,{$wpdb->prefix}redirection_groups.id FROM {$wpdb->prefix}redirection_groups INNER JOIN {$wpdb->prefix}redirection_modules ON {$wpdb->prefix}redirection_modules.id={$wpdb->prefix}redirection_groups.module_id ORDER BY {$wpdb->prefix}redirection_modules.name,{$wpdb->prefix}redirection_groups.position" );
		if ( $rows ) {
			foreach ( $rows AS $row ) {
				$data[$row->module_name][$row->id] = $row->group_name;
			}
		}

		return $data;
	}

	/**
	 * Get first group ID
	 */
	static function get_first_id()	{
		global $wpdb;

		return $wpdb->get_var( "SELECT id FROM {$wpdb->prefix}redirection_groups ORDER BY id LIMIT 0,1" );
	}

	static function create( $data ) {
		global $wpdb;

		$name   = trim( $data['name'] );
		$module = intval( $data['module_id'] );

		if ( $name != '' && $module > 0 ) {
			$position = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( * ) FROM {$wpdb->prefix}redirection_groups WHERE module_id=%d", $module ) );
			if ( isset( $data['position'] ) )
				$position = $data['position'];

			$data = array(
				'name'      => trim( $name ),
				'module_id' => intval( $module ),
				'position'  => intval( $position )
			);

			if ( isset( $data['status'] ) && isset( $data['position'] ) )
				$data['status'] = $data['status'];

			$wpdb->insert( $wpdb->prefix.'redirection_groups', $data );

			Red_Module::flush( $module );
			return $wpdb->insert_id;
		}

		return false;
	}

	function update( $data ) {
		global $wpdb;

		$this->tracking = isset( $data['tracking'] ) ? true : false;
		$this->status   = isset( $data['status'] ) ? 'enabled' : 'disabled';

		$wpdb->update( $wpdb->prefix.'redirection_groups', array( 'name' => $data['name'], 'status' => $this->status, 'tracking' => intval( $this->tracking ) ), array( 'id' => intval( $this->id ) ) );

		Red_Module::flush( $this->module_id );
	}

	static function delete( $group ) {
		global $wpdb;

		$obj = self::get( $group );

		// Delete all items in this group
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_items WHERE group_id=%d", $group ) );

		// Delete the group
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_groups WHERE id=%d", $group ) );

		// Update positions
		$rows = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}redirection_groups ORDER BY position" );
		if ( count( $rows ) > 0 ) {
			foreach ( $rows AS $pos => $row ) {
				$wpdb->update( $wpdb->prefix.'redirection_groups', array( 'position' => intval( $pos ) ), array( 'id' => intval( $row->id ) ) );
			}
		}
	}

	static function save_order( $items, $start ) {
		global $wpdb;

		foreach ( $items AS $pos => $id ) {
			$wpdb->update( $wpdb->prefix.'redirection_groups', array( 'position' => $pos + $start ), array( 'id' => intval( $id ) ) );
		}

		$group = self::get( $items[0] );
		Red_Module::flush( $group->module_id );
	}

	function move_to( $module ) {
		global $wpdb;

		$wpdb->update( $wpdb->prefix.'redirection_groups', array( 'module_id' => intval( $module ) ), array( 'id' => $this->id ) );

		Red_Module::flush( $module );
		Red_Module::flush( $this->id );
	}

	function reset() {
		global $wpdb;

		$this->last_count  = 0;
		$this->last_access = '0000-00-00 00:00:00';

		$wpdb->update( $wpdb->prefix.'redirection_items', array( 'last_count' => 0, 'last_access' => $this->last_access ), array( 'group_id' => $this->id ) );

		RE_Log::delete_for_group( $this->id );
	}

	function items() {
		if ( $this->items > 0 )
			return sprintf( ' (%d)', $this->items );
		return '';
	}

	function type() {
		if ( $this->apache )
			return '.ht';
		return 'WP';
	}

	function tracked() {
		if ( $this->tracking == 1 )
			return __( 'Yes', 'redirection' );
		return __( 'No', 'redirection' );
	}

	function toggle_status() {
		global $wpdb;

		$this->status = ( $this->status == 'enabled' ) ? 'disabled' : 'enabled';

		$wpdb->update( $wpdb->prefix.'redirection_groups', array( 'status' => $this->status ), array( 'id' => $this->id ) );
	}

	function hits() {
		global $wpdb;

		return (int)$wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_logs WHERE group_id=%d", $this->id ) );
	}
}
