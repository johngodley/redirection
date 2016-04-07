<?php

class Nginx_Module extends Red_Module {
	const MODULE_ID = 3;

	private $location  = '';
	private $canonical = '';

	public function get_id() {
		return self::MODULE_ID;
	}

	public function can_edit_config() {
		return false;
	}

	protected function load( $data ) {
		$mine = array( 'location', 'canonical' );

		foreach ( $mine as $key ) {
			if ( isset( $data[ $key ] ) )
				$this->$key = $data[ $key ];
		}
	}

	protected function flush_module() {
	}

	public function update( $data ) {
		return false;
	}

	public function render_config() {
	}

	public function get_config() {
		return array();
	}

	public function get_name() {
		return __( 'Nginx', 'redirection' );
	}

	public function get_description() {
		return __( 'For use with Nginx server. Requires manual configuration. The redirect happens without loading WordPress. No tracking of hits. This is an experimental module.', 'redirection' );
	}
}
