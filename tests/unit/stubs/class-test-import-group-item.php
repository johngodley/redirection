<?php

class Test_Import_Group_Item {
	private $id;
	private $enabled;

	public function __construct( $id, $enabled = true ) {
		$this->id = intval( $id, 10 );
		$this->enabled = $enabled ? true : false;
	}

	public function get_id() {
		return $this->id;
	}

	public function is_enabled() {
		return $this->enabled;
	}
}
