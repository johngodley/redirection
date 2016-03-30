<?php

class RE_Log {
	var $id;
	var $created;
	var $url;
	var $agent;
	var $referrer;
	var $ip;
	var $redirection_id;

	function __construct( $values ) {
		foreach ( $values AS $key => $value ) {
		 	$this->$key = $value;
		}

		$this->created = mysql2date( 'U', $this->created );
		$this->url     = stripslashes( $this->url );
	}

	static function get_by_id( $id ) {
		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}redirection_logs WHERE id=%d", $id ) );
		if ( $row )
			return new RE_Log( $row );
		return false;
	}

	static function create( $url, $target, $agent, $ip, $referrer, $extra = array()) {
		global $wpdb, $redirection;

		$insert = array(
			'url'     => urldecode( $url ),
			'created' => current_time( 'mysql' ),
			'ip'      => $ip,
		);

		if ( !empty( $agent ) )
			$insert['agent'] = $agent;

		if ( !empty( $referrer ) )
			$insert['referrer'] = $referrer;

		$insert['sent_to']        = $target;
		$insert['redirection_id'] = isset( $extra['redirect_id'] ) ? $extra['redirect_id'] : 0;
		$insert['group_id']       = isset( $extra['group_id'] ) ? $extra['group_id'] : 0;

		$insert = apply_filters( 'redirection_log_data', $insert );
		do_action( 'redirection_log', $insert );

		$wpdb->insert( $wpdb->prefix.'redirection_logs', $insert );
	}

	static function show_url( $url ) {
		return implode('&#8203;/', explode( '/', substr( $url, 0, 80 ) ) ).( strlen( $url ) > 80 ? '...' : '' );
	}

	static function delete( $id ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_logs WHERE id=%d", $id ) );
	}

	static function delete_for_id( $id ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_logs WHERE redirection_id=%d", $id ) );
	}

	static function delete_for_group( $id ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_logs WHERE group_id=%d", $id ) );
	}

	static function delete_all( $type = 'all', $id = 0 ) {
		global $wpdb;

		$where = array();
		if ( $type == 'module' )
			$where[] = $wpdb->prepare( 'module_id=%d', $id );
		elseif ( $type == 'group' )
			$where[] = $wpdb->prepare( 'group_id=%d AND redirection_id IS NOT NULL', $id );
		elseif ( $type == 'redirect' )
			$where[] = $wpdb->prepare( 'redirection_id=%d', $id );

		if ( isset( $_REQUEST['s'] ) )
			$where[] = $wpdb->prepare( 'url LIKE %s', '%'.like_escape( $_REQUEST['s'] ).'%' );

		$where_cond = "";
		if ( count( $where ) > 0 )
			$where_cond = " WHERE ".implode( ' AND ', $where );

		$wpdb->query( "DELETE FROM {$wpdb->prefix}redirection_logs ".$where_cond );
	}

	static function export_to_csv() {
		global $wpdb;

		$filename = 'redirection-log-'.date_i18n( get_option( 'date_format' ) ).'.csv';

		header( 'Content-Type: text/csv' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Content-Disposition: attachment; filename="'.$filename.'"' );

		$stdout = fopen( 'php://output', 'w' );

		fputcsv( $stdout, array( 'date', 'source', 'target', 'ip', 'referrer', 'agent' ) );

		$extra = '';
		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_logs";
		if ( isset( $_REQUEST['s'] ) )
			$extra = $wpdb->prepare( " WHERE url LIKE %s", '%'.like_escape( $_REQUEST['s'] ).'%' );

		$total_items = $wpdb->get_var( $sql.$extra );
		$exported = 0;

		while ( $exported < $total_items ) {
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}redirection_logs LIMIT %d,%d", $exported, 100 ) );
			$exported += count( $rows );

			foreach ( $rows AS $row ) {
				$csv = array(
					$row->created,
					$row->url,
					$row->sent_to,
					$row->ip,
					$row->referrer,
					$row->agent,
				);

				fputcsv( $stdout, $csv );
			}

			if ( count( $rows ) < 100 )
				break;
		}
	}
}

class RE_404 {
	var $id;
	var $created;
	var $url;
	var $agent;
	var $referrer;
	var $ip;

	function __construct( $values ) {
		foreach ( $values AS $key => $value ) {
		 	$this->$key = $value;
		 }

		$this->created = mysql2date ('U', $this->created);
	}

	static function get_by_id( $id ) {
		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}redirection_404 WHERE id=%d", $id ) );
		if ( $row )
			return new RE_404( $row );
		return false;
	}

	static function create( $url, $agent, $ip, $referrer ) {
		global $wpdb, $redirection;

		$insert = array(
			'url'     => urldecode( $url ),
			'created' => current_time( 'mysql' ),
			'ip'      => ip2long( $ip ),
		);

		if ( !empty( $agent ) )
			$insert['agent'] = $agent;

		if ( !empty( $referrer ) )
			$insert['referrer'] = $referrer;

		$wpdb->insert( $wpdb->prefix.'redirection_404', $insert );
	}

	static function delete( $id ) {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_404 WHERE id=%d", $id ) );
	}

	static function delete_all() {
		global $wpdb;

		$where = array();
		if ( isset( $_REQUEST['s'] ) )
			$where[] = $wpdb->prepare( 'url LIKE %s', '%'.like_escape( $_REQUEST['s'] ).'%' );

		$where_cond = "";
		if ( count( $where ) > 0 )
			$where_cond = " WHERE ".implode( ' AND ', $where );

		$wpdb->query( "DELETE FROM {$wpdb->prefix}redirection_404 ".$where_cond );
	}

	static function export_to_csv() {
		global $wpdb;

		$filename = 'redirection-log-'.date_i18n( get_option( 'date_format' ) ).'.csv';

		header( 'Content-Type: text/csv' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Content-Disposition: attachment; filename="'.$filename.'"' );

		$stdout = fopen( 'php://output', 'w' );

		fputcsv( $stdout, array( 'date', 'source', 'ip', 'referrer' ) );

		$extra = '';
		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_404";
		if ( isset( $_REQUEST['s'] ) )
			$extra = $wpdb->prepare( " WHERE url LIKE %s", '%'.like_escape( $_REQUEST['s'] ).'%' );

		$total_items = $wpdb->get_var( $sql.$extra );
		$exported = 0;

		while ( $exported < $total_items ) {
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}redirection_404 LIMIT %d,%d", $exported, 100 ) );
			$exported += count( $rows );

			foreach ( $rows AS $row ) {
				$csv = array(
					$row->created,
					$row->url,
					long2ip( $row->ip ),
					$row->referrer,
				);

				fputcsv( $stdout, $csv );
			}

			if ( count( $rows ) < 100 )
				break;
		}
	}
}
