<?php

require_once __DIR__ . '/redirect-sanitizer.php';
require_once __DIR__ . '/redirect-filter.php';
require_once __DIR__ . '/redirect-options.php';
require_once __DIR__ . '/redirect-cache.php';

/**
 * Redirect class
 *
 * @phpstan-type RedirectData array{
 *     id?: int,
 *     url?: string,
 *     match_url?: string,
 *     match_data?: string,
 *     regex?: bool|int,
 *     action_data?: string,
 *     action_code?: int,
 *     action_type?: string,
 *     match_type?: string,
 *     title?: string,
 *     last_access?: string|int,
 *     last_count?: int,
 *     status?: string,
 *     position?: int,
 *     group_id?: int
 * }
 * @phpstan-type RedirectJson array{
 *     id: int,
 *     url: string,
 *     match_url: string,
 *     match_data: mixed,
 *     action_code: int,
 *     action_type: string,
 *     action_data: mixed,
 *     match_type: string,
 *     title: string,
 *     hits: int,
 *     regex: bool,
 *     group_id: int,
 *     position: int,
 *     last_access: string,
 *     enabled: bool
 * }
 * @phpstan-type RedirectStatusAction 'enable'|'disable'
 * @phpstan-type RedirectFilterBy array<string, string>
 * @phpstan-type RedirectFilterParams array{
 *     filterBy?: RedirectFilterBy
 * }
 * @phpstan-type RedirectMatchData array{
 *     source?: array{
 *         flag_query: 'ignore'|'exact'|'pass'|'exactorder',
 *         flag_case: bool,
 *         flag_trailing: bool,
 *         flag_regex: bool
 *     },
 *     options?: array{
 *         log_exclude?: bool
 *     }
 * }
 * @phpstan-type RedirectUpdateDetails array{
 *     url?: string,
 *     regex?: int|bool,
 *     match_type?: string,
 *     action_type?: string,
 *     action_code?: int,
 *     action_data?: array<string, mixed>,
 *     match_data?: RedirectMatchData,
 *     title?: string,
 *     group_id?: int,
 *     position?: int,
 *     hits?: int,
 *     last_access?: string
 * }
 * @phpstan-type RedirectCreateDetails array{
 *     url?: string,
 *     regex?: int|bool,
 *     match_type?: string,
 *     action_type?: string,
 *     action_code?: int,
 *     action_data?: array<string, mixed>,
 *     match_data?: RedirectMatchData,
 *     title?: string,
 *     group_id?: int,
 *     position?: int,
 *     hits?: int,
 *     last_access?: string,
 *     enabled?: bool|string,
 *     status?: string
 * }
 */
class Red_Item {
	/**
	 * Maximum number of redirects to get from the database in one request.
	 *
	 * @var integer
	 */
	const MAX_REDIRECTS = 20000;
	const DEFAULT_PER_PAGE = 25;
	const MAX_PER_PAGE = 200;

	/**
	 * Redirect ID
	 *
	 * @var integer
	 */
	private $id = 0;

	/**
	 * Source URL (full)
	 *
	 * @var string
	 */
	private $url = '';

	/**
	 * Source URL (match)
	 *
	 * @var string
	 */
	private $match_url = '';

	/**
	 * Undocumented variable
	 *
	 * @var string
	 */
	private $match_data = '';

	/**
	 * Is regular expression?
	 *
	 * @var boolean
	 */
	private $regex = false;

	/**
	 * Action data
	 *
	 * @var string
	 */
	private $action_data = '';

	/**
	 * HTTP code
	 *
	 * @var integer
	 */
	private $action_code = 0;

	/**
	 * Action type
	 *
	 * @var string
	 */
	private $action_type = '';

	/**
	 * Match type
	 *
	 * @var string
	 */
	private $match_type = '';

	/**
	 * Redirect title
	 *
	 * @var string
	 */
	private $title = '';

	/**
	 * Last time the redirect was accessed
	 *
	 * @var integer
	 */
	private $last_access = 0;

	/**
	 * Number of hits
	 *
	 * @var integer
	 */
	private $last_count = 0;

	/**
	 * Status of the redirect
	 *
	 * @var string
	 */
	private $status = 'enabled';

	/**
	 * Position value
	 *
	 * @var integer
	 */
	private $position = 0;

	/**
	 * Group ID
	 *
	 * @var integer
	 */
	private $group_id = 0;

	/**
	 * Source flags
	 *
	 * @var Red_Source_Flags|null
	 */
	public $source_flags = null;

