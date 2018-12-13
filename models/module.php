<?php

include_once dirname( dirname( __FILE__ ) ) . '/modules/wordpress.php';
include_once dirname( dirname( __FILE__ ) ) . '/modules/apache.php';
include_once dirname( dirname( __FILE__ ) ) . '/modules/nginx.php';

abstract class Red_Module {
	public function __construct( $options ) {
		if ( is_array( $options ) ) {
			$this->load( $options );
		}
	}

	static function get( $id ) {
		$id = intval( $id, 10 );
		$options = red_get_options();

		if ( $id === Apache_Module::MODULE_ID ) {
			return new Apache_Module( isset( $options['modules'][ Apache_Module::MODULE_ID ] ) ? $options['modules'][ Apache_Module::MODULE_ID ] : array() );
		} elseif ( $id === WordPress_Module::MODULE_ID ) {
			return new WordPress_Module( isset( $options['modules'][ WordPress_Module::MODULE_ID ] ) ? $options['modules'][ WordPress_Module::MODULE_ID ] : array() );
		} elseif ( $id === Nginx_Module::MODULE_ID ) {
			return new Nginx_Module( isset( $options['modules'][ Nginx_Module::MODULE_ID ] ) ? $options['modules'][ Nginx_Module::MODULE_ID ] : array() );
		}

		return false;
	}

	public function get_total_redirects() {
		global $wpdb;

		return intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items INNER JOIN {$wpdb->prefix}redirection_groups ON {$wpdb->prefix}redirection_items.group_id={$wpdb->prefix}redirection_groups.id WHERE {$wpdb->prefix}redirection_groups.module_id=%d", $this->get_id() ) ), 10 );
	}

	static public function is_valid_id( $id ) {
		if ( $id === Apache_Module::MODULE_ID || $id === WordPress_Module::MODULE_ID || $id === Nginx_Module::MODULE_ID ) {
			return true;
		}

		return false;
	}

	static function get_all() {
		return array(
			WordPress_Module::MODULE_ID => Red_Module::get( WordPress_Module::MODULE_ID )->get_name(),
			Apache_Module::MODULE_ID    => Red_Module::get( Apache_Module::MODULE_ID )->get_name(),
			Nginx_Module::MODULE_ID     => Nginx_Module::get( Nginx_Module::MODULE_ID )->get_name(),
		);
	}

	static function get_id_for_name( $name ) {
		$names = array(
			'wordpress' => WordPress_Module::MODULE_ID,
			'apache'    => Apache_Module::MODULE_ID,
			'nginx'     => Nginx_Module::MODULE_ID,
		);

		if ( isset( $names[ $name ] ) ) {
			return $names[ $name ];
		}

		return false;
	}

	static function flush( $group_id ) {
		$group = Red_Group::get( $group_id );

		if ( $group ) {
			$module = Red_Module::get( $group->get_module_id() );

			if ( $module ) {
				$module->flush_module();
			}
		}
	}

	static function flush_by_module( $module_id ) {
		$module = Red_Module::get( $module_id );

		if ( $module ) {
			$module->flush_module();
		}
	}

	abstract public function get_id();

	abstract public function update( array $options );

	abstract protected function load( $options );
	abstract protected function flush_module();
}
