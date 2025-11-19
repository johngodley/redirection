<?php

class Nginx_Module extends Red_Module {
	const MODULE_ID = 3;

	/**
	 * Get module ID
	 *
	 * @return int
	 */
	public function get_id() {
		return self::MODULE_ID;
	}

	/**
	 * Get module name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Nginx';
	}

	/**
	 * Flush module cache
	 *
	 * @return void
	 */
	protected function flush_module() {
	}

	/**
	 * Update module configuration
	 *
	 * @param array<string, mixed> $data Update data.
	 * @return false
	 */
	public function update( array $data ) {
		return false;
	}
}
