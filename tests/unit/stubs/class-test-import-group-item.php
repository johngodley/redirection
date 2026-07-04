<?php

class Test_Import_Group_Item {
	private $id;
	private $name;
	private $enabled;

	public function __construct( $id, $enabled = true, $name = 'Group' ) {
		$this->id = intval( $id, 10 );
		$this->name = (string) $name;
		$this->enabled = $enabled ? true : false;
	}

	public function get_id() {
		return $this->id;
	}

	public function get_name() {
		return $this->name;
	}

	public function is_enabled() {
		return $this->enabled;
	}
}
