<?php

require_once dirname( dirname( __FILE__ ) ) . '/modules/wordpress.php';
require_once dirname( dirname( __FILE__ ) ) . '/modules/apache.php';
require_once dirname( dirname( __FILE__ ) ) . '/modules/nginx.php';

/**
 * Base class for redirect module.
 */
abstract class Red_Module {
	/**
	 * Constructor. Loads options
	 *
	 * @param array $options Any module options.
	 */
	public function __construct( $options = [] ) {
		if ( ! empty( $options ) ) {
			$this->load( $options );
		}
	}

	/**
	 * Get a module based on the supplied ID, and loads it with appropriate options.
	 *
	 * @param integer $id Module ID.
	 * @return Red_Module|false
	 */
	public static function get( $id ) {
		$id = intval( $id, 10 );
		$options = red_get_options();

		if ( $id === Apache_Module::MODULE_ID ) {
			return new Apache_Module( isset( $options['modules'][ Apache_Module::MODULE_ID ] ) ? $options['modules'][ Apache_Module::MODULE_ID ] : array() );
		}

		if ( $id === WordPress_Module::MODULE_ID ) {
			return new WordPress_Module( isset( $options['modules'][ WordPress_Module::MODULE_ID ] ) ? $options['modules'][ WordPress_Module::MODULE_ID ] : array() );
		}

		if ( $id === Nginx_Module::MODULE_ID ) {
			return new Nginx_Module( isset( $options['modules'][ Nginx_Module::MODULE_ID ] ) ? $options['modules'][ Nginx_Module::MODULE_ID ] : array() );
		}

		return false;
	}

	/**
	 * Check that an ID is valid.
	 *
	 * @param integer $id Module ID.
	 * @return boolean
	 */
	public static function is_valid_id( $id ) {
		if ( $id === Apache_Module::MODULE_ID || $id === WordPress_Module::MODULE_ID || $id === Nginx_Module::MODULE_ID ) {
			return true;
		}

		return false;
	}

	/**
	 * Return a module ID given the module name
	 *
	 * @param string $name Module name.
	 * @return integer|false
	 */
	public static function get_id_for_name( $name ) {
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

	/**
	 * Flush the module that a group belongs to
	 *
	 * @param integer $group_id Module group ID.
	 * @return void
	 */
	public static function flush( $group_id ) {
		$group = Red_Group::get( $group_id );

		if ( is_object( $group ) ) {
			$module = self::get( $group->get_module_id() );

			if ( $module ) {
				$module->flush_module();
			}
		}
	}

	/**
	 * Flush the module
	 *
	 * @param integer $module_id Module ID.
	 * @return void
	 */
	public static function flush_by_module( $module_id ) {
		$module = self::get( $module_id );

		if ( $module ) {
			$module->flush_module();
		}
	}

	/**
	 * Get the module ID
	 *
	 * @return integer
	 */
	abstract public function get_id();

	/**
	 * Update
	 *
	 * @param array $data Data.
	 * @return false
	 */
	public function update( array $data ) {
		return false;
	}

	/**
	 * Load
	 *
	 * @param array $options Options.
	 * @return void
	 */
	protected function load( $options ) {
	}

	/**
	 * Flush
	 *
	 * @return void
	 */
	protected function flush_module() {
	}
}
