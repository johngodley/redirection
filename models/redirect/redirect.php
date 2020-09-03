<?php

require_once dirname( __FILE__ ) . '/redirect-sanitizer.php';
require_once dirname( __FILE__ ) . '/redirect-filter.php';
require_once dirname( __FILE__ ) . '/redirect-options.php';

class Red_Item {
	private $id  = null;
	private $url = null;
	private $match_url = null;
	private $match_data = null;
	private $regex = false;
	private $action_data = null;
	private $action_code = 0;
	private $action_type;
	private $match_type;
	private $title;
	private $last_access = null;
	private $last_count = 0;
	private $status = 'enabled';
	private $position;
	private $group_id;

	public $source_flags = false;

	public function __construct( $values = null ) {
		if ( is_object( $values ) ) {
			$this->load_from_data( $values );
		}
	}

	private function load_from_data( stdClass $values ) {
		foreach ( $values as $key => $value ) {
			if ( property_exists( $this, $key ) ) {
				$this->$key = $value;
			}
		}

		$this->regex = (bool) $this->regex;
		$this->last_access = ( $this->last_access === '1970-01-01 00:00:00' || $this->last_access === '0000-00-00 00:00:00' ) ? 0 : mysql2date( 'U', $this->last_access );

		$this->load_matcher();
		$this->load_action();
		$this->load_source();
	}

	// v4 JSON
	private function load_source() {
		// Default regex flag to regex column. This will be removed once the regex column has been migrated
		// todo: deprecate
		$this->source_flags = new Red_Source_Flags( array_merge( red_get_options(), [ 'flag_regex' => $this->regex ] ) );
		$this->source_options = new Red_Source_Options();

		if ( isset( $this->match_data ) ) {
			$json = json_decode( $this->match_data, true );

			if ( $json && isset( $json['source'] ) ) {
				// Merge redirect flags with default flags
				$this->source_flags->set_flags( array_merge( red_get_options(), $json['source'] ) );
			}

			if ( $json && isset( $json['options'] ) ) {
				$this->source_options->set_options( $json['options'] );
			}
		}
	}

	private function load_matcher() {
		if ( empty( $this->match_type ) ) {
			$this->match_type = 'url';
		}

		$this->match = Red_Match::create( $this->match_type, $this->action_data );
	}

	private function load_action() {
		if ( empty( $this->action_type ) ) {
			$this->action_type = 'nothing';
		}

		$this->action = Red_Action::create( $this->action_type, $this->action_code );
		if ( $this->match ) {
			$this->match->action = $this->action;
		}
	}

