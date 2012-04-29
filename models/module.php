<?php

class Red_Module {
	function Red_Module( $values = '' )	{
		if ( is_object( $values ) ) {
			foreach ( $values AS $key => $value ) {
			 	$this->$key = $value;
			}

			if ( $this->options )
				$this->load( unserialize( $this->options ) );
		}
	}

	function module_flush( $items ) {
	}

	function module_flush_delete() {
	}

	function flush( $id ) {
		$module = Red_Module::get( $id );
		if ( $module && $module->is_valid() )
			$module->module_flush( Red_Item::get_all_for_module( $id ) );
	}

	function flush_delete( $id ) {
		$module = Red_Module::get( $id );
		if ( $module )
			$module->module_flush_delete();
	}

	function update( $data ) {
		global $wpdb;

		$this->name = $data['name'];
		$options = $this->save( $data );
		$wpdb->update( $wpdb->prefix.'redirection_modules', array( 'name' => trim( $data['name'] ), 'options' => empty( $options ) ? '' : serialize( $options ) ), array( 'id' => intval( $this->id ) ) );

		Red_Module::clear_cache( $this->id );
	}

	function delete() {
		global $wpdb;

		$groups = Red_Group::get_for_module( $this->id );
		if ( count( $groups ) > 0 ) {
			foreach ( $groups AS $group ) {
				$group->delete( $group->id );
			}
		}

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_modules WHERE id=%d", $this->id ) );

		RE_Log::delete_for_module( $this->id );
		Red_Module::clear_cache( $this->id );
		Red_Module::flush_delete( $this->id );
	}

	function clear_cache( $module ) {
		delete_option( 'redirection_module_cache' );
		Red_Module::flush( $module );
	}

	function create( $data ) {
		global $wpdb;

		if ( strlen( $data['name'] ) > 0 ) {
			$db = array(
				'name' => trim( $data['name'] ),
				'type' => $data['type'],
			);

			if ( isset( $data['options'] ) )
				$db['options'] = serialize( $data['options'] );

			$wpdb->insert( $wpdb->prefix.'redirection_modules', $db );

			Red_Module::flush( $wpdb->insert_id );
			return $wpdb->insert_id;
		}

		return false;
	}

	function get( $id ) {
		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}redirection_modules WHERE id=%d", $id ) );
		if ( $row )
			return Red_Module::new_item( $row );
		return false;
	}

	function get_by_type( $type )	{
		global $wpdb;

		$cache = get_option( 'redirection_module_cache' );
		if ( $cache && isset( $cache[$type] ) )
			return $cache[$type];

		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}redirection_modules WHERE type=%s ORDER BY id", $type ) );
		$items = array();
		if ( count( $rows ) > 0 ) {
			foreach ( $rows AS $row ) {
				$items[] = Red_Module::new_item( $row );
			}
		}

		$cache[$type] = $items;
		update_option( 'redirection_module_cache', $cache );
		return $items;
	}

	/**
	 * Get all modules
	 */
	function get_all() {
		global $wpdb;

		$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_modules WHERE id > 0 ORDER BY id" );
		$items = array();
		if ( count( $rows ) > 0 ) {
			foreach ( $rows AS $row ) {
				$items[] = Red_Module::new_item( $row );
			}
		}

		return $items;
	}

	/**
	 * Get first module
	 */
	function get_first_id() {
		global $wpdb;
		return $wpdb->get_var( "SELECT id FROM {$wpdb->prefix}redirection_modules ORDER BY id LIMIT 0,1" );
	}

	/**
	 * Get all modules
	 */

	function get_for_select() {
		$data  = array();
		$items = Red_Module::get_all();

		foreach ( $items AS $item ) {
			$data[$item->id] = $item->name;
		}

		return $data;
	}

	/**
	 * Get all module types
	 */
	function get_types() {
		return array (
			'apache' => __( 'Apache', 'redirection' ),
			'wp'     => __( 'WordPress', 'redirection' ),
			'404'    => __( '404 Errors', 'redirection' ),
		 );
	}

	function new_item( $data ) {
		$map = array (
			'apache' => array( 'Apache_Module',    'apache.php' ),
			'wp'     => array( 'WordPress_Module', 'wordpress.php' ),
			'404'    => array( 'Error404_Module',  '404.php' )
		 );

		if ( isset( $map[$data->type] ) ) {
			$obj  = $map[$data->type][0];
			$file = $map[$data->type][1];

			if ( !class_exists( $obj ) )
				include dirname( __FILE__ )."/../modules/$file";

			return new $obj( $data );
		}

		return false;
	}

	function canonical() {
		$can = array( 'none' => '&mdash;', 'nowww' => __( 'Strip WWW', 'redirection' ), 'www' => __( 'Force WWW', 'redirection' ) );
		return $can[$this->canonical];
	}

	function index() {
		$can = array( 'ignore' => '&mdash;', 'remove' => __( 'Strip index.php', 'redirection' ) );
		return $can[$this->index];
	}

	function options() {
	}

	function type() {
		$types = $this->get_types();
		return $types[$this->type];
	}

	function checked( $item, $field = '' ) {
		if ( $field && is_array( $item ) ) {
			if ( isset( $item[$field] ) && $item[$field] )
				echo ' checked="checked"';
		}
		elseif ( !empty( $item ) )
			echo ' checked="checked"';
	}

	function select( $items, $default = '' ) {
		if ( count( $items ) > 0 ) {
			foreach ( $items AS $key => $value ) {
				echo '<option value="'.$key.'"'.($key == $default ? ' selected="selected"' : '' ).'>'.$value.'</option>';
			}
		}
	}

	function groups() {
		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id ) FROM {$wpdb->prefix}redirection_groups WHERE module_id=%d", $this->id ) );
	}

	function redirects() {
		global $wpdb;

		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT({$wpdb->prefix}redirection_items.id) FROM {$wpdb->prefix}redirection_groups INNER JOIN {$wpdb->prefix}redirection_items ON {$wpdb->prefix}redirection_items.group_id={$wpdb->prefix}redirection_groups.id WHERE module_id=%d GROUP BY {$wpdb->prefix}redirection_items.group_id", $this->id ) );
		if ( $count > 0 )
			return $count;
		return 0;
	}

	function hits() {
		global $wpdb;

		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id ) FROM {$wpdb->prefix}redirection_logs WHERE module_id=%d", $this->id ) );
		if ( $count > 0 )
			return $count;
		return 0;
	}

	function reset() {
		Red_Module::clear_cache( $this->id );

		$groups = Red_Group::get_for_module( $this->id );
		if ( count( $groups ) > 0 )	{
			foreach ( $groups AS $group ) {
				$group->reset();
			}
		}

		RE_Log::delete_for_module( $this->id );
	}

	function is_valid() {
		return true;
	}

	function load( $data ) {
	}

	function config() {
	}
}

