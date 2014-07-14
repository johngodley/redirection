<?php
/**
 * Redirection
 *
 * @package Redirection
 * @author John Godley
 * @copyright Copyright( C) John Godley
 **/

/*
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages( including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort( including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================ */
class Red_Item {
	var $id          = null;
	var $created;
	var $referrer;
	var $url         = null;
	var $regex       = false;
	var $action_data = null;
	var $action_code = 0;

	var $last_access   = null;
	var $last_count    = 0;

	var $tracking      = true;

	function Red_Item( $values, $type = '', $match = '' )	{
		if ( is_object( $values ) ) {
			foreach ( $values AS $key => $value ) {
			 	$this->$key = $value;
			}

			if ( $this->match_type ) {
				$this->match              = Red_Match::create( $this->match_type, $this->action_data);
				$this->match->id          = $this->id;
				$this->match->action_code = $this->action_code;
			}

			if ( $this->action_type )	{
				$this->action        = Red_Action::create( $this->action_type, $this->action_code);
				$this->match->action = $this->action;
			}
			else
				$this->action = Red_Action::create( 'nothing', 0 );

			if ( $this->last_access == '0000-00-00 00:00:00' )
				$this->last_access = 0;
			else
				$this->last_access = mysql2date( 'U', $this->last_access);
		}
		else {
			$this->url   = $values;
			$this->type  = $type;
			$this->match = $match;
		}
	}

	static function get_all_for_module( $module ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT @redirection_items.*,@redirection_groups.tracking FROM @redirection_items INNER JOIN @redirection_groups ON @redirection_groups.id=@redirection_items.group_id AND @redirection_groups.status='enabled' AND @redirection_groups.module_id=%d WHERE @redirection_items.status='enabled' ORDER BY @redirection_groups.position,@redirection_items.position", $module );
		$sql = str_replace( '@', $wpdb->prefix, $sql );

		$rows  = $wpdb->get_results( $sql );
		$items = array();
		if ( count( $rows) > 0 ) {
			foreach ( $rows AS $row ) {
				$items[] = new Red_Item( $row );
			}
		}

		return $items;
	}

	static function exists( $url ) {
		global $wpdb;

		if ( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$wpdb->prefix}redirection_items WHERE url=%s", $url ) ) > 0 )
			return true;
		return false;
	}

	static function get_for_url( $url, $type )	{
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT @redirection_items.*,@redirection_groups.tracking,@redirection_groups.position AS group_pos,@redirection_modules.id AS module_id FROM @redirection_items INNER JOIN @redirection_groups ON @redirection_groups.id=@redirection_items.group_id AND @redirection_groups.status='enabled' INNER JOIN @redirection_modules ON @redirection_modules.id=@redirection_groups.module_id AND @redirection_modules.type=%s WHERE( @redirection_items.regex=1 OR @redirection_items.url=%s)", $type, $url );
		$sql = str_replace( '@', $wpdb->prefix, $sql);

		$rows = $wpdb->get_results( $sql ) ;
		$items = array();
		if ( count( $rows ) > 0 ) {
			foreach ( $rows AS $row ) {
				$items[$row->group_pos * 1000 + $row->position] = new Red_Item( $row );
			}
		}

		// Sort it in PHP
		ksort( $items );
		$items = array_values( $items );
		return $items;
	}

	static function get_by_module( $module ) {
		global $wpdb;

		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->prefix}redirection_items INNER JOIN {$wpdb->prefix}redirection_groups ON {$wpdb->prefix}redirection_groups.id={$wpdb->prefix}redirection_items.group_id";
		$sql .= $wpdb->prepare( " WHERE {$wpdb->prefix}redirection_groups.module_id=%d", $module );

		$rows = $wpdb->get_results( $sql );
		$items = array();
		if ( count( $rows) > 0) {
			foreach( $rows AS $row)
				$items[] = new Red_Item( $row);
		}

