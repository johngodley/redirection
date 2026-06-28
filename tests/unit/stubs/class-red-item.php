<?php

if ( ! class_exists( 'Red_Item' ) ) {
	class Red_Item {
		public static $items = [];
		public static $create_calls = [];
		public static $get_by_id_calls = 0;

		private $id;
		private $data;

		public function __construct( $id, array $data = [] ) {
			$this->id = intval( $id, 10 );
			$this->data = $data;
		}

		public static function reset() {
			self::$items = [];
			self::$create_calls = [];
			self::$get_by_id_calls = 0;
		}

		public static function add_existing( $id, array $data ) {
			$item = new self( $id, $data );

			self::$items[ intval( $id, 10 ) ] = $item;
			return $item;
		}

		public static function get_by_id( $id ) {
			self::$get_by_id_calls++;
			return self::$items[ intval( $id, 10 ) ] ?? false;
		}

		public static function create( array $redirect ) {
			self::$create_calls[] = $redirect;
			return new self( count( self::$create_calls ), $redirect );
		}

		public function to_json() {
			return $this->data;
		}

		public function get_group_id() {
			return isset( $this->data['group_id'] ) ? intval( $this->data['group_id'], 10 ) : 0;
		}

		public function update( array $redirect ) {
			$this->data = $redirect;
			return true;
		}

		public function disable() {
			return true;
		}

		public function enable() {
			return true;
		}
	}
}
