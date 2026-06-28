<?php

namespace Redirection\ImportExport;

/**
 * Repository wrapper for modules.
 */
class ModuleRepository {
	/**
	 * @param int $module_id
	 * @return \Red_Module|false
	 */
	public function get( $module_id ) {
		return \Red_Module::get( intval( $module_id, 10 ) );
	}

	/**
	 * @param string $name
	 * @return int|false
	 */
	public function get_id_for_name( $name ) {
		return \Red_Module::get_id_for_name( $name );
	}
}
