<?php

namespace Redirection\Database;

class InvalidUpgrader extends Upgrader {
	/**
	 * @var string
	 */
	private $message;

	/**
	 * @param string $class Missing or invalid upgrader class name.
	 */
	public function __construct( string $class ) {
		$this->message = sprintf( 'Database upgrader class "%s" could not be loaded', $class );
	}

	/**
	 * @return array<string, string>
	 */
	public function get_stages() {
		return [
			'invalid_upgrader' => $this->message,
		];
	}

	/**
	 * @param Status $status
	 * @return void
	 */
	public function perform_stage( Status $status ): void {
		$status->set_error( $this->message );
	}

	/**
	 * @param string $stage
	 * @return list<string>
	 */
	public function get_queries_for_stage( string $stage ): array {
		return [];
	}
}