	public static function get_all_for_module( $module ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT {$wpdb->prefix}redirection_items.* FROM {$wpdb->prefix}redirection_items
				INNER JOIN {$wpdb->prefix}redirection_groups ON {$wpdb->prefix}redirection_groups.id={$wpdb->prefix}redirection_items.group_id
				AND {$wpdb->prefix}redirection_groups.status='enabled' AND {$wpdb->prefix}redirection_groups.module_id=%d
				WHERE {$wpdb->prefix}redirection_items.status='enabled'
				ORDER BY {$wpdb->prefix}redirection_groups.position,{$wpdb->prefix}redirection_items.position",
				$module
			)
		);
		$items = array();

		foreach ( (array) $rows as $row ) {
			$items[] = new Red_Item( $row );
		}

		return $items;
	}

	public static function get_for_url( $url ) {
		$status = new Red_Database_Status();

		// deprecate
		if ( $status->does_support( '4.0' ) ) {
			return self::get_for_matched_url( $url );
		}

		return self::get_old_url( $url );
	}

	public static function get_for_matched_url( $url ) {
		global $wpdb;

		$url = new Red_Url_Match( $url );
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}redirection_items WHERE match_url=%s OR match_url='regex'", $url->get_url() ) );

		$items = array();
		if ( count( $rows ) > 0 ) {
			foreach ( $rows as $row ) {
				$items[] = new Red_Item( $row );
			}
		}

		usort( $items, array( 'Red_Item', 'sort_urls' ) );

		return $items;
	}

	// deprecate
	public static function get_old_url( $url ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT {$wpdb->prefix}redirection_items.*,{$wpdb->prefix}redirection_groups.position AS group_pos
				FROM {$wpdb->prefix}redirection_items INNER JOIN {$wpdb->prefix}redirection_groups ON
				{$wpdb->prefix}redirection_groups.id={$wpdb->prefix}redirection_items.group_id AND {$wpdb->prefix}redirection_groups.status='enabled'
				AND {$wpdb->prefix}redirection_groups.module_id=%d WHERE ({$wpdb->prefix}redirection_items.regex=1
				OR {$wpdb->prefix}redirection_items.url=%s)",
				WordPress_Module::MODULE_ID,
				$url
			)
		);

		$items = array();
		if ( count( $rows ) > 0 ) {
			foreach ( $rows as $row ) {
				$items[] = array(
					'position' => ( $row->group_pos * 1000 ) + $row->position,
					'item' => new Red_Item( $row ),
				);
			}
		}

		usort( $items, array( 'Red_Item', 'sort_urls_old' ) );
		$items = array_map( array( 'Red_Item', 'reduce_sorted_items' ), $items );

		// Sort it in PHP
		ksort( $items );
		$items = array_values( $items );
		return $items;
	}

	public static function reduce_sorted_items( $item ) {
		return $item['item'];
	}

	public static function get_all() {
		global $wpdb;

		$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_items" );
		$items = array();

		foreach ( (array) $rows as $row ) {
			$items[] = new Red_Item( $row );
		}

		return $items;
	}

	public static function sort_urls( $first, $second ) {
		if ( $first->position === $second->position ) {
			return 0;
		}

		return ( $first->position < $second->position ) ? -1 : 1;
	}

	public static function sort_urls_old( $first, $second ) {
		if ( $first['position'] === $second['position'] ) {
			return 0;
		}

		return ( $first['position'] < $second['position'] ) ? -1 : 1;
	}

	/**
	 * Get a redirect by ID
	 *
	 * @param integer $id Redirect ID.
	 * @return Red_Item|false
	 */
	public static function get_by_id( $id ) {
		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}redirection_items WHERE id=%d", $id ) );
		if ( $row ) {
			return new Red_Item( $row );
		}

		return false;
	}

	public static function disable_where_matches( $url ) {
		global $wpdb;

		$wpdb->update( $wpdb->prefix . 'redirection_items', array( 'status' => 'disabled' ), array( 'url' => $url ) );
	}

	public function delete() {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_items WHERE id=%d", $this->id ) );
		do_action( 'redirection_redirect_deleted', $this );

		Red_Module::flush( $this->group_id );
	}

	public static function create( array $details ) {
		global $wpdb;

		$sanitizer = new Red_Item_Sanitize();
		$data = $sanitizer->get( $details );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$data['status'] = 'enabled';

		// todo: fix this mess
		if ( ( isset( $details['enabled'] ) && ( $details['enabled'] === 'disabled' || $details['enabled'] === false ) ) || ( isset( $details['status'] ) && $details['status'] === 'disabled' ) ) {
			$data['status'] = 'disabled';
		}

		if ( ! isset( $details['position'] ) || $details['position'] === 0 ) {
			$data['position'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items WHERE group_id=%d", $data['group_id'] ) );
		}

		$data = apply_filters( 'redirection_create_redirect', $data );

		if ( ! empty( $data['match_data'] ) ) {
			$data['match_data'] = wp_json_encode( $data['match_data'] );
		}

		// Create
		if ( $wpdb->insert( $wpdb->prefix . 'redirection_items', $data ) !== false ) {
			Red_Module::flush( $data['group_id'] );

			$redirect = self::get_by_id( $wpdb->insert_id );
			if ( $redirect ) {
				do_action( 'redirection_redirect_updated', $wpdb->insert_id, $redirect );

				return $redirect;
			}

			return new WP_Error( 'redirect_create_failed', 'Unable to get newly added redirect' );
		}

		return new WP_Error( 'redirect_create_failed', 'Unable to add new redirect' );
	}

	public function update( $details ) {
		global $wpdb;

		$sanitizer = new Red_Item_Sanitize();
		$data = $sanitizer->get( $details );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$old_group = false;
		if ( $data['group_id'] !== $this->group_id ) {
			$old_group = $this->group_id;
		}

		// Save this
		$data = apply_filters( 'redirection_update_redirect', $data );
		if ( ! empty( $data['match_data'] ) ) {
			$data['match_data'] = wp_json_encode( $data['match_data'], JSON_UNESCAPED_SLASHES );
		}

		$result = $wpdb->update( $wpdb->prefix . 'redirection_items', $data, array( 'id' => $this->id ) );
		if ( $result !== false ) {
			do_action( 'redirection_redirect_updated', $this, self::get_by_id( $this->id ) );
			$this->load_from_data( (object) $data );

			Red_Module::flush( $this->group_id );

			if ( $old_group !== $this->group_id ) {
				Red_Module::flush( $old_group );
			}

			return true;
		}

		return new WP_Error( 'redirect_create_failed', 'Unable to update redirect' );
	}

	/**
	 * Determine if a requested URL matches this URL
	 *
	 * @param string $requested_url The URL being requested.
	 * @return bool true if matched, false otherwise
	 */
	public function is_match( $requested_url, $original_url = false ) {
		if ( ! $this->is_enabled() ) {
			return false;
		}

		if ( $original_url === false ) {
			$original_url = $requested_url;
		}

		$url = new Red_Url( $this->url );
		if ( $url->is_match( $requested_url, $this->source_flags ) ) {
			// URL is matched, now match the redirect type (i.e. login status, IP address)
			$target = $this->match->is_match( $requested_url );

			// Check if our action wants a URL
			if ( $this->action->needs_target() ) {
				// Our action requires a target URL - get this, using our type match result
				$target = $this->match->get_target_url( $original_url, $url->get_url(), $this->source_flags, $target );
				$target = Red_Url_Query::add_to_target( $target, $original_url, $this->source_flags );
				$target = apply_filters( 'redirection_url_target', $target, $this->url );
			}

			// Fire any early actions
			if ( $target ) {
				$target = $this->action->process_before( $this->action_code, $target );
			}

			if ( $target ) {
				// We still have a target, so log it and carry on with the action
				do_action( 'redirection_visit', $this, $requested_url, $target );
				return $this->action->process_after( $this->action_code, $target );
			}
		}

		return false;
	}

	public function visit( $url, $target ) {
		global $wpdb;

		$options = red_get_options();

		// Update the counters
		$this->last_count++;

		if ( apply_filters( 'redirection_redirect_counter', $options['track_hits'], $url ) ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}redirection_items SET last_count=last_count+1, last_access=NOW() WHERE id=%d", $this->id ) );
		}

		if ( $target && $this->source_options->can_log() ) {
			if ( $target === true ) {
				$target = $this->action_type === 'pass' ? $this->match->get_data()['url'] : '';
			}

			$details = [
				'target' => $target,
				'agent' => Redirection_Request::get_user_agent(),
				'referrer' => Redirection_Request::get_referrer(),
				'request_method' => Redirection_Request::get_request_method(),
				'http_code' => $this->get_action_code(),
				'redirect_id' => $this->id,
				'redirect_by' => 'redirection',
			];

			if ( $options['log_header'] ) {
				$details['request_data'] = [
					'headers' => Redirection_Request::get_request_headers(),
				];
			}

			Red_Redirect_Log::create( Redirection_Request::get_server(), $url, Redirection_Request::get_ip(), $details );
		}
	}

	public function is_enabled() {
		return $this->status === 'enabled';
	}

	public function reset() {
		global $wpdb;

		$this->last_count  = 0;
		$this->last_access = '1970-01-01 00:00:00';

		$update = [
			'last_count' => 0,
			'last_access' => $this->last_access,
		];
		$where = [
			'id' => $this->id,
		];

		$wpdb->update( $wpdb->prefix . 'redirection_items', $update, $where );
	}

	public function enable() {
		global $wpdb;

		$this->status = 'enabled';
		$wpdb->update( $wpdb->prefix . 'redirection_items', [ 'status' => $this->status ], [ 'id' => $this->id ] );
	}

	public function disable() {
		global $wpdb;

		$this->status = 'disabled';
		$wpdb->update( $wpdb->prefix . 'redirection_items', [ 'status' => $this->status ], [ 'id' => $this->id ] );
	}

	public function get_id() {
		return intval( $this->id, 10 );
	}

	public function get_position() {
		return intval( $this->position, 10 );
	}

	public function get_group_id() {
		return intval( $this->group_id, 10 );
	}

	public function get_url() {
		return $this->url;
	}

	public function get_match_url() {
		return $this->match_url;
	}

	public function get_match_data() {
		$source = $this->source_flags->get_json_with_defaults();
		$options = $this->source_options->get_json();

		$data = [];

		if ( ! empty( $source ) ) {
			$data['source'] = $source;
		}

		if ( ! empty( $options ) ) {
			$data['options'] = $options;
		}

		if ( count( $data ) > 0 ) {
			return $data;
		}

		return null;
	}

	public function get_title() {
		return $this->title ? $this->title : '';
	}

	public function get_hits() {
		return intval( $this->last_count, 10 );
	}

	public function get_last_hit() {
		return intval( $this->last_access, 10 );
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
		return intval( $this->action_code, 10 );
	}

	public function get_action_data() {
		return $this->action_data ? $this->action_data : '';
	}

	public static function delete_all( array $params ) {
		global $wpdb;

		$filters = new Red_Item_Filters( isset( $params['filterBy'] ) ? $params['filterBy'] : [] );
		$where = $filters->get_as_sql();

		return $wpdb->query( "DELETE FROM {$wpdb->prefix}redirection_items $where" );
	}

	public static function reset_all( array $params ) {
		global $wpdb;

		$filters = new Red_Item_Filters( isset( $params['filterBy'] ) ? $params['filterBy'] : [] );
		$where = $filters->get_as_sql();

		return $wpdb->query( "UPDATE {$wpdb->prefix}redirection_items SET last_count=0, last_access='1970-01-01 00:00:00' $where" );
	}

	public static function set_status_all( $status, array $params ) {
		global $wpdb;

		$filters = new Red_Item_Filters( isset( $params['filterBy'] ) ? $params['filterBy'] : [] );
		$where = $filters->get_as_sql();

		return $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}redirection_items SET status=%s $where", $status === 'enable' ? 'enabled' : 'disabled' ) );
	}

	public static function get_filtered( array $params ) {
		global $wpdb;

		$orderby = 'id';
		$direction = 'DESC';
		$limit = RED_DEFAULT_PER_PAGE;
		$offset = 0;
		$where = '';

		if ( isset( $params['orderby'] ) && in_array( $params['orderby'], array( 'source', 'last_count', 'last_access', 'position' ), true ) ) {
			$orderby = $params['orderby'];

			if ( $orderby === 'source' ) {
				$orderby = 'url';
			}
		}

		if ( isset( $params['direction'] ) && in_array( $params['direction'], array( 'asc', 'desc' ), true ) ) {
			$direction = strtoupper( $params['direction'] );
		}

		if ( isset( $params['filterBy'] ) && is_array( $params['filterBy'] ) ) {
			$filters = new Red_Item_Filters( $params['filterBy'] );
			$where = $filters->get_as_sql();
		}

		if ( isset( $params['per_page'] ) ) {
			$limit = intval( $params['per_page'], 10 );
			$limit = min( RED_MAX_PER_PAGE, $limit );
			$limit = max( 5, $limit );
		}

		if ( isset( $params['page'] ) ) {
			$offset = intval( $params['page'], 10 );
			$offset = max( 0, $offset );
			$offset *= $limit;
		}

		// $orderby and $direction is whitelisted
		// phpcs:ignore
		$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_items $where ORDER BY $orderby $direction " . $wpdb->prepare( 'LIMIT %d,%d', $offset, $limit ) );

		// phpcs:ignore
		$total_items = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items " . $where ) );
		$items = array();

		foreach ( $rows as $row ) {
			$group = new Red_Item( $row );
			$items[] = $group->to_json();
		}

		return array(
			'items' => $items,
			'total' => intval( $total_items, 10 ),
		);
	}

	public function to_json() {
		return array(
			'id' => $this->get_id(),
			'url' => $this->get_url(),
			'match_url' => $this->get_match_url(),
			'match_data' => $this->get_match_data(),
			'action_code' => $this->get_action_code(),
			'action_type' => $this->get_action_type(),
			'action_data' => $this->match ? $this->match->get_data() : null,
			'match_type' => $this->get_match_type(),
			'title' => $this->get_title(),
			'hits' => $this->get_hits(),
			'regex' => $this->is_regex(),
			'group_id' => $this->get_group_id(),
			'position' => $this->get_position(),
			'last_access' => $this->get_last_hit() > 0 ? date_i18n( get_option( 'date_format' ), $this->get_last_hit() ) : '-',
			'enabled' => $this->is_enabled(),
		);
	}
}
