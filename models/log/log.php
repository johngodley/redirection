<?php

require_once __DIR__ . '/log-404.php';
require_once __DIR__ . '/log-redirect.php';

/**
 * Base log class
 */
abstract class Red_Log {
	const MAX_IP_LENGTH = 45;
	const MAX_DOMAIN_LENGTH = 255;
	const MAX_URL_LENGTH = 2000;
	const MAX_AGENT_LENGTH = 255;
	const MAX_REFERRER_LENGTH = 255;

	/**
	 * Supported HTTP methods
	 *
	 * @var array
	 */
	protected static $supported_methods = [ 'GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH' ];

	/**
	 * Log ID
	 *
	 * @var integer
	 */
	protected $id = 0;

	/**
	 * Created date time
	 *
	 * @var integer
	 */
	protected $created = 0;

	/**
	 * Requested URL
	 *
	 * @var string
	 */
	protected $url = '';

	/**
	 * Client user agent
	 *
	 * @var string
	 */
	protected $agent = '';

	/**
	 * Client referrer
	 *
	 * @var string
	 */
	protected $referrer = '';

	/**
	 * Client IP
	 *
	 * @var string
	 */
	protected $ip = '';

	/**
	 * Requested domain
	 *
	 * @var string
	 */
	protected $domain = '';

	/**
	 * Response HTTP code
	 *
	 * @var integer
	 */
	protected $http_code = 0;

	/**
	 * Request method
	 *
	 * @var string
	 */
	protected $request_method = '';

	/**
	 * Additional request data
	 *
	 * @var string
	 */
	protected $request_data = '';

	/**
	 * Constructor
	 *
	 * @param array $values Array of log values.
	 */
	final public function __construct( array $values ) {
		foreach ( $values as $key => $value ) {
			$this->$key = $value;
		}

		if ( isset( $values['created'] ) ) {
			$converted = mysql2date( 'U', $values['created'] );

			if ( $converted ) {
				$this->created = intval( $converted, 10 );
			}
		}
	}

	/**
	 * Get's the table name for this log object
	 *
	 * @param Object $wpdb WPDB object.
	 * @return string
	 */
	protected static function get_table_name( $wpdb ) {
		return '';
	}

	/**
	 * Get a log item by ID
	 *
	 * @param integer $id Log ID.
	 * @return Red_Log|false
	 */
	public static function get_by_id( $id ) {
		global $wpdb;

		$table = static::get_table_name( $wpdb );

		// Table name is internally generated.
		// phpcs:ignore
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id=%d", $id ), ARRAY_A );
		if ( $row ) {
			return new static( $row );
		}

		return false;
	}

	/**
	 * Delete a log entry
	 *
	 * @param integer $id Log ID.
	 * @return integer|false
	 */
	public static function delete( $id ) {
		global $wpdb;

		return $wpdb->delete( static::get_table_name( $wpdb ), [ 'id' => $id ] );
	}

	/**
	 * Delete all matching log entries
	 *
	 * @param array $params Array of filter parameters.
	 * @return integer|false
	 */
	public static function delete_all( array $params = [] ) {
		global $wpdb;

		$query = self::get_query( $params );
		$table = static::get_table_name( $wpdb );

		$sql = "DELETE FROM {$table} {$query['where']}";

		// phpcs:ignore
		return $wpdb->query( $sql );
	}

	/**
	 * Convert a log entry to JSON
	 *
	 * @return Array
	 */
	public function to_json() {
		return [
			'id' => intval( $this->id, 10 ),
			'created' => date_i18n( get_option( 'date_format' ), $this->created ),
			'created_time' => gmdate( get_option( 'time_format' ), $this->created ),
			'url' => $this->url,
			'agent' => $this->agent,
			'referrer' => $this->referrer,
			'domain' => $this->domain,
			'ip' => $this->ip,
			'http_code' => intval( $this->http_code, 10 ),
			'request_method' => $this->request_method,
			'request_data' => $this->request_data ? json_decode( $this->request_data, true ) : '',
		];
	}

	/**
	 * Get filtered log entries
	 *
	 * @param array $params Filters.
	 * @return Array{items: Array, total: integer}
	 */
	public static function get_filtered( array $params ) {
		global $wpdb;

		$query = self::get_query( $params );
		$table = static::get_table_name( $wpdb );

		$sql = "SELECT * FROM {$table} {$query['where']}";

		// Already escaped
		// phpcs:ignore
		$sql .= $wpdb->prepare( ' ORDER BY ' . $query['orderby'] . ' ' . $query['direction'] . ' LIMIT %d,%d', $query['offset'], $query['limit'] );

		// Already escaped
		// phpcs:ignore
		$rows = $wpdb->get_results( $sql, ARRAY_A );

		// Already escaped
		// phpcs:ignore
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM $table " . $query['where'] );
		$items = array();

		foreach ( $rows as $row ) {
			$item = new static( $row );
			$items[] = $item->to_json();
		}

		return [
			'items' => $items,
			'total' => intval( $total_items, 10 ),
		];
	}

