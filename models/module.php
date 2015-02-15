<?php

include_once dirname( dirname( __FILE__ ) ).'/modules/wordpress.php';
include_once dirname( dirname( __FILE__ ) ).'/modules/apache.php';

abstract class Red_Module {
	public function __construct( $options ) {
		if ( is_array( $options ) )
			$this->load( $options );
	}

	static function get( $id ) {
		$id = intval( $id );
		$options = red_get_options();

		if ( $id === Apache_Module::MODULE_ID )
			return new Apache_Module( isset( $options['modules'][Apache_Module::MODULE_ID] ) ? $options['modules'][Apache_Module::MODULE_ID] : array() );
		else if ( $id === WordPress_Module::MODULE_ID )
			return new WordPress_Module( isset( $options['modules'][WordPress_Module::MODULE_ID] ) ? $options['modules'][WordPress_Module::MODULE_ID] : array() );

		return false;
	}

	public function get_total_redirects() {
		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items INNER JOIN {$wpdb->prefix}redirection_groups WHERE {$wpdb->prefix}redirection_groups.module_id=%d", $this->get_id() ) );
	}

	static public function is_valid_id( $id ) {
		if ( $id === Apache_Module::MODULE_ID || $id === WordPress_Module::MODULE_ID )
			return true;
		return false;
	}

	static function get_for_select() {
		$options = red_get_options();

		return array(
			WordPress_Module::MODULE_ID => Red_Module::get( WordPress_Module::MODULE_ID ),
			Apache_Module::MODULE_ID    => Red_Module::get( Apache_Module::MODULE_ID ),
		);
	}

	abstract public function update( $options );
	abstract public function can_edit_config();
	abstract public function get_id();
	abstract public function get_name();
	abstract public function render_config();
	abstract public function get_description();

	abstract protected function load( $options );
}
