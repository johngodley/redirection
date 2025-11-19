<?php

/**
 * @phpstan-import-type LogJson from Red_Log
 * @phpstan-import-type LogFilterParams from Red_Log
 * @phpstan-import-type LogGetParams from Red_Log
 * @phpstan-type RedirectCsvRow object{
 *   created: string,
 *   url: string,
 *   sent_to: string,
 *   ip: string,
 *   referrer: string,
 *   agent: string
 * }
 * @phpstan-type RedirectLogJson array{
 *   id: int,
 *   created: string,
 *   created_time: string,
 *   url: string,
 *   agent: string,
 *   referrer: string,
 *   domain: string,
 *   ip: string,
 *   http_code: int,
 *   request_method: string,
 *   request_data: mixed,
 *   sent_to: string,
 *   redirection_id: int,
 *   redirect_by_slug: string,
 *   redirect_by: string
 * }
 *
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
	 * @param \wpdb $wpdb WPDB object.
	 * @return string
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
	 * @param array<string, mixed> $details Other log details.
	 * @return int|false Log ID, or false
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
	 * @phpstan-param LogFilterParams & array{target?: string, redirect_by?: string} $filter
	 * @phpstan-return list<string>
	 * @return array
	 */
	protected static function get_query_filter( array $filter ) {
		global $wpdb;

		$where = parent::get_query_filter( $filter );

		/** @var array{target?: string, redirect_by?: string} $filter */
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
	 * @return array<int, string>
	 */
	public static function get_csv_header() {
		return [ 'date', 'source', 'target', 'ip', 'referrer', 'agent' ];
	}

	/**
	 * Get the CSV row for this log object
	 *
	 * @param object $row Log row.
	 * @phpstan-param object $row
	 * @return array<int, string>
	 */
	public static function get_csv_row( $row ) {
		/** @var RedirectCsvRow $row */
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
	 * @phpstan-return RedirectLogJson
	 * @return array<string, mixed>
	 */
	public function to_json() {
		return array_merge(
			parent::to_json(),
			[
				'sent_to' => $this->sent_to,
				'redirection_id' => intval( $this->redirection_id, 10 ),
				'redirect_by_slug' => $this->redirect_by,
				'redirect_by' => $this->get_redirect_name( $this->redirect_by === null ? '' : $this->redirect_by ),
			]
		);
	}
}