	/**
	 * Get grouped log entries
	 *
	 * @param string $group Group type.
	 * @param array  $params Filter params.
	 * @return Array{items: mixed, total: integer}
	 */
	public static function get_grouped( $group, array $params ) {
		global $wpdb;

		$table = static::get_table_name( $wpdb );
		$query = self::get_query( $params );

		if ( ! in_array( $group, [ 'ip', 'url', 'agent' ], true ) ) {
			$group = 'url';
		}

		// Already escaped
		// phpcs:ignore
		$sql = $wpdb->prepare( "SELECT COUNT(*) as count,$group FROM {$table} {$query['where']} GROUP BY $group ORDER BY count {$query['direction']}, $group LIMIT %d,%d", $query['offset'], $query['limit'] );

		// Already escaped
		// phpcs:ignore
		$rows = $wpdb->get_results( $sql );

		// Already escaped
		// phpcs:ignore
		$total_items = $wpdb->get_var( "SELECT COUNT(DISTINCT $group) FROM {$table} {$query['where']}" );

		foreach ( $rows as $row ) {
			$row->count = intval( $row->count, 10 );

			if ( isset( $row->url ) ) {
				$row->id = $row->url;
			} elseif ( isset( $row->ip ) ) {
				$row->id = $row->ip;
			} elseif ( isset( $row->agent ) ) {
				$row->id = $row->agent;
			}
		}

		return array(
			'items' => $rows,
			'total' => intval( $total_items, 10 ),
		);
	}

	/**
	 * Convert a set of filters to a SQL query.
	 *
	 * @param array $params Filters.
	 * @return Array{orderby: string, direction: string, limit: integer, offset: integer, where: string}
	 */
	public static function get_query( array $params ) {
		$query = [
			'orderby' => 'id',
			'direction' => 'DESC',
			'limit' => RED_DEFAULT_PER_PAGE,
			'offset' => 0,
			'where' => '',
		];

		if ( isset( $params['orderby'] ) && in_array( $params['orderby'], array( 'ip', 'url' ), true ) ) {
			$query['orderby'] = $params['orderby'];
		}

		if ( isset( $params['direction'] ) && in_array( strtoupper( $params['direction'] ), array( 'ASC', 'DESC' ), true ) ) {
			$query['direction'] = strtoupper( $params['direction'] );
		}

		if ( isset( $params['per_page'] ) ) {
			$limit = intval( $params['per_page'], 10 );
			if ( $limit >= 5 && $limit <= RED_MAX_PER_PAGE ) {
				$query['limit'] = $limit;
			}
		}

		if ( isset( $params['page'] ) ) {
			$offset = intval( $params['page'], 10 );

			if ( $offset >= 0 ) {
				$query['offset'] = $offset * $query['limit'];
			}
		}

		if ( isset( $params['filterBy'] ) && is_array( $params['filterBy'] ) ) {
			$where = static::get_query_filter( $params['filterBy'] );

			if ( count( $where ) > 0 ) {
				$query['where'] = 'WHERE ' . implode( ' AND ', $where );
			}
		}

		return $query;
	}

	/**
	 * Get query filters as a SQL `WHERE` statement. SQL will be sanitized
	 *
	 * @param array $filter Array of filter params.
	 * @return array
	 */
	protected static function get_query_filter( array $filter ) {
		global $wpdb;

		$where = [];

		if ( isset( $filter['ip'] ) ) {
			// phpcs:ignore
			$ip = @inet_pton( trim( $filter['ip'] ) );

			if ( $ip !== false ) {
				// Full IP match
				// phpcs:ignore
				$ip = @inet_ntop( $ip );  // Convert back to string
				$where[] = $wpdb->prepare( 'ip = %s', $ip );
			} else {
				// Partial IP match
				$where[] = $wpdb->prepare( 'ip LIKE %s', '%' . $wpdb->esc_like( trim( $filter['ip'] ) ) . '%' );
			}
		}

		if ( isset( $filter['domain'] ) ) {
			$where[] = $wpdb->prepare( 'domain LIKE %s', '%' . $wpdb->esc_like( trim( $filter['domain'] ) ) . '%' );
		}

		if ( isset( $filter['url-exact'] ) ) {
			$where[] = $wpdb->prepare( 'url = %s', $filter['url-exact'] );
		} elseif ( isset( $filter['url'] ) ) {
			$where[] = $wpdb->prepare( 'url LIKE %s', '%' . $wpdb->esc_like( trim( $filter['url'] ) ) . '%' );
		}

		if ( isset( $filter['referrer'] ) ) {
			$where[] = $wpdb->prepare( 'referrer LIKE %s', '%' . $wpdb->esc_like( trim( $filter['referrer'] ) ) . '%' );
		}

		if ( isset( $filter['agent'] ) ) {
			$where[] = $wpdb->prepare( 'agent LIKE %s', '%' . $wpdb->esc_like( trim( $filter['agent'] ) ) . '%' );
		}

		if ( isset( $filter['http'] ) ) {
			$where[] = $wpdb->prepare( 'http_code = %d', $filter['http'] );
		}

		if ( isset( $filter['method'] ) && in_array( strtoupper( $filter['method'] ), static::$supported_methods, true ) ) {
			$where[] = $wpdb->prepare( 'request_method = %s', strtoupper( $filter['method'] ) );
		}

		return $where;
	}

