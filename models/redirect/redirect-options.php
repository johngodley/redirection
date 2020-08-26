<?php

class Red_Source_Options {
	private $log_exclude = false;

	public function __construct( $options = null ) {
		if ( $options ) {
			$this->set_options( $options );
		}
	}

	public function set_options( $options ) {
		if ( isset( $options['log_exclude'] ) && $options['log_exclude'] === true ) {
			$this->log_exclude = true;
		}
	}

	public function can_log() {
		$options = red_get_options();

		if ( isset( $options['expire_redirect'] ) && $options['expire_redirect'] !== -1 ) {
			return ! $this->log_exclude;
		}

		return false;
	}

	public function get_json() {
		return array_filter( [
			'log_exclude' => $this->log_exclude,
		] );
	}
}
