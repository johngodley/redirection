<?php

namespace Redirection\Database;

class Upgrade {
	/**
	 * @var string
	 */
	private $version;

	/**
	 * @var class-string<Upgrader>
	 */
	private $class;

	/**
	 * @param string $version
	 * @param class-string<Upgrader> $class
	 */
	public function __construct( string $version, string $class ) {
		$this->version = $version;
		$this->class = $class;
	}

	public function get_version(): string {
		return $this->version;
	}

	/**
	 * @return class-string<Upgrader>
	 */
	public function get_class(): string {
		return $this->class;
	}
}
