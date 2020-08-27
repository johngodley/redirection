<?php

/**
 * Redirect logging. Extends the base log class with specifics for redirects
 */
class Red_Redirect_Log extends Red_Log {
	/**
	 * The redirect associated with this log entry.
	 *
	 * @var integer
	 */
	protected $redirection_id = 0;

	/**
	 * The URL the client was redirected to.
	 *
	 * @var string
	 */
	protected $sent_to = '';

	/**
	 * Who redirected this URL?
	 *
	 * @var string
	 */
	protected $redirect_by = '';

	/**
	 * Get's the table name for this log object
	 *
	 * @param Object $wpdb WPDB object.
	 * @return String
	 */
	protected static function get_table_name( $wpdb ) {
		return "{$wpdb->prefix}redirection_logs";
	}

	/**
	 * Create a redirect log entry
	 *
	 * @param string $domain Domain name of request.
	 * @param string $url URL of request.
	 * @param string $ip IP of client.
	 * @param array  $details Other log details.
	 * @return integer|false Log ID, or false
	 */
	public static function create( $domain, $url, $ip, $details ) {
		global $wpdb;

		$insert = self::sanitize_create( $domain, $url, $ip, $details );
		$insert['redirection_id'] = 0;

		if ( isset( $details['redirect_id'] ) ) {
			$insert['redirection_id'] = intval( $details['redirect_id'], 10 );
		}

		if ( isset( $details['target'] ) ) {
			$insert['sent_to'] = $details['target'];
		}

		if ( isset( $details['redirect_by'] ) ) {
			$insert['redirect_by'] = strtolower( substr( $details['redirect_by'], 0, 50 ) );
		}

		$insert = apply_filters( 'redirection_log_data', $insert );
		if ( $insert ) {
			do_action( 'redirection_log', $insert );

			$wpdb->insert( $wpdb->prefix . 'redirection_logs', $insert );
			if ( $wpdb->insert_id ) {
				return $wpdb->insert_id;
			}
		}

		return false;
	}

	/**
	 * Get query filters as a SQL `WHERE` statement. SQL will be sanitized
	 *
	 * @param array $filter Array of filter params.
	 * @return array
	 */
	protected static function get_query_filter( array $filter ) {
		global $wpdb;

		$where = parent::get_query_filter( $filter );

		if ( isset( $filter['target'] ) ) {
			$where[] = $wpdb->prepare( 'sent_to LIKE %s', '%' . $wpdb->esc_like( trim( $filter['target'] ) ) . '%' );
		}

		if ( isset( $filter['redirect_by'] ) ) {
			$where[] = $wpdb->prepare( 'redirect_by = %s', $filter['redirect_by'] );
		}

		return $where;
	}

	/**
	 * Get the CSV filename for this log object
	 *
	 * @return string
	 */
	public static function get_csv_filename() {
		return 'redirection-log';
	}

	/**
	 * Get the CSV headers for this log object
	 *
	 * @return array
	 */
	public static function get_csv_header() {
		return [ 'date', 'source', 'target', 'ip', 'referrer', 'agent' ];
	}

	/**
	 * Get the CSV headers for this log object
	 *
	 * @param object $row Log row.
	 * @return array
	 */
	public static function get_csv_row( $row ) {
		return [
			$row->created,
			$row->url,
			$row->sent_to,
			$row->ip,
			$row->referrer,
			$row->agent,
		];
	}

	/**
	 * Get a displayable name for the originator of the redirect.
	 *
	 * @param string $agent Redirect agent.
	 * @return string
	 */
	private function get_redirect_name( $agent ) {
		// phpcs:ignore
		if ( $agent === 'wordpress' ) {
			return 'WordPress';
		}

		return ucwords( $agent );
	}

	/**
	 * Convert a log entry to JSON
	 *
	 * @return Array
	 */
	public function to_json() {
		return array_merge( parent::to_json(), [
			'sent_to' => $this->sent_to,
			'redirection_id' => intval( $this->redirection_id, 10 ),
			'redirect_by_slug' => $this->redirect_by,
			'redirect_by' => $this->get_redirect_name( $this->redirect_by ),
		] );
	}
}

// phpcs:ignore
class RE_Log {
	public static function create( $url, $target, $agent, $ip, $referrer, $extra = array() ) {
		_deprecated_function( __FUNCTION__, '4.6', 'Red_Redirect_Log::create( $domain, $url, $ip, $details )' );

		return Red_Redirect_Log::create( Redirection_Request::get_server(), $url, $ip, array_merge( [
			'agent' => $agent,
			'referrer' => $referrer,
			'target' => $target,
			'request_method' => Redirection_Request::get_request_method(),
		], $extra ) );
	}
}