	/**
	 * Source options
	 *
	 * @var Red_Source_Options|null
	 */
	public $source_options = null;

	/**
	 * Match object
	 *
	 * @var Red_Match<array<string, mixed>, (array<string, mixed>|string)>|null
	 */
	public $match = null;

	/**
	 * Action object
	 *
	 * @var Red_Action|null
	 */
	public $action = null;

	/**
	 * Constructor
	 *
	 * @param RedirectData|stdClass|null $values Values.
	 */
	public function __construct( $values = null ) {
		if ( is_object( $values ) ) {
			$this->load_from_data( (array) $values );
		} elseif ( is_array( $values ) ) {
			$this->load_from_data( $values );
		}
	}

	/**
	 * Load values into the object
	 *
	 * @param RedirectData $values Values.
	 * @return void
	 */
	private function load_from_data( array $values ) {
		// @phpstan-ignore function.impossibleType, booleanOr.rightAlwaysTrue
		if ( array_key_exists( 'id', $values ) && ( is_string( $values['id'] ) || is_int( $values['id'] ) ) ) {
			$this->id = intval( $values['id'], 10 );
		}

		// @phpstan-ignore function.alreadyNarrowedType
		if ( array_key_exists( 'url', $values ) && is_string( $values['url'] ) ) {
			$this->url = $values['url'];
		}

		// @phpstan-ignore function.alreadyNarrowedType
		if ( array_key_exists( 'match_url', $values ) && is_string( $values['match_url'] ) ) {
			$this->match_url = $values['match_url'];
		}

		// @phpstan-ignore notIdentical.alwaysTrue
		if ( array_key_exists( 'match_data', $values ) && $values['match_data'] !== null ) {
			$this->match_data = $values['match_data'];
		}

		if ( array_key_exists( 'regex', $values ) ) {
			$this->regex = (bool) $values['regex'];
		}

		// @phpstan-ignore notIdentical.alwaysTrue
		if ( array_key_exists( 'action_data', $values ) && $values['action_data'] !== null ) {
			$this->action_data = $values['action_data'];
		}

		if ( array_key_exists( 'action_code', $values ) ) {
			$this->action_code = intval( $values['action_code'], 10 );
		}

		// @phpstan-ignore function.alreadyNarrowedType
		if ( array_key_exists( 'action_type', $values ) && is_string( $values['action_type'] ) ) {
			$this->action_type = $values['action_type'];
		}

		// @phpstan-ignore function.alreadyNarrowedType
		if ( array_key_exists( 'match_type', $values ) && is_string( $values['match_type'] ) ) {
			$this->match_type = $values['match_type'];
		}

		// @phpstan-ignore function.alreadyNarrowedType
		if ( array_key_exists( 'title', $values ) && is_string( $values['title'] ) ) {
			$this->title = $values['title'];
		}

		if ( array_key_exists( 'last_access', $values ) ) {
			$this->last_access = $this->parse_last_access( $values['last_access'] );
		}

		if ( array_key_exists( 'last_count', $values ) ) {
			$this->last_count = intval( $values['last_count'], 10 );
		}

		// @phpstan-ignore function.alreadyNarrowedType
		if ( array_key_exists( 'status', $values ) && is_string( $values['status'] ) ) {
			$this->status = $values['status'];
		}

		if ( array_key_exists( 'position', $values ) ) {
			$this->position = intval( $values['position'], 10 );
		}

		// XXX convert to pnpm

		if ( array_key_exists( 'group_id', $values ) ) {
			$this->group_id = intval( $values['group_id'], 10 );
		}

		$this->match_type = $this->match_type !== '' ? $this->match_type : 'url';
		$this->action_type = $this->action_type !== '' ? $this->action_type : 'nothing';
		// @phpstan-ignore cast.useless
		$this->regex = (bool) $this->regex;

		$this->match = Red_Match::create( $this->match_type, $this->action_data );
		$this->action = Red_Action::create( $this->action_type, $this->action_code );

		$options = Red_Options::get();
		$this->source_flags = new Red_Source_Flags( array_merge( $options, [ 'flag_regex' => $this->regex ] ) );
		$this->source_options = new Red_Source_Options();

		if ( $this->match_data !== '' ) {
			$json = json_decode( $this->match_data, true );

			if ( is_array( $json ) && isset( $json['source'] ) && is_array( $json['source'] ) ) {
				$this->source_flags->set_flags( array_merge( $options, $json['source'] ) );
			}

			if ( is_array( $json ) && isset( $json['options'] ) && is_array( $json['options'] ) ) {
				$this->source_options->set_options( $json['options'] );
			}
		}
	}

