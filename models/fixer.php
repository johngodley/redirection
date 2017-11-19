<?php

include_once dirname( REDIRECTION_FILE ).'/models/database.php';

class Red_Fixer {
	public function get_status() {
		global $wpdb;

		$options = red_get_options();

		$db = new RE_Database();
		$groups = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups" ), 10 );
		$bad_group = $this->get_missing();
		$monitor_group = $options['monitor_post'];
		$valid_monitor = Red_Group::get( $monitor_group ) || $monitor_group === 0;

		$result = array(
			array_merge( array( 'id' => 'db', 'name' => __( 'Database tables', 'redirection' ) ), $db->get_status() ),
			array(
				'name' => __( 'Valid groups', 'redirection' ),
				'id' => 'groups',
				'message' => $groups === 0 ? __( 'No valid groups, so you will not be able to create any redirects', 'redirection' ) : __( 'Valid groups detected', 'redirection' ),
				'status' => $groups === 0 ? 'problem' : 'good',
			),
			array(
				'name' => __( 'Valid redirect group', 'redirection' ),
				'id' => 'redirect_groups',
				'message' => count( $bad_group ) > 0 ? __( 'Redirects with invalid groups detected', 'redirection' ) : __( 'All redirects have a valid group', 'redirection' ),
				'status' => count( $bad_group ) > 0 ? 'problem' : 'good',
			),
			array(
				'name' => __( 'Post monitor group', 'redirection' ),
				'id' => 'monitor',
				'message' => $valid_monitor === false ? __( 'Post monitor group is invalid', 'redirection' ) : __( 'Post monitor group is valid' ),
				'status' => $valid_monitor === false ? 'problem' : 'good',
			)
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

	private function get_missing() {
		global $wpdb;

		return $wpdb->get_results( "SELECT {$wpdb->prefix}redirection_items.id FROM {$wpdb->prefix}redirection_items LEFT JOIN {$wpdb->prefix}redirection_groups ON {$wpdb->prefix}redirection_items.group_id = {$wpdb->prefix}redirection_groups.id WHERE {$wpdb->prefix}redirection_groups.id IS NULL" );
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

	private function fix_redirect_groups() {
		global $wpdb;

		$missing = $this->get_missing();

		foreach ( $missing as $row ) {
			$wpdb->update( $wpdb->prefix.'redirection_items', array( 'group_id' => $this->get_valid_group() ), array( 'id' => $row->id ) );
		}
	}

	private function fix_monitor() {
		red_set_options( array( 'monitor_post' => $this->get_valid_group() ) );
	}

	private function get_valid_group() {
		$groups = Red_Group::get_all();

		return $groups[ 0 ]['id'];
	}
}
