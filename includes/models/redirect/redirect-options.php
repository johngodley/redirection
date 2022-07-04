<?php

/**
 * Options for a redirect source URL
 */
class Red_Source_Options {
	/**
	 * Exclude this from logging.
	 *
	 * @var boolean
	 */
	private $log_exclude = false;

	/**
	 * Constructor
	 *
	 * @param array|null $options Options.
	 */
	public function __construct( $options = null ) {
		if ( $options ) {
			$this->set_options( $options );
		}
	}

	/**
	 * Set options
	 *
	 * @param array $options Options.
	 * @return void
	 */
	public function set_options( $options ) {
		if ( isset( $options['log_exclude'] ) && $options['log_exclude'] === true ) {
			$this->log_exclude = true;
		}
	}

	/**
	 * Can this source be logged?
	 *
	 * @return boolean
	 */
	public function can_log() {
		$options = red_get_options();

		if ( isset( $options['expire_redirect'] ) && $options['expire_redirect'] !== -1 ) {
			return ! $this->log_exclude;
		}

		return false;
	}

	/**
	 * Get options as JSON
	 *
	 * @return array
	 */
	public function get_json() {
		return array_filter( [
			'log_exclude' => $this->log_exclude,
		] );
	}
}
