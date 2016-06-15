<?php

class Red_Item {
	private $id          = null;
	private $created;
	private $referrer;
	private $url         = null;
	private $regex       = false;
	private $action_data = null;
	private $action_code = 0;
	private $action_type;
	private $match_type;
	private $title;
	private $last_access = null;
	private $last_count  = 0;
	private $tracking    = true;
	private $status;
	private $position;
	private $group_id;

	function __construct( $values, $type = '', $match = '' ) {
		if ( is_object( $values ) ) {
			foreach ( $values as $key => $value ) {
			 	$this->$key = $value;
			}

			if ( $this->match_type === '' ) {
				$this->match_type = 'url';
			}

			$this->regex = (bool)$this->regex;
			$this->match              = Red_Match::create( $this->match_type, $this->action_data );
			$this->match->id          = $this->id;
			$this->match->action_code = $this->action_code;

			$action = false;

			if ( $this->action_type ) {
				$action = Red_Action::create( $this->action_type, $this->action_code );
			}

			if ( $action ) {
				$this->action = $action;
				$this->match->action = $this->action;
			}
			else
				$this->action = Red_Action::create( 'nothing', 0 );

			if ( $this->last_access === '0000-00-00 00:00:00' )
				$this->last_access = 0;
			else
				$this->last_access = mysql2date( 'U', $this->last_access );
		}
		else {
			$this->url   = $values;
			$this->type  = $type;
			$this->match = $match;
		}
	}

	static function get_all_for_module( $module ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT {$wpdb->prefix}redirection_items.*,{$wpdb->prefix}redirection_groups.tracking FROM {$wpdb->prefix}redirection_items INNER JOIN {$wpdb->prefix}redirection_groups ON {$wpdb->prefix}redirection_groups.id={$wpdb->prefix}redirection_items.group_id AND {$wpdb->prefix}redirection_groups.status='enabled' AND {$wpdb->prefix}redirection_groups.module_id=%d WHERE {$wpdb->prefix}redirection_items.status='enabled' ORDER BY {$wpdb->prefix}redirection_groups.position,{$wpdb->prefix}redirection_items.position", $module );

		$rows  = $wpdb->get_results( $sql );
		$items = array();
		if ( count( $rows ) > 0 ) {
			foreach ( $rows as $row ) {
				$items[] = new Red_Item( $row );
			}
		}

		return $items;
	}

	static function get_for_url( $url, $type ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT {$wpdb->prefix}redirection_items.*,{$wpdb->prefix}redirection_groups.position AS group_pos FROM {$wpdb->prefix}redirection_items INNER JOIN {$wpdb->prefix}redirection_groups ON {$wpdb->prefix}redirection_groups.id={$wpdb->prefix}redirection_items.group_id AND {$wpdb->prefix}redirection_groups.status='enabled' AND {$wpdb->prefix}redirection_groups.module_id=%d WHERE ({$wpdb->prefix}redirection_items.regex=1 OR {$wpdb->prefix}redirection_items.url=%s)", WordPress_Module::MODULE_ID, $url );

		$rows = $wpdb->get_results( $sql );
		$items = array();
		if ( count( $rows ) > 0 ) {
			foreach ( $rows as $row ) {
				$items[] = array( 'position' => ( $row->group_pos * 1000 ) + $row->position, 'item' => new Red_Item( $row ) );
			}
		}

		usort( $items, array( 'Red_Item', 'sort_urls' ) );
		$items = array_map( array( 'Red_Item', 'reduce_sorted_items' ), $items );

		// Sort it in PHP
		ksort( $items );
		$items = array_values( $items );
		return $items;
	}

	static function sort_urls( $first, $second ) {
		if ( $first['position'] === $second['position'] )
			return 0;

		return $first['position'] < $second['position'];
	}

	static function reduce_sorted_items( $item ) {
		return $item['item'];
	}