		return $items;
	}

	/**
	 * Get redirection items in a group
	 */
	static function get_by_group( $group, &$pager ) {
		global $wpdb;

		$sql = $wpdb->prepare( "FROM {$wpdb->prefix}redirection_items WHERE group_id=%d", $group );

		if ( $pager->search )
			$sql .= $wpdb->prepare( ' AND url LIKE %s', '%'.like_escape( $pager->search ).'%' );

		$pager->set_total( $wpdb->get_var( "SELECT COUNT(*) ".$sql ) );
		$rows = $wpdb->get_results( "SELECT * ".$sql.' ORDER BY position'.$pager->to_limits() );

		$items = array();
		if ( count( $rows ) > 0 ) {
			foreach ( $rows AS $row ) {
				$items[] = new Red_Item( $row );
			}
		}

		return $items;
	}

	static function get_by_id( $id ) {
		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}redirection_items WHERE id=%d", $id ) );
		if ( $row )
			return new Red_Item( $row );
		return false;
	}

	static function auto_generate() {
		global $redirection;

		$options = $redirection->get_options();
		$id = time();

		$url = $options['auto_target'];
		$url = str_replace( '$dec$', $id, $url );
		$url = str_replace( '$hex$', sprintf( '%x', $id), $url );
		return $url;
	}

	static function create( $details ) {
		global $wpdb;

		// Auto generate URLs
		if ( $details['source'] == '' )
			$details['source'] = self::auto_generate();

		if ( $details['target'] == '' )
			$details['target'] = self::auto_generate();

		// Make sure we don't redirect to ourself
		if ( $details['source'] == $details['target'] )
			return new WP_Error( 'redirect-add', __( 'Source and target URL must be different', 'redirection' ) );

		$matcher  = Red_Match::create( $details['match'] );
		$group_id = intval( $details['group'] );
		$group    = Red_Group::get( $group_id );

		if ( $group_id <= 0 || !$group )
			return new WP_Error( 'redirect-add', __( 'Invalid group when creating redirect', 'redirection' ) );

		if ( !$matcher )
			return new WP_Error( 'redirect-add', __( 'Invalid source URL when creating redirect for given match type', 'redirection' ) );

		$regex    = ( isset( $details['regex']) && $details['regex'] != false) ? 1 : 0;
		$position = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items WHERE group_id=%d", $group_id ) );

		$action = $details['red_action'];
		$action_code = 0;
		if ( $action == 'url' || $action == 'random' )
			$action_code = 301;
		elseif ( $action == 'error' )
			$action_code = 404;

		if ( isset( $details['action_code'] ) )
			$action_code = intval( $details['action_code'] );

		$data = array(
			'url'         => self::sanitize_url( $details['source'], $regex),
			'action_type' => $details['red_action'],
			'regex'       => $regex,
			'position'    => $position,
			'match_type'  => $details['match'],
			'action_data' => $matcher->data( $details ),
			'action_code' => $action_code,
			'last_access' => '0000-00-00 00:00:00',
			'group_id'    => $group_id
		);

		$data = apply_filters( 'redirection_create_redirect', $data );

		$wpdb->delete( $wpdb->prefix.'redirection_items', array( 'url' => $data['action_data'], 'action_type' => $data['action_type'], 'action_data' => $data['url'] ) );

		if ( $wpdb->insert( $wpdb->prefix.'redirection_items', $data ) ) {
			Red_Module::flush( $group->module_id );

			return self::get_by_id( $wpdb->insert_id );
		}

		return new WP_Error( 'redirect-add', __( 'Unable to add new redirect - delete Redirection from the options page and re-install' ) );
	}

	static function delete_by_group( $group ) {
		global $wpdb;

		RE_Log::delete_for_group( $group);

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_items WHERE group_id=%d", $group ) );

		$group = Red_Group::get( $group_id );
		Red_Module::flush( $group->module_id );
	}

	static function delete( $id ) {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_items WHERE id=%d", $id ) );

		RE_Log::delete_for_id( $id );

		// Reorder all elements
		$rows = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}redirection_items ORDER BY position" );
		if ( count( $rows) > 0 ) {
			foreach ( $rows AS $pos => $row ) {
				$wpdb->update( $wpdb->prefix.'redirection_items', array( 'position' => $pos ), array( 'id' => $row->id ) );
			}
		}
	}


	static function sanitize_url( $url, $regex )	{
		// Make sure that the old URL is relative
		$url = preg_replace( '@^https?://(.*?)/@', '/', $url );
		$url = preg_replace( '@^https?://(.*?)$@', '/', $url );

		if ( substr( $url, 0, 1) != '/' && $regex == false )
			$url = '/'.$url;
		return $url;
	}


	function update( $details ) {
		if ( strlen( $details['old'] ) > 0 ) {
			global $wpdb;

			$this->regex = isset( $details['regex'] ) ? 1 : 0;
			$this->url   = self::sanitize_url( $details['old'], $this->regex );
			$this->title = $details['title'];

			$data  = $this->match->data( $details );

			$this->action_code = 0;
			if ( isset( $details['action_code'] ) )
				$this->action_code = intval( $details['action_code'] );

			if ( isset( $details['group_id'] ) )
				$this->group_id = intval( $details['group_id'] );

			// Save this
			global $wpdb;
			$wpdb->update( $wpdb->prefix.'redirection_items', array( 'url' => $this->url, 'regex' => $this->regex, 'action_code' => $this->action_code, 'action_data' => $data, 'group_id' => $this->group_id, 'title' => $this->title ), array( 'id' => $this->id ) );

			$group = Red_Group::get( $this->group_id );
			if ( $group )
				Red_Module::flush( $group->module_id );
		}
	}

	static function save_order( $items, $start ) {
		global $wpdb;

		foreach ( $items AS $pos => $id ) {
			$wpdb->update( $wpdb->prefix.'redirection_items', array( 'position' => $pos + $start ), array( 'id' => $id ) );
		}

		$item  = self::get_by_id( $id );
		$group = Red_Group::get( $item->group_id );
		if ( $group )
			Red_Module::flush( $group->module_id );
	}

	function matches( $url ) {
		$this->url = str_replace( ' ', '%20', $this->url );
		$matches   = false;

		// Check if we match the URL
		if ( ( $this->regex == false && ( $this->url == $url || $this->url == rtrim( $url, '/' ) || $this->url == urldecode( $url ) ) ) ||( $this->regex == true && @preg_match( '@'.str_replace( '@', '\\@', $this->url).'@', $url, $matches) > 0) ||( $this->regex == true && @preg_match( '@'.str_replace( '@', '\\@', $this->url).'@', urldecode( $url ), $matches) > 0) ) {
			// Check if our match wants this URL
			$target = $this->match->get_target( $url, $this->url, $this->regex);

			if ( $target ) {
				$target = $this->replaceSpecialTags( $target );

				$this->visit( $url, $target );

				if ( $this->status == 'enabled' )
					return $this->action->process_before( $this->action_code, $target );
			}
		}

		return false;
	}

	function replaceSpecialTags( $target ) {
		if ( is_numeric( $target ) )
			$target = get_permalink( $target );
		else {
			$user = wp_get_current_user();
			if ( !empty( $user ) ) {
				$target = str_replace( '%userid%', $user->ID, $target );
				$target = str_replace( '%userlogin%', isset( $user->user_login ) ? $user->user_login : '', $target );
				$target = str_replace( '%userurl%', isset( $user->user_url ) ? $user->user_url : '', $target );
			}
		}

		return $target;
	}

	function visit( $url, $target ) {
		if ( $this->tracking && $this->id ) {
			global $wpdb, $redirection;

			// Update the counters
			$count = $this->last_count + 1;
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}redirection_items SET last_count=%d, last_access=NOW() WHERE id=%d", $count, $this->id ) );

			if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
			  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			elseif ( isset( $_SERVER['REMOTE_ADDR'] ) )
			  $ip = $_SERVER['REMOTE_ADDR'];

			$options = $redirection->get_options();
			if ( isset( $options['expire_redirect'] ) && $options['expire_redirect'] >= 0 )
				$log = RE_Log::create( $url, $target, $_SERVER['HTTP_USER_AGENT'], $ip, isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '', array( 'redirect_id' => $this->id, 'module_id' => $this->module_id, 'group_id' => $this->group_id) );
		}
	}

	function reset() {
		global $wpdb;

		$this->last_count  = 0;
		$this->last_access = '0000-00-00 00:00:00';

		$wpdb->update( $wpdb->prefix.'redirection_items', array( 'last_count' => 0, 'last_access' => $this->last_access ), array( 'id' => $this->id ) );

		RE_Log::delete_for_id( $this->id );
	}

	function show_url( $url ) {
		return implode( '&#8203;/', explode( '/', $url ) );
	}

	function move_to( $group ) {
		global $wpdb;

		$wpdb->update( $wpdb->prefix.'redirection_items', array( 'group_id' => $group ), array( 'id' => $this->id ) );
	}

	function toggle_status() {
		global $wpdb;

		$this->status = ( $this->status == 'enabled' ) ? 'disabled' : 'enabled';
		$wpdb->update( $wpdb->prefix.'redirection_items', array( 'status' => $this->status ), array( 'id' => $this->id ) );
	}

	static function actions( $action = '' ) {
		$actions = array(
			'url'     => __( 'Redirect to URL', 'redirection' ),
			'random'  => __( 'Redirect to random post', 'redirection' ),
			'pass'    => __( 'Pass-through', 'redirection' ),
			'error'   => __( 'Error (404)', 'redirection' ),
			'nothing' => __( 'Do nothing', 'redirection' ),
		);

		if ( $action )
			return $actions[$action];
		return $actions;
	}

	function match_name() {
		return $this->match->match_name();
	}

	function type()	{
		if ( ( $this->action_type == 'url' || $this->action_type == 'error' || $this->action_type == 'random' ) && $this->action_code > 0 )
			return $this->action_code;
		else if ( $this->action_type == 'pass' )
			return 'pass';
		return '&mdash;';
	}
}
