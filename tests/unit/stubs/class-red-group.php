<?php

if ( ! class_exists( 'Red_Group' ) ) {
	class Red_Group {
		public static $groups = [];

		public static function reset() {
			self::$groups = [];
		}

		public static function get( $id ) {
			return self::$groups[ intval( $id, 10 ) ] ?? false;
		}

		public static function create( $name, $module_id, $enabled = true ) {
			$id = count( self::$groups ) + 1;
			$group = new Test_Import_Group_Item( $id );

			self::$groups[ $id ] = $group;
			return $group;
		}
	}
}