	static function get_by_module( $module ) {
		global $wpdb;

		$sql = "SELECT {$wpdb->prefix}redirection_items.* FROM {$wpdb->prefix}redirection_items INNER JOIN {$wpdb->prefix}redirection_groups ON {$wpdb->prefix}redirection_groups.id={$wpdb->prefix}redirection_items.group_id";
		$sql .= $wpdb->prepare( " WHERE {$wpdb->prefix}redirection_groups.module_id=%d", $module );

		$rows = $wpdb->get_results( $sql );
		$items = array();

		foreach ( (array) $rows as $row ) {
			$items[] = new Red_Item( $row );
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
		$options = red_get_options();
		$id = time();

		$url = $options['auto_target'];
		$url = str_replace( '$dec$', $id, $url );
		$url = str_replace( '$hex$', sprintf( '%x', $id ), $url );
		return $url;
	}

	static function create( array $details ) {
		global $wpdb;

		$details = array_map( 'trim', $details );
		$details = array_map( 'stripslashes', $details );

		// Auto generate URLs
		if ( empty( $details['source'] ) )
			$details['source'] = self::auto_generate();

		if ( empty( $details['target'] ) )
			$details['target'] = self::auto_generate();

		// Make sure we don't redirect to ourself
		if ( $details['source'] === $details['target'] )
			return new WP_Error( 'redirect-add', __( 'Source and target URL must be different', 'redirection' ) );

		$parsed_url = parse_url( $details['source'] );
		$parsed_domain = parse_url( site_url() );

		if ( isset( $parsed_url['scheme'] ) && ( $parsed_url['scheme'] === 'http' || $parsed_url['scheme'] === 'https' ) && $parsed_url['host'] !== $parsed_domain['host'] ) {
			return new WP_Error( 'redirect-add', sprintf( __( 'You can only redirect from a relative URL (<code>%s</code>) on this domain (<code>%s</code>).', 'redirection' ), $parsed_url['path'], $parsed_domain['host'] ) );
		}

		$matcher  = Red_Match::create( $details['match'] );
		$group_id = intval( $details['group_id'] );
		$group    = Red_Group::get( $group_id );

		if ( $group_id <= 0 || ! $group )
			return new WP_Error( 'redirect-add', __( 'Invalid group when creating redirect', 'redirection' ) );

		if ( ! $matcher )
			return new WP_Error( 'redirect-add', __( 'Invalid source URL when creating redirect for given match type', 'redirection' ) );

		$regex    = ( isset( $details['regex'] ) && $details['regex'] !== false ) ? 1 : 0;
		$position = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items WHERE group_id=%d", $group_id ) );

		$action = $details['red_action'];
		$action_code = 0;
		if ( $action === 'url' || $action === 'random' )
			$action_code = 301;
		elseif ( $action === 'error' )
			$action_code = 404;

		if ( isset( $details['action_code'] ) )
			$action_code = intval( $details['action_code'] );

		$data = array(
			'url'         => self::sanitize_url( $details['source'], $regex ),
			'action_type' => $details['red_action'],
			'regex'       => $regex,
			'position'    => $position,
			'match_type'  => $details['match'],
			'action_data' => $matcher->data( $details ),
			'action_code' => $action_code,
			'last_access' => '0000-00-00 00:00:00',
			'group_id'    => $group_id,
		);

		$data = apply_filters( 'redirection_create_redirect', $data );

		$wpdb->delete( $wpdb->prefix.'redirection_items', array( 'url' => $data['action_data'], 'action_type' => $data['action_type'], 'action_data' => $data['url'] ) );

		if ( $wpdb->insert( $wpdb->prefix.'redirection_items', $data ) ) {
			Red_Module::flush( $group_id );
			return self::get_by_id( $wpdb->insert_id );
		}

		return new WP_Error( 'redirect-add', __( 'Unable to add new redirect - delete Redirection from the options page and re-install' ) );
	}

	public function delete() {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_items WHERE id=%d", $this->id ) );

		RE_Log::delete_for_id( $this->id );

		// Reorder all elements
		$rows = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}redirection_items ORDER BY position" );
		if ( count( $rows ) > 0 ) {
			foreach ( $rows as $pos => $row ) {
				$wpdb->update( $wpdb->prefix.'redirection_items', array( 'position' => $pos ), array( 'id' => $row->id ) );
			}
		}

		Red_Module::flush( $this->group_id );
	}

	static function sanitize_url( $url, $regex = false ) {
		// Make sure that the old URL is relative
		$url = preg_replace( '@^https?://(.*?)/@', '/', $url );
		$url = preg_replace( '@^https?://(.*?)$@', '/', $url );

		// No hash
		$url = preg_replace( '/#.*$/', '', $url );

		// No new lines
		$url = preg_replace( "/[\r\n\t].*?$/s", '', $url );

		// Clean control codes
		$url = preg_replace( '/[^\PC\s]/u', '', $url );

		// Ensure a slash at start
		if ( substr( $url, 0, 1 ) !== '/' && $regex === false )
			$url = '/'.$url;

		return $url;
	}

