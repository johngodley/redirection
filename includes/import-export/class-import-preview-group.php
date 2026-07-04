<?php

namespace Redirection\ImportExport;

class ImportPreviewGroup {
	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var int
	 */
	private $module_id;

	/**
	 * @var bool
	 */
	private $enabled;

	public function __construct( int $group_id, string $name, int $module_id, bool $enabled ) {
		$this->id = $group_id;
		$this->name = $name;
		$this->module_id = $module_id;
		$this->enabled = $enabled;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function get_module_id(): int {
		return $this->module_id;
	}

	public function is_enabled(): bool {
		return $this->enabled;
	}
}
