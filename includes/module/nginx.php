<?php

class Nginx_Module extends Red_Module {
	const MODULE_ID = 3;

	private $location  = '';

	public function get_id() {
		return self::MODULE_ID;
	}

	public function get_name() {
		return 'Nginx';
	}

	protected function load( $data ) {
		$mine = array( 'location' );

		foreach ( $mine as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$this->$key = $data[ $key ];
			}
		}
	}

	protected function flush_module() {
	}

	public function update( array $data ) {
		return false;
	}
}