	/**
	 * Parse a last_access value into a timestamp.
	 *
	 * @param string|int $value Raw value.
	 * @return int
	 */
	private function parse_last_access( $value ) {
		if ( is_int( $value ) ) {
			return $value;
		}

		if ( $value === '1970-01-01 00:00:00' || $value === '0000-00-00 00:00:00' ) {
			return 0;
		}

		$timestamp = mysql2date( 'U', $value );

		return $timestamp ? intval( $timestamp, 10 ) : 0;
	}

	/**
	 * Get all redirects for a module. No paging.
	 *
	 * @param integer $module Module ID.
	 * @return Red_Item[]
	 */
	public static function get_all_for_module( $module ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT {$wpdb->prefix}redirection_items.* FROM {$wpdb->prefix}redirection_items
				INNER JOIN {$wpdb->prefix}redirection_groups ON {$wpdb->prefix}redirection_groups.id={$wpdb->prefix}redirection_items.group_id
				AND {$wpdb->prefix}redirection_groups.module_id=%d
				AND {$wpdb->prefix}redirection_groups.status='enabled'
				WHERE {$wpdb->prefix}redirection_items.status='enabled'
				ORDER BY {$wpdb->prefix}redirection_items.position",
				$module
			)
		);
		$items = array();

		foreach ( (array) $rows as $row ) {
			$items[] = new Red_Item( $row );
		}

		return $items;
	}

	/**
	 * Get a list of redirects that match a URL. This is a helper function that calls the appropriate method for the current database version.
	 *
	 * @param string $url URL to match.
	 * @return Red_Item[]
	 */
	public static function get_for_url( $url ) {
		$status = new Red_Database_Status();

		// deprecate
		if ( $status->does_support( '4.0' ) ) {
			return self::get_for_matched_url( $url );
		}

		return self::get_old_url( $url );
	}

	/**
	 * Get a redirect that matches a URL
	 *
	 * @param string $url URL to match.
	 * @return Red_Item[]
	 */
	public static function get_for_matched_url( $url ) {
		global $wpdb;

		// Anything in the cache?
		$cache = Redirect_Cache::init();
		$rows = $cache->get( $url );

		$url = new Red_Url_Match( $url );
		$url_without = $url->get_url();
		$url_with = $url->get_url_with_params();

		if ( $rows === false ) {
			// Nothing in cache, get from DB
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}redirection_items WHERE match_url IN (%s, %s, 'regex') AND status='enabled' LIMIT %d", $url_without, $url_with, self::MAX_REDIRECTS ) );
		}

		$items = [];

		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$items[] = new Red_Item( $row );
			}

			usort( $items, [ 'Red_Item', 'sort_urls' ] );

			if ( count( $items ) >= self::MAX_REDIRECTS ) {
				// Something has gone pretty wrong at this point
				// @phpstan-ignore disallowed.function
				error_log( 'Redirection: maximum redirect limit exceeded' ); // phpcs:ignore
			}
		}

		return $items;
	}

	/**
	 * Get a redirect that matches a URL
	 *
	 * @deprecated 3.7.3
	 * @param string $url URL to match.
	 * @return Red_Item[]
	 */
	public static function get_old_url( $url ) {
		global $wpdb;

		// Yeah I know
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

	/**
	 * Return only the 'item' element
	 *
	 * @param array{position: int, item: Red_Item} $item Item with position and Red_Item object.
	 * @return Red_Item
	 */
	public static function reduce_sorted_items( $item ) {
		return $item['item'];
	}

	/**
	 * Get all redirects. No paging, and it could be too large for memory
	 *
	 * @return Red_Item[]
	 */
	public static function get_all() {
		global $wpdb;

		$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_items" );
		$items = array();

		foreach ( (array) $rows as $row ) {
			$items[] = new Red_Item( $row );
		}

		return $items;
	}

	/**
	 * Sort URLs
	 *
	 * @param Red_Item $first First URL.
	 * @param Red_Item $second Second URL.
	 * @return integer
	 */
	public static function sort_urls( $first, $second ) {
		if ( $first->position === $second->position ) {
			// Fall back to which redirect was created first
			return ( $first->id < $second->id ) ? -1 : 1;
		}

		return ( $first->position < $second->position ) ? -1 : 1;
	}

	/**
	 * Sort URLs (deprecated)
	 *
	 * @param array $first First URL.
	 * @param array $second Second URL.
	 *
	 * @phpstan-param array{position:int} $first
	 * @phpstan-param array{position:int} $second
	 * @phpstan-return -1|0|1
	 * @return int
	 */
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

	/**
	 * Disable all redirects that match the URL
	 *
	 * @param string $url URL to match.
	 * @return void
	 */
	public static function disable_where_matches( $url ) {
		global $wpdb;

		$wpdb->update( $wpdb->prefix . 'redirection_items', array( 'status' => 'disabled' ), array( 'url' => $url ) );
	}

	/**
	 * Delete this redirect
	 *
	 * @return void
	 */
	public function delete() {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_items WHERE id=%d", $this->id ) );
		do_action( 'redirection_redirect_deleted', $this );

		Red_Module::flush( $this->group_id );
	}

	/**
	 * Create a redirect with new details
	 *
	 * @param RedirectCreateDetails $details Redirect details.
	 * @return WP_Error|Red_Item
	 */
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
			$data['match_data'] = wp_json_encode( $data['match_data'], JSON_UNESCAPED_SLASHES );
		}

		// Create
		if ( $wpdb->insert( $wpdb->prefix . 'redirection_items', $data ) !== false ) {
			Red_Module::flush( $data['group_id'] );

			$redirect = self::get_by_id( $wpdb->insert_id );
			if ( $redirect !== false ) {
				do_action( 'redirection_redirect_updated', $wpdb->insert_id, $redirect );

				return $redirect;
			}

			return new WP_Error( 'redirect_create_failed', 'Unable to get newly added redirect' );
		}

		return new WP_Error( 'redirect_create_failed', 'Unable to add new redirect' );
	}

	/**
	 * Update the redirect with new details
	 *
	 * @param RedirectUpdateDetails $details Redirect details.
	 * @return WP_Error|true
	 */
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
			$this->load_from_data( $data );

			Red_Module::flush( $this->group_id );

			if ( $old_group !== $this->group_id && $old_group !== false ) {
				Red_Module::flush( $old_group );
			}

			return true;
		}

		return new WP_Error( 'redirect_create_failed', 'Unable to update redirect' );
	}

	/**
	 * Determine if a requested URL matches this URL
	 *
	 * @param string       $requested_url The URL being requested (decoded).
	 * @param string|false $original_url The URL being requested (not decoded).
	 * @return Red_Action|false true if matched, false otherwise
	 */
	public function get_match( $requested_url, $original_url = false ) {
		if ( ! $this->is_enabled() || $this->match === null || $this->source_flags === null || $this->action === null ) {
			// Don't do anything if Redirection is disabled or we don't have any of the objects
			return false;
		}

		if ( $original_url === false ) {
			$original_url = $requested_url;
		}

		$url = new Red_Url( $this->url );

		// Does the URL match? This may not be the case for regular expressions
		if ( ! $url->is_match( $requested_url, $this->source_flags ) ) {
			return false;
		}

		// Does the additional Red_Match logic also match? This provides dynamic checking of things like IP, cookies, etc
		$matched = $this->match->is_match( $requested_url );
		$target_url = false;

		// Does the action need a target (URL)?
		if ( $this->action->needs_target() ) {
			// Get the target from the action and the match status - some matches have a matched/unmatched target
			$target_url = $this->match->get_target_url( $original_url, $url->get_url(), $this->source_flags, $matched );
			if ( $target_url !== false ) {
				$target_url = Red_Url_Query::add_to_target( $target_url, $original_url, $this->source_flags );
			}

			// Allow plugins a look
			$target_url = apply_filters( 'redirection_url_target', $target_url, $url->get_url() );

			// Do we have still have a target?
			if ( ! $target_url ) {
				// No, return early and move on to the next redirect. This could be a matched/unmatched target that has no value
				return false;
			}

			// Set this in the action
			$this->action->set_target( $target_url );

			// Fire an action to let people know
			do_action( 'redirection_visit', $this, $original_url, $target_url );
		}

		// Return the action for processing if we have either matched or we have a target URL (possibly from an 'not matched' condition)
		if ( $matched || $target_url ) {
			return $this->action === null ? false : $this->action;
		}

		return false;
	}

	/**
	 * Register a visit against this redirect
	 *
	 * @param string      $url Full URL that is visited, including query parameters.
	 * @param string|true $target Target URL, if appropriate.
	 * @return void
	 */
	public function visit( $url, $target ) {
		global $wpdb;

		$options = Red_Options::get();

		// Update the counters
		$this->last_count++;

		if ( apply_filters( 'redirection_redirect_counter', $options['track_hits'], $url ) ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}redirection_items SET last_count=last_count+1, last_access=NOW() WHERE id=%d", $this->id ) );
		}

		if ( $target !== '' && $this->source_options !== null && $this->source_options->can_log() ) {
			if ( $target === true && $this->match !== null ) {
				$match_data = $this->match->get_data();
				$target = $this->action_type === 'pass' && is_array( $match_data ) && isset( $match_data['url'] ) ? $match_data['url'] : '';
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

			if ( $options['log_header'] === true ) {
				$details['request_data'] = [
					'headers' => Redirection_Request::get_request_headers(),
				];
			}

			Red_Redirect_Log::create( Redirection_Request::get_server(), $url, Redirection_Request::get_ip(), $details );
		}
	}

	/**
	 * Is this redirect enabled?
	 *
	 * @return boolean
	 */
	public function is_enabled() {
		return $this->status === 'enabled';
	}

	/**
	 * Reset this redirect
	 *
	 * @return void
	 */
	public function reset() {
		global $wpdb;

		$this->last_count  = 0;
		$this->last_access = 0;

		$update = [
			'last_count' => 0,
			'last_access' => '1970-01-01 00:00:00',
		];
		$where = [
			'id' => $this->id,
		];

		$wpdb->update( $wpdb->prefix . 'redirection_items', $update, $where );
	}

	/**
	 * Enable this redirect
	 *
	 * @return void
	 */
	public function enable() {
		global $wpdb;

		$this->status = 'enabled';
		$wpdb->update( $wpdb->prefix . 'redirection_items', [ 'status' => $this->status ], [ 'id' => $this->id ] );
		do_action( 'redirection_redirect_enabled', $this->id );
	}

	/**
	 * Disable this redirect
	 *
	 * @return void
	 */
	public function disable() {
		global $wpdb;

		$this->status = 'disabled';
		$wpdb->update( $wpdb->prefix . 'redirection_items', [ 'status' => $this->status ], [ 'id' => $this->id ] );
		do_action( 'redirection_redirect_disabled', $this->id );
	}

	/**
	 * Get the redirect ID
	 *
	 * @return integer
	 */
	public function get_id() {
		return intval( $this->id, 10 );
	}

	/**
	 * Get the redirect position
	 *
	 * @return integer
	 */
	public function get_position() {
		return intval( $this->position, 10 );
	}

	/**
	 * Get the redirect group ID
	 *
	 * @return integer
	 */
	public function get_group_id() {
		return intval( $this->group_id, 10 );
	}

	/**
	 * Get the redirect URL
	 *
	 * @return string
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * Get the match URL
	 *
	 * @return string
	 */
	public function get_match_url() {
		return $this->match_url;
	}

	/**
	 * Get match data
	 *
	 * @return RedirectMatchData|null
	 */
	public function get_match_data() {
		if ( $this->source_flags === null || $this->source_options === null ) {
			return null;
		}

		$source = $this->source_flags->get_json_with_defaults();
		$options = $this->source_options->get_json();

		$data = [];

		// @phpstan-ignore empty.variable
		if ( ! empty( $source ) ) {
			$data['source'] = $source;
		}

		if ( ! empty( $options ) ) {
			$data['options'] = $options;
		}

		// @phpstan-ignore greater.alwaysTrue
		if ( count( $data ) > 0 ) {
			return $data;
		}

		// @phpstan-ignore deadCode.unreachable
		return null;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title ? $this->title : '';
	}

	/**
	 * Get number of hits
	 *
	 * @return integer
	 */
	public function get_hits() {
		return intval( $this->last_count, 10 );
	}

	/**
	 * Get time of last hit
	 *
	 * @return integer
	 */
	public function get_last_hit() {
		return intval( $this->last_access, 10 );
	}

	/**
	 * Is this a regular expression?
	 *
	 * @return boolean
	 */
	public function is_regex() {
		return $this->regex ? true : false;
	}

	/**
	 * Does this redirect depend on dynamic match data? For example, it is checking a cookie or IP
	 *
	 * @return boolean
	 */
	public function is_dynamic() {
		if ( $this->match !== null ) {
			return $this->match->get_type() !== 'url';
		}

		return false;
	}

	/**
	 * Get match type
	 *
	 * @return string
	 */
	public function get_match_type() {
		return $this->match_type;
	}

	/**
	 * Get action type
	 *
	 * @return string
	 */
	public function get_action_type() {
		return $this->action_type;
	}

	/**
	 * Get action code
	 *
	 * @return integer
	 */
	public function get_action_code() {
		return intval( $this->action_code, 10 );
	}

	/**
	 * Get action data
	 *
	 * @return string
	 */
	public function get_action_data() {
		return $this->action_data ? $this->action_data : '';
	}

	/**
	 * Delete all redirects that match a filter
	 *
	 * @param RedirectFilterParams $params Filter parameters.
	 * @return boolean
	 */
	public static function delete_all( array $params ) {
		global $wpdb;

		$filters = new Red_Item_Filters( isset( $params['filterBy'] ) ? $params['filterBy'] : [] );
		$where = $filters->get_as_sql();

		// where is known
		// phpcs:ignore
		return $wpdb->query( "DELETE FROM {$wpdb->prefix}redirection_items $where" );
	}

	/**
	 * Reset all redirects that match a filter
	 *
	 * @param RedirectFilterParams $params Filter parameters.
	 * @return boolean
	 */
	public static function reset_all( array $params ) {
		global $wpdb;

		$filters = new Red_Item_Filters( isset( $params['filterBy'] ) ? $params['filterBy'] : [] );
		$where = $filters->get_as_sql();

		// where is known
		// phpcs:ignore
		return $wpdb->query( "UPDATE {$wpdb->prefix}redirection_items SET last_count=0, last_access='1970-01-01 00:00:00' $where" );
	}

	/**
	 * Set the status of all redirects that match the filter
	 *
	 * @param RedirectStatusAction $status Status to set.
	 * @param RedirectFilterParams $params Filter parameters.
	 * @return boolean
	 */
	public static function set_status_all( $status, array $params ) {
		global $wpdb;

		$filters = new Red_Item_Filters( isset( $params['filterBy'] ) ? $params['filterBy'] : [] );
		$where = $filters->get_as_sql();

		// where is known
		// phpcs:ignore
		return $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}redirection_items SET status=%s $where", $status === 'enable' ? 'enabled' : 'disabled' ) );
	}

	/**
	 * Get a filtered list of redirects
	 *
	 * @param array<string, mixed> $params Filter parameters.
	 * @return array{total: int, items: list<RedirectJson>}
	 */
	public static function get_filtered( array $params ) {
		global $wpdb;

		$orderby = 'id';
		$direction = 'DESC';
		$limit = self::DEFAULT_PER_PAGE;
		$offset = 0;
		$where = '';

		if ( isset( $params['orderby'] ) && in_array( $params['orderby'], [ 'source', 'last_count', 'last_access', 'position' ], true ) ) {
			$orderby = $params['orderby'];

			if ( $orderby === 'source' ) {
				$orderby = 'url';
			}
		}

		if ( isset( $params['direction'] ) && in_array( $params['direction'], [ 'asc', 'desc' ], true ) ) {
			$direction = strtoupper( $params['direction'] );
		}

		if ( isset( $params['filterBy'] ) && is_array( $params['filterBy'] ) ) {
			$filters = new Red_Item_Filters( $params['filterBy'] );
			$where = $filters->get_as_sql();
		}

		if ( isset( $params['per_page'] ) ) {
			$limit = intval( $params['per_page'], 10 );
			$limit = min( self::MAX_PER_PAGE, $limit );
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
		$items = [];

		foreach ( $rows as $row ) {
			$group = new Red_Item( $row );
			$items[] = $group->to_json();
		}

		return [
			'items' => $items,
			'total' => intval( $total_items, 10 ),
		];
	}

	/**
	 * Convert the redirect to JSON
	 *
	 * @return RedirectJson
	 */
	public function to_json() {
		return [
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
		];
	}

	/**
	 * Convert the redirect to SQL
	 *
	 * @return RedirectJson
	 */
	public function to_sql() {
		$json = $this->to_json();
		$action_data = null;

		if ( $this->match !== null ) {
			$data = $this->match->get_data();

			if ( $data !== null ) {
				$action_data = $this->match->save( $data, false );

				if ( is_array( $action_data ) ) {
					// phpcs:ignore
					$action_data = serialize( $action_data );
				}
			}
		}

		return array_merge(
			$json,
			[
				'match_data'  => wp_json_encode( $this->get_match_data(), JSON_UNESCAPED_SLASHES ),
				'action_data' => $action_data,
				'last_access' => gmdate( 'Y-m-d H:i:s', $this->last_access ),
				'status'      => $this->is_enabled() ? 'enabled' : 'disabled',
			]
		);
	}
}
