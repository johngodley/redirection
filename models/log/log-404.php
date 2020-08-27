<?php

/**
 * 404 error logging. Extends the base log class with specifics for 404s
 */
class Red_404_Log extends Red_Log {
	/**
	 * Get's the table name for this log object
	 *
	 * @param Object $wpdb WPDB object.
	 * @return String
	 */
	protected static function get_table_name( $wpdb ) {
		return "{$wpdb->prefix}redirection_404";
	}

	/**
	 * Create a 404 log entry
	 *
	 * @param string $domain Domain name of request.
	 * @param string $url URL of request.
	 * @param string $ip IP of client.
	 * @param array  $details Other log details.
	 * @return integer|false Log ID, or false
	 */
	public static function create( $domain, $url, $ip, $details ) {
		global $wpdb;

		$insert = static::sanitize_create( $domain, $url, $ip, $details );
		$insert = apply_filters( 'redirection_404_data', $insert );

		if ( $insert ) {
			do_action( 'redirection_404', $insert );

			$wpdb->insert( $wpdb->prefix . 'redirection_404', $insert );
			if ( $wpdb->insert_id ) {
				return $wpdb->insert_id;
			}
		}

		return false;
	}

	/**
	 * Get the CSV filename for this log object
	 *
	 * @return string
	 */
	public static function get_csv_filename() {
		return 'redirection-404';
	}

	/**
	 * Get the CSV headers for this log object
	 *
	 * @return array
	 */
	public static function get_csv_header() {
		return [ 'date', 'source', 'ip', 'referrer', 'useragent' ];
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
			$row->ip,
			$row->referrer,
			$row->agent,
		];
	}
}

// phpcs:ignore
class RE_404 {
	public static function create( $url, $agent, $ip, $referrer ) {
		_deprecated_function( __FUNCTION__, '4.6', 'Red_404_Log::create( $domain, $url, $ip, $details )' );

		return Red_404_Log::create( Redirection_Request::get_server(), $url, $ip, [
			'agent' => $agent,
			'referrer' => $referrer,
			'request_method' => Redirection_Request::get_request_method(),
		] );
	}
}