	function update( $details ) {
		if ( strlen( $details['old'] ) > 0 ) {
			global $wpdb;

			$details = array_map( 'stripslashes', $details );

			$this->regex = isset( $details['regex'] ) ? 1 : 0;
			$this->url   = self::sanitize_url( $details['old'], $this->regex );
			$this->title = $details['title'];

			$data = $this->match->data( $details );

			$this->action_code = 0;
			if ( isset( $details['action_code'] ) )
				$this->action_code = intval( $details['action_code'] );

			$old_group = false;
			if ( isset( $details['group_id'] ) ) {
				$old_group = intval( $this->group_id );
				$this->group_id = intval( $details['group_id'] );
			}

			// Save this
			$wpdb->update( $wpdb->prefix.'redirection_items', array( 'url' => $this->url, 'regex' => $this->regex, 'action_code' => $this->action_code, 'action_data' => $data, 'group_id' => $this->group_id, 'title' => $this->title ), array( 'id' => $this->id ) );

			if ( $old_group !== $this->group_id ) {
				Red_Module::flush( $this->group_id );
				Red_Module::flush( $old_group );
			}
		}
	}

	static function save_order( $items, $start ) {
		global $wpdb;

		foreach ( $items as $pos => $id ) {
			$wpdb->update( $wpdb->prefix.'redirection_items', array( 'position' => $pos + $start ), array( 'id' => $id ) );
		}

		Red_Module::flush( $this->group_id );
	}

	function matches( $url ) {
		$this->url = str_replace( ' ', '%20', $this->url );
		$matches   = false;

		// Check if we match the URL
		if ( ( $this->regex === false && ( $this->url === $url || $this->url === rtrim( $url, '/' ) || $this->url === urldecode( $url ) ) ) || ( $this->regex === true && @preg_match( '@'.str_replace( '@', '\\@', $this->url ).'@', $url, $matches ) > 0) || ( $this->regex === true && @preg_match( '@'.str_replace( '@', '\\@', $this->url ).'@', urldecode( $url ), $matches ) > 0) ) {
			// Check if our match wants this URL
			$target = $this->match->get_target( $url, $this->url, $this->regex );

			if ( $target ) {
				$target = $this->replace_special_tags( $target );

				$this->visit( $url, $target );

				if ( $this->status === 'enabled' )
					return $this->action->process_before( $this->action_code, $target );
			}
		}

		return false;
	}

	function replace_special_tags( $target ) {
		if ( is_numeric( $target ) )
			$target = get_permalink( $target );
		else {
			$user = wp_get_current_user();
			if ( ! empty( $user ) ) {
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

			$options = red_get_options();
			if ( isset( $options['expire_redirect'] ) && $options['expire_redirect'] >= 0 )
				$log = RE_Log::create( $url, $target, $_SERVER['HTTP_USER_AGENT'], $ip, isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '', array( 'redirect_id' => $this->id, 'group_id' => $this->group_id ) );
		}
	}

	public function is_enabled() {
		return $this->status === 'enabled';
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

	public function enable() {
		global $wpdb;

		$this->status = 'enabled';
		$wpdb->update( $wpdb->prefix.'redirection_items', array( 'status' => $this->status ), array( 'id' => $this->id ) );
	}

	public function disable() {
		global $wpdb;

		$this->status = 'disabled';
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
			return $actions[ $action ];
		return $actions;
	}

	function match_name() {
		return $this->match->match_name();
	}

	function type() {
		if ( ( $this->action_type === 'url' || $this->action_type === 'error' || $this->action_type === 'random' ) && $this->action_code > 0 )
			return $this->action_code;
		else if ( $this->action_type === 'pass' )
			return 'pass';
		return '&mdash;';
	}

	public function get_id() {
		return $this->id;
	}

	public function get_position() {
		return $this->position;
	}

	public function get_group_id() {
		return $this->group_id;
	}

	public function get_url() {
		return $this->url;
	}

	public function get_title() {
		return $this->title;
	}

	public function get_hits() {
		return $this->last_count;
	}

	public function get_last_hit() {
		return $this->last_access;
	}

	public function is_regex() {
		return $this->regex ? true : false;
	}

	public function get_match_type() {
		return $this->match_type;
	}

	public function get_action_type() {
		return $this->action_type;
	}

	public function get_action_code() {
		return intval( $this->action_code );
	}

	public function get_action_data() {
		return $this->action_data;
	}
}
