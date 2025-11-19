<?php

/**
 * Options for a redirect source URL
 *
 * @phpstan-type SourceOptionsJson array{
 *     log_exclude?: bool
 * }
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
	 * @param SourceOptionsJson|null $options Options.
	 */
	public function __construct( $options = null ) {
		if ( $options !== null ) {
			$this->set_options( $options );
		}
	}

	/**
	 * Set options
	 *
	 * @param SourceOptionsJson $options Options.
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
		$options = Red_Options::get();

		if ( $options['expire_redirect'] !== -1 ) {
			return ! $this->log_exclude;
		}

		return false;
	}

	/**
	 * Get options as JSON
	 *
	 * @return SourceOptionsJson
	 */
	public function get_json() {
		return array_filter(
			[
				'log_exclude' => $this->log_exclude,
			]
		);
	}
}
