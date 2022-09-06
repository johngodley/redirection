<?php

namespace Redirection\Module;

use Redirection\Group;

require_once __DIR__ . '/module-wordpress.php';
require_once __DIR__ . '/module-apache.php';
require_once __DIR__ . '/module-nginx.php';

/**
 * Base class for redirect module.
 */
abstract class Module {
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

	abstract public function get_name();

	/**
	 * Get a module based on the supplied ID, and loads it with appropriate options.
	 *
	 * @param integer $id Module ID.
	 * @return Module|false
	 */
	public static function get( $id ) {
		$id = intval( $id, 10 );
		$options = \Redirection\Plugin\Settings\red_get_options();

		if ( $id === Apache::MODULE_ID ) {
			return new Apache( isset( $options['modules'][ Apache::MODULE_ID ] ) ? $options['modules'][ Apache::MODULE_ID ] : array() );
		}

		if ( $id === WordPress::MODULE_ID ) {
			return new WordPress( isset( $options['modules'][ WordPress::MODULE_ID ] ) ? $options['modules'][ WordPress::MODULE_ID ] : array() );
		}

		if ( $id === Nginx::MODULE_ID ) {
			return new Nginx( isset( $options['modules'][ Nginx::MODULE_ID ] ) ? $options['modules'][ Nginx::MODULE_ID ] : array() );
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
		if ( $id === Apache::MODULE_ID || $id === WordPress::MODULE_ID || $id === Nginx::MODULE_ID ) {
			return true;
		}

		return false;
	}

	/**
	 * Return a module ID given the module name
	 *
	 * @param String $name Module name.
	 * @return integer|false
	 */
	public static function get_id_for_name( $name ) {
		$names = array(
			'wordpress' => WordPress::MODULE_ID,
			'apache'    => Apache::MODULE_ID,
			'nginx'     => Nginx::MODULE_ID,
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
		$group = Group\Group::get( $group_id );

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
	 * @return boolean
	 */
	protected function flush_module() {
		return true;
	}
}
