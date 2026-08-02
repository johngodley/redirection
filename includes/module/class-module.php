<?php

namespace Redirection\Module;

use Redirection\Group\Group;
use Redirection\Settings\Settings;

/**
 * Base class for redirect module.
 *
 * @phpstan-type WordPressModuleOptions array{}
 * @phpstan-type ApacheModuleOptions array{
 *     location: string
 * }
 * @phpstan-type NginxModuleOptions array{
 *     location: string
 * }
 * @phpstan-type RedModuleOptions WordPressModuleOptions|ApacheModuleOptions|NginxModuleOptions
 */
abstract class Module {
	/**
	 * Constructor. Loads options
	 *
	 * @param array $options Any module options.
	 * @phpstan-param RedModuleOptions $options
	 */
	public function __construct( array $options = [] ) {
		if ( count( $options ) > 0 ) {
			$this->load( $options );
		}
	}

	/**
	 * Get a module based on the supplied ID, and loads it with appropriate options.
	 *
	 * @param integer $id Module ID.
	 * @return Module|false
	 */
	public static function get( $id ) {
		$id = intval( $id, 10 );
		$options = Settings::get();

		if ( $id === Apache::MODULE_ID ) {
			return new Apache( $options['modules'][ Apache::MODULE_ID ] ?? [] );
		}

		if ( $id === Plugin::MODULE_ID ) {
			return new Plugin( $options['modules'][ Plugin::MODULE_ID ] ?? [] );
		}

		if ( $id === Nginx::MODULE_ID ) {
			return new Nginx( $options['modules'][ Nginx::MODULE_ID ] ?? [] );
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
		if ( $id === Apache::MODULE_ID || $id === Plugin::MODULE_ID || $id === Nginx::MODULE_ID ) {
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
		$names = [
			'wordpress' => Plugin::MODULE_ID,
			'apache'    => Apache::MODULE_ID,
			'nginx'     => Nginx::MODULE_ID,
		];

		return $names[ $name ] ?? false;
	}

	/**
	 * Flush the module that a group belongs to
	 *
	 * @param integer $group_id Module group ID.
	 * @return void
	 */
	public static function flush( $group_id ) {
		$group = Group::get( $group_id );

		if ( is_object( $group ) ) {
			$module = self::get( $group->get_module_id() );

			if ( $module !== false ) {
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

		if ( $module !== false ) {
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
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * Update
	 *
	 * @param array $data Data.
	 * @phpstan-param RedModuleOptions $data
	 * @return array<string, string>|false
	 */
	public function update( array $data ) {
		return false;
	}

	/**
	 * Load
	 *
	 * @param array $options Options.
	 * @phpstan-param RedModuleOptions $options
	 * @return void
	 */
	protected function load( array $options ) {
	}

	/**
	 * Flush
	 *
	 * @return void
	 */
	protected function flush_module() {
	}
}