	/**
	 * Sanitize a new log entry
	 *
	 * @param string $domain Requested Domain.
	 * @param string $url Requested URL.
	 * @param string $ip Client IP. This is assumed to be a valid IP and won't be checked.
	 * @param array  $details Extra log details.
	 * @return array
	 */
	protected static function sanitize_create( $domain, $url, $ip, array $details = [] ) {
		$insert = [
			'url' => substr( sanitize_text_field( $url ), 0, self::MAX_URL_LENGTH ),
			'domain' => substr( sanitize_text_field( $domain ), 0, self::MAX_DOMAIN_LENGTH ),
			'ip' => substr( sanitize_text_field( $ip ), 0, self::MAX_IP_LENGTH ),
			'created' => current_time( 'mysql' ),
		];

		// Unfortunatley these names dont match up
		$allowed = [
			'agent' => 'agent',
			'referrer' => 'referrer',
			'request_method' => 'request_method',
			'http_code' => 'http_code',
			'request_data' => 'request_data',
		];

		foreach ( $allowed as $name => $replace ) {
			if ( ! empty( $details[ $name ] ) ) {
				$insert[ $replace ] = $details[ $name ];
			}
		}

		if ( isset( $insert['agent'] ) ) {
			$insert['agent'] = substr( sanitize_text_field( $insert['agent'] ), 0, self::MAX_AGENT_LENGTH );
		}

		if ( isset( $insert['referrer'] ) ) {
			$insert['referrer'] = substr( sanitize_text_field( $insert['referrer'] ), 0, self::MAX_REFERRER_LENGTH );
		}

		if ( isset( $insert['request_data'] ) ) {
			$insert['request_data'] = wp_json_encode( $insert['request_data'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK );
		}

		if ( isset( $insert['http_code'] ) ) {
			$insert['http_code'] = intval( $insert['http_code'], 10 );
		}

		if ( isset( $insert['request_method'] ) ) {
			$insert['request_method'] = strtoupper( sanitize_text_field( $insert['request_method'] ) );

			if ( ! in_array( $insert['request_method'], static::$supported_methods, true ) ) {
				$insert['request_method'] = '';
			}
		}

		return $insert;
	}

	/**
	 * Get the CSV filename for this log object
	 *
	 * @return string
	 */
	public static function get_csv_filename() {
		return '';
	}

	/**
	 * Get the CSV headers for this log object
	 *
	 * @return array
	 */
	public static function get_csv_header() {
		return [];
	}

	/**
	 * Get the CSV headers for this log object
	 *
	 * @param object $row Log row.
	 * @return array
	 */
	public static function get_csv_row( $row ) {
		return [];
	}

	/**
	 * Export the log entry to CSV
	 *
	 * @return void
	 */
	public static function export_to_csv() {
		$filename = static::get_csv_filename() . '-' . date_i18n( get_option( 'date_format' ) ) . '.csv';

		header( 'Content-Type: text/csv' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		// phpcs:ignore
		$stdout = fopen( 'php://output', 'w' );
		fputcsv( $stdout, static::get_csv_header() );

		global $wpdb;

		$table = static::get_table_name( $wpdb );
		// phpcs:ignore
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
		$exported = 0;

		$limit = 100;

		while ( $exported < $total_items ) {
			// phpcs:ignore
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table LIMIT %d,%d", $exported, $limit ) );
			$exported += count( $rows );

			foreach ( $rows as $row ) {
				$csv = static::get_csv_row( $row );
				fputcsv( $stdout, $csv );
			}

			if ( count( $rows ) < $limit ) {
				break;
			}
		}
	}
}
