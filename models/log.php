<?php
/**
 * Redirection
 *
 * @package Redirection
 * @author John Godley
 * @copyright Copyright (C) John Godley
 **/

/*
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages (including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================ */

class RE_Log {
	var $id;
	var $created;
	var $url;
	var $agent;
	var $referrer;
	var $ip;
	var $redirection_id;

	function RE_Log ($values) {
		foreach ($values AS $key => $value)
		 	$this->$key = $value;

		$this->created = mysql2date ('U', $this->created);
		$this->url     = stripslashes ($this->url);
	}

	static function get_by_id( $id ) {
		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}redirection_logs WHERE id=%d", $id ) );
		if ( $row )
			return new RE_Log ($row);
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
		$insert['module_id']      = isset( $extra['module_id'] ) ? $extra['module_id'] : 0;
		$insert['group_id']       = isset( $extra['group_id'] ) ? $extra['group_id'] : 0;

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

	static function delete_for_module( $id ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_logs WHERE module_id=%d", $id ) );
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

		if ( isset( $_POST['s'] ) )
			$where[] = $wpdb->prepare( 'url LIKE %s', '%'.$_POST['s'].'%' );

		$where_cond = "";
		if ( count( $where ) > 0 )
			$where_cond = " WHERE ".implode( ' AND ', $where );

		$wpdb->query( "DELETE FROM {$wpdb->prefix}redirection_logs ".$where_cond );
	}
}

class RE_404 {
	var $id;
	var $created;
	var $url;
	var $agent;
	var $referrer;
	var $ip;

	function RE_404( $values ) {
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
		if ( isset( $_POST['s'] ) )
			$where[] = $wpdb->prepare( 'url LIKE %s', '%'.$_POST['s'].'%' );

		$where_cond = "";
		if ( count( $where ) > 0 )
			$where_cond = " WHERE ".implode( ' AND ', $where );

		$wpdb->query( "DELETE FROM {$wpdb->prefix}redirection_404 ".$where_cond );
	}
}

