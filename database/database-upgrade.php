<?php

class Red_Database_Upgrade {
	/**
	 * @var string
	 */
	private $version;

	/**
	 * @var string
	 */
	private $file;

	/**
	 * @var class-string<Red_Database_Upgrader>
	 */
	private $class;

	/**
	 * @param string $version
	 * @param string $file
	 * @param class-string<Red_Database_Upgrader> $class
	 */
	public function __construct( string $version, string $file, string $class ) {
		$this->version = $version;
		$this->file = $file;
		$this->class = $class;
	}

	public function get_version(): string {
		return $this->version;
	}

	public function get_file(): string {
		return $this->file;
	}

	/**
	 * @return class-string<Red_Database_Upgrader>
	 */
	public function get_class(): string {
		return $this->class;
	}
}
