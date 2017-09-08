<?php

include_once dirname( REDIRECTION_FILE ).'/models/database.php';

class Red_Fixer {
	public function get_status() {
		global $wpdb;

		$db = new RE_Database();
		$groups = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups" ), 10 );

		$result = array(
			array_merge( array( 'id' => 'db', 'name' => __( 'Database tables', 'redirection' ) ), $db->get_status() ),
			array(
				'name' => __( 'Valid groups', 'redirection' ),
				'id' => 'groups',
				'message' => $groups === 0 ? __( 'No valid groups, so you will not be able to create any redirects', 'redirection' ) : __( 'Valid groups detected', 'redirection' ),
				'status' => $groups === 0 ? 'problem' : 'good',
			),
		);

		return $result;
	}

	public function fix( $status ) {
		foreach ( $status as $item ) {
			if ( $item['status'] !== 'good' ) {
				$fixer = 'fix_'.$item['id'];
				$result = $this->$fixer();

				if ( is_wp_error( $result ) ) {
					return $result;
				}
			}
		}

		return $this->get_status();
	}

	private function fix_db() {
		$db = new RE_Database();

		try {
			$db->create_tables();
		} catch ( Exception $e ) {
			return new WP_Error( __( 'Failed to fix database tables', 'redirection' ) );
		}

		return true;
	}

	private function fix_groups() {
		if ( Red_Group::create( 'new group', 1 ) === false ) {
			return new WP_Error( __( 'Unable to create group', 'redirection' ) );
		}

		return true;
	}
}
