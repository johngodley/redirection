<?php

require_once __DIR__ . '/log-404.php';
require_once __DIR__ . '/log-redirect.php';

/**
 * Base log class
 *
 * @phpstan-type LogDbRow array{
 *   id?: int|string,
 *   created?: string,
 *   url?: string,
 *   agent?: string|null,
 *   referrer?: string|null,
 *   domain?: string|null,
 *   ip?: string|null,
 *   http_code?: int|string|null,
 *   request_method?: string|null,
 *   request_data?: mixed,
 *   redirection_id?: int|string,
 *   sent_to?: string,
 *   redirect_by?: string|null
 * }
 * @phpstan-type LogJson array{
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
 *   request_data: mixed
 * }
 * @phpstan-type LogQuery array{
 *   orderby: string,
 *   direction: string,
 *   limit: int,
 *   offset: int,
 *   where: string
 * }
 * @phpstan-type LogFilterParams array{
 *   ip?: string,
 *   domain?: string,
 *   'url-exact'?: string,
 *   url?: string,
 *   referrer?: string,
 *   agent?: string,
 *   http?: int,
 *   method?: string
 * }
 * @phpstan-type LogGetParams array{
 *   orderby?: 'ip'|'url',
 *   direction?: 'ASC'|'DESC',
 *   per_page?: int,
 *   page?: int,
 *   filterBy?: LogFilterParams,
 *   groupBy?: 'ip'|'url'|'agent',
 *   items?: array<int, string|int>,
 *   global?: bool
 * }
 */
abstract class Red_Log {
	const MAX_IP_LENGTH = 45;
	const MAX_DOMAIN_LENGTH = 255;
	const MAX_URL_LENGTH = 2000;
	const DEFAULT_PER_PAGE = 25;
	const MAX_PER_PAGE = 200;
	const MAX_AGENT_LENGTH = 255;
	const MAX_REFERRER_LENGTH = 255;

	/**
	 * Supported HTTP methods
	 *
	 * @phpstan-var list<string>
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
	 * @phpstan-param LogDbRow $values
	 * @param array<string, mixed> $values Array of log values.
	 */
	final public function __construct( $values ) {
		if ( isset( $values['id'] ) ) {
			$this->id = intval( $values['id'], 10 );
		}

		if ( isset( $values['created'] ) && is_string( $values['created'] ) ) {
			$converted = mysql2date( 'U', $values['created'] );

			if ( $converted !== false ) {
				$this->created = intval( $converted, 10 );
			}
		}

		if ( isset( $values['url'] ) && is_string( $values['url'] ) ) {
			$this->url = $values['url'];
		}

		if ( isset( $values['agent'] ) && is_string( $values['agent'] ) ) {
			$this->agent = $values['agent'];
		}

		if ( isset( $values['referrer'] ) && is_string( $values['referrer'] ) ) {
			$this->referrer = $values['referrer'];
		}

		if ( isset( $values['ip'] ) && is_string( $values['ip'] ) ) {
			$this->ip = $values['ip'];
		}

		if ( isset( $values['domain'] ) && is_string( $values['domain'] ) ) {
			$this->domain = $values['domain'];
		}

		if ( isset( $values['http_code'] ) ) {
			$this->http_code = intval( $values['http_code'], 10 );
		}

		if ( isset( $values['request_method'] ) && is_string( $values['request_method'] ) ) {
			$this->request_method = $values['request_method'];
		}

		if ( isset( $values['request_data'] ) && is_string( $values['request_data'] ) ) {
			$this->request_data = $values['request_data'];
		}

		if ( $this instanceof Red_Redirect_Log ) {
			if ( isset( $values['redirection_id'] ) ) {
				$this->redirection_id = intval( $values['redirection_id'], 10 );
			}

			if ( isset( $values['sent_to'] ) && is_string( $values['sent_to'] ) ) {
				$this->sent_to = $values['sent_to'];
			}

			if ( isset( $values['redirect_by'] ) && is_string( $values['redirect_by'] ) ) {
				$this->redirect_by = $values['redirect_by'];
			}
		}
	}

	/**
	 * Get's the table name for this log object
	 *
	 * @param \wpdb $wpdb WPDB object.
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
	 * @phpstan-param LogGetParams $params Array of filter parameters.
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
	 * @phpstan-return LogJson
	 * @return array
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
	 * @param array<string, mixed> $params Filters.
	 * @phpstan-return array{items: list<LogJson>, total: int}
	 * @return array
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

		/** @var list<LogJson> $items */
		return [
			'items' => $items,
			'total' => intval( $total_items, 10 ),
		];
	}

	/**
	 * Get grouped log entries
	 *
	 * @param string $group Group type ('ip'|'url'|'agent').
	 * @param array<string, mixed>  $params Filter params.
	 * @phpstan-return array{items: list<object>, total: int}
	 * @return array
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
			$row->id = isset( $row->{ $group } ) ? $row->{ $group } : '';
		}

		/** @var list<object> $rows */
		return array(
			'items' => $rows,
			'total' => intval( $total_items, 10 ),
		);
	}

	/**
	 * Convert a set of filters to a SQL query.
	 *
	 * @param array<string, mixed> $params Filters.
	 * @phpstan-return LogQuery
	 * @return array
	 */
	public static function get_query( array $params ) {
		$query = [
			'orderby' => 'id',
			'direction' => 'DESC',
			'limit' => self::DEFAULT_PER_PAGE,
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
			if ( $limit >= 5 && $limit <= self::MAX_PER_PAGE ) {
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
	 * @phpstan-param LogFilterParams $filter Array of filter params.
	 * @phpstan-return list<string>
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
			$agent = trim( $filter['agent'] );

			if ( empty( $agent ) ) {
				$where[] = $wpdb->prepare( 'agent = %s', $agent );
			} else {
				$where[] = $wpdb->prepare( 'agent LIKE %s', '%' . $wpdb->esc_like( $agent ) . '%' );
			}
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
	 * @param array<string, mixed>  $details Extra log details.
	 * @return array<string, mixed>
	 */
	protected static function sanitize_create( $domain, $url, $ip, array $details = [] ) {
		$url = urldecode( $url );
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
	 * @return array<int, string>
	 */
	public static function get_csv_header() {
		return [];
	}

	/**
	 * Get the CSV row for this log object
	 *
	 * @param object $row Log row.
	 * @return array<int, string|int>
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
		$data = static::get_export_data( 'csv' );
		if ( $data === false ) {
			return;
		}

		$filename = static::get_csv_filename() . '-' . date_i18n( get_option( 'date_format' ) ) . '.csv';

		header( 'Content-Type: text/csv' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		echo $data;
	}

	/**
	 * @param 'csv'|'json' $format
	 * @phpstan-param LogGetParams $params
	 * @param array<string, mixed> $params
	 * @param array<int, string> $display_selected
	 * @return string|false
	 */
	public static function get_export_data( $format, array $params = [], array $display_selected = [] ) {
		if ( self::should_export_all_rows( $params, $display_selected ) ) {
			if ( $format === 'csv' ) {
				return self::get_export_csv_data();
			}

			if ( $format === 'json' ) {
				return self::get_export_json_data();
			}

			return false;
		}

		$rows = self::get_export_rows( $params );

		if ( $format === 'csv' ) {
			return self::get_custom_export_csv_data( $rows, $display_selected, $params );
		}

		if ( $format === 'json' ) {
			return self::get_custom_export_json_data( $rows, $display_selected, $params );
		}

		return false;
	}

	/**
	 * @phpstan-param LogGetParams $params
	 * @param array<string, mixed> $params
	 * @return int
	 */
	public static function get_export_total( array $params = [] ) {
		global $wpdb;

		if ( isset( $params['items'] ) && is_array( $params['items'] ) && count( $params['items'] ) > 0 ) {
			return count( self::get_export_rows( $params ) );
		}

		$safe_group_by = self::get_safe_group_by( $params );
		if ( $safe_group_by !== false ) {
			$table = static::get_table_name( $wpdb );
			$query = self::get_query( $params );

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $safe_group_by is restricted by get_safe_group_by() to a known column name.
			$total = $wpdb->get_var( "SELECT COUNT(DISTINCT $safe_group_by) FROM $table " . $query['where'] );

			return intval( $total, 10 );
		}

		$table = static::get_table_name( $wpdb );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );

		return intval( $total, 10 );
	}

	/**
	 * @param 'csv'|'json' $format
	 * @param int $limit
	 * @return array{total: int, estimated_size: int}
	 */
	public static function get_export_preview( $format, $limit = 20 ) {
		$total_items = static::get_export_total();
		$rows = self::get_export_array_rows( $limit );

		return [
			'total' => $total_items,
			'estimated_size' => self::get_export_estimated_size( $format, $total_items, $rows ),
		];
	}

	/**
	 * @phpstan-return array<int, LogDbRow>
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_export_bundle_rows() {
		return self::get_export_array_rows();
	}

	/**
	 * @return string|false
	 */
	private static function get_export_csv_data() {
		return self::get_export_csv_data_for_rows( self::get_export_object_rows() );
	}

	/**
	 * @param array<int, object> $rows
	 * @return string|false
	 */
	private static function get_export_csv_data_for_rows( array $rows ) {
		$sanitizer = new \Redirection\ImportExport\Sanitizer\CsvSanitizer();

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Temporary in-memory export buffer
		$stdout = fopen( 'php://temp', 'w+' );
		if ( $stdout === false ) {
			return false;
		}

		fputcsv( $stdout, static::get_csv_header() );

		foreach ( $rows as $row ) {
			fputcsv( $stdout, array_map( [ $sanitizer, 'escape' ], static::get_csv_row( $row ) ) );
		}

		rewind( $stdout );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread -- Temporary in-memory export buffer
		$data = stream_get_contents( $stdout );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Temporary in-memory export buffer
		fclose( $stdout );

		return $data === false ? false : $data;
	}

	/**
	 * @return string|false
	 */
	private static function get_export_json_data() {
		return self::get_export_json_data_for_rows( self::get_export_array_rows() );
	}

	/**
	 * @param array<int, array<string, scalar|null>> $rows
	 * @param array<int, string> $display_selected
	 * @phpstan-param LogGetParams $params
	 * @param array<string, mixed> $params
	 * @return string|false
	 */
	private static function get_custom_export_json_data( array $rows, array $display_selected, array $params = [] ) {
		$items = [];
		$fields = self::get_export_fields( $display_selected, $params );

		foreach ( $rows as $row ) {
			$items[] = self::filter_export_row( static::map_export_row( $row ), $fields );
		}

		$data = wp_json_encode( $items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

		return is_string( $data ) ? $data . PHP_EOL : false;
	}

	/**
	 * @phpstan-param array<int, LogDbRow> $rows
	 * @param array<int, array<string, mixed>> $rows
	 * @return string|false
	 */
	private static function get_export_json_data_for_rows( array $rows ) {
		$items = [];

		foreach ( $rows as $row ) {
			$item = new static( $row );
			$items[] = $item->to_json();
		}

		$data = wp_json_encode( $items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

		return is_string( $data ) ? $data . PHP_EOL : false;
	}

	/**
	 * @param array<int, array<string, scalar|null>> $rows
	 * @param array<int, string> $display_selected
	 * @phpstan-param LogGetParams $params
	 * @param array<string, mixed> $params
	 * @return string|false
	 */
	private static function get_custom_export_csv_data( array $rows, array $display_selected, array $params = [] ) {
		$fields = self::get_export_fields( $display_selected, $params );
		$sanitizer = new \Redirection\ImportExport\Sanitizer\CsvSanitizer();

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Temporary in-memory export buffer
		$stdout = fopen( 'php://temp', 'w+' );
		if ( $stdout === false ) {
			return false;
		}

		fputcsv( $stdout, array_map( [ static::class, 'get_export_field_label' ], $fields ) );

		foreach ( $rows as $row ) {
			$mapped_row = self::filter_export_row( static::map_export_row( $row ), $fields );
			fputcsv( $stdout, array_map( [ $sanitizer, 'escape' ], array_values( $mapped_row ) ) );
		}

		rewind( $stdout );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread -- Temporary in-memory export buffer
		$data = stream_get_contents( $stdout );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Temporary in-memory export buffer
		fclose( $stdout );

		return $data === false ? false : $data;
	}

	/**
	 * @return array<int, object>
	 */
	private static function get_export_object_rows( ?int $max_items = null ) {
		global $wpdb;

		$table = static::get_table_name( $wpdb );
		$total_items = static::get_export_total();
		$target_total = $max_items === null ? $total_items : min( $total_items, intval( $max_items, 10 ) );
		$exported = 0;
		$limit = 100;
		$items = [];

		while ( $exported < $target_total ) {
			$current_limit = min( $limit, $target_total - $exported );
			// Table name is generated internally.
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table LIMIT %d,%d", $exported, $current_limit ) );
			$exported += count( $rows );

			foreach ( $rows as $row ) {
				$items[] = $row;
			}

			if ( count( $rows ) < $current_limit ) {
				break;
			}
		}

		return $items;
	}

	/**
	 * @phpstan-return array<int, LogDbRow>
	 * @return array<int, array<string, mixed>>
	 */
	private static function get_export_array_rows( ?int $max_items = null ) {
		global $wpdb;

		$table = static::get_table_name( $wpdb );
		$total_items = static::get_export_total();
		$target_total = $max_items === null ? $total_items : min( $total_items, intval( $max_items, 10 ) );
		$exported = 0;
		$limit = 100;
		$items = [];

		while ( $exported < $target_total ) {
			$current_limit = min( $limit, $target_total - $exported );
			// Table name is generated internally.
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table LIMIT %d,%d", $exported, $current_limit ), ARRAY_A );
			$exported += count( $rows );

			foreach ( $rows as $row ) {
				$items[] = $row;
			}

			if ( count( $rows ) < $current_limit ) {
				break;
			}
		}

		return $items;
	}

	/**
	 * @phpstan-param array<int, LogDbRow> $sample_rows
	 * @param array<int, array<string, mixed>> $sample_rows
	 * @param 'csv'|'json' $format
	 * @param int $total_items
	 * @return int
	 */
	private static function get_export_estimated_size( $format, $total_items, array $sample_rows ) {
		if ( $total_items === 0 ) {
			return 0;
		}

		if ( count( $sample_rows ) >= $total_items ) {
			$data = static::get_export_data( $format );

			return is_string( $data ) ? strlen( $data ) : 0;
		}

		$empty_data = self::get_export_data_for_rows( $format, [] );
		$sample_data = self::get_export_data_for_rows( $format, $sample_rows );
		if ( $empty_data === false || $sample_data === false || count( $sample_rows ) === 0 ) {
			return 0;
		}

		$item_size = max( 0, strlen( $sample_data ) - strlen( $empty_data ) ) / count( $sample_rows );

		return intval( round( strlen( $empty_data ) + ( $item_size * $total_items ) ), 10 );
	}

	/**
	 * @phpstan-param array<int, LogDbRow> $rows
	 * @param array<int, array<string, mixed>> $rows
	 * @param 'csv'|'json' $format
	 * @return string|false
	 */
	private static function get_export_data_for_rows( $format, array $rows ) {
		if ( $format === 'csv' ) {
			$objects = [];

			foreach ( $rows as $row ) {
				$objects[] = (object) $row;
			}

			return self::get_export_csv_data_for_rows( $objects );
		}

		if ( $format === 'json' ) {
			return self::get_export_json_data_for_rows( $rows );
		}

		return false;
	}

	/**
	 * @param array<string, mixed> $params
	 * @param array<int, string> $display_selected
	 * @return bool
	 */
	private static function should_export_all_rows( array $params, array $display_selected ) {
		return count( $params ) === 0 && count( $display_selected ) === 0;
	}

	/**
	 * @param array<int, string> $display_selected
	 * @phpstan-param LogGetParams $params
	 * @param array<string, mixed> $params
	 * @return array<int, string>
	 */
	protected static function get_export_fields( array $display_selected, array $params = [] ) {
		$safe_group_by = self::get_safe_group_by( $params );
		if ( $safe_group_by !== false ) {
			return [ $safe_group_by, 'count' ];
		}

		$allowed = array_keys( static::get_export_field_labels() );

		if ( count( $display_selected ) === 0 ) {
			return $allowed;
		}

		return array_values(
			array_filter(
				$display_selected,
				static function ( $field ) use ( $allowed ) {
					return in_array( $field, $allowed, true );
				}
			)
		);
	}

	/**
	 * @return array<string, string>
	 */
	protected static function get_export_field_labels() {
		return [
			'date' => 'date',
			'method' => 'method',
			'domain' => 'domain',
			'url' => 'source',
			'target' => 'target',
			'redirect_by' => 'redirect_by',
			'code' => 'code',
			'referrer' => 'referrer',
			'agent' => 'agent',
			'ip' => 'ip',
			'count' => 'count',
		];
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected static function get_export_field_label( $field ) {
		$labels = static::get_export_field_labels();

		return isset( $labels[ $field ] ) ? $labels[ $field ] : $field;
	}

	/**
	 * @param array<string, scalar|null> $row
	 * @return array<string, scalar|null>
	 */
	protected static function map_export_row( array $row ) {
		return [
			'date' => isset( $row['created'] ) ? $row['created'] : '',
			'method' => isset( $row['request_method'] ) ? $row['request_method'] : '',
			'domain' => isset( $row['domain'] ) ? $row['domain'] : '',
			'url' => isset( $row['url'] ) ? $row['url'] : '',
			'target' => '',
			'redirect_by' => isset( $row['redirect_by'] ) ? $row['redirect_by'] : '',
			'code' => isset( $row['http_code'] ) ? intval( $row['http_code'], 10 ) : 0,
			'referrer' => isset( $row['referrer'] ) ? $row['referrer'] : '',
			'agent' => isset( $row['agent'] ) ? $row['agent'] : '',
			'ip' => isset( $row['ip'] ) ? $row['ip'] : '',
			'count' => isset( $row['count'] ) ? intval( $row['count'], 10 ) : 0,
		];
	}

	/**
	 * @param array<string, scalar|null> $row
	 * @param array<int, string> $fields
	 * @return array<string, scalar|null>
	 */
	private static function filter_export_row( array $row, array $fields ) {
		$filtered = [];

		foreach ( $fields as $field ) {
			$filtered[ $field ] = isset( $row[ $field ] ) ? $row[ $field ] : '';
		}

		return $filtered;
	}

	/**
	 * @phpstan-param LogGetParams $params
	 * @param array<string, mixed> $params
	 * @return array<int, array<string, scalar|null>>
	 */
	private static function get_export_rows( array $params ) {
		$safe_group_by = self::get_safe_group_by( $params );
		if ( $safe_group_by !== false ) {
			return self::get_grouped_export_rows( $safe_group_by, $params );
		}

		return self::get_filtered_export_rows( $params );
	}

	/**
	 * @phpstan-param LogGetParams $params
	 * @param array<string, mixed> $params
	 * @return array<int, array<string, scalar|null>>
	 */
	private static function get_filtered_export_rows( array $params ) {
		global $wpdb;

		$query = self::get_query( $params );
		$table = static::get_table_name( $wpdb );
		$sql = "SELECT * FROM {$table} {$query['where']}";
		$items = isset( $params['items'] ) && is_array( $params['items'] ) ? $params['items'] : [];

		if ( count( $items ) > 0 ) {
			$ids = array_values(
				array_filter(
					array_map(
						static function ( $item ) {
							return is_numeric( $item ) ? intval( $item, 10 ) : 0;
						},
						$items
					)
				)
			);

			if ( count( $ids ) === 0 ) {
				return [];
			}

			$sql .= $query['where'] === '' ? ' WHERE ' : ' AND ';
			$sql .= 'id IN (' . implode( ',', array_map( 'intval', $ids ) ) . ')';
		}

		$sql .= ' ORDER BY id DESC';

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $sql, ARRAY_A );

		return is_array( $rows ) ? $rows : [];
	}

	/**
	 * @phpstan-param LogGetParams $params
	 * @param 'ip'|'url'|'agent' $safe_group_by
	 * @param array<string, mixed> $params
	 * @return array<int, array<string, scalar|null>>
	 */
	private static function get_grouped_export_rows( $safe_group_by, array $params ) {
		global $wpdb;

		$query = self::get_query( $params );
		$table = static::get_table_name( $wpdb );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $safe_group_by is restricted by get_safe_group_by() to a known column name.
		$sql = "SELECT COUNT(*) as count,$safe_group_by FROM {$table} {$query['where']}";
		$items = isset( $params['items'] ) && is_array( $params['items'] ) ? $params['items'] : [];

		if ( count( $items ) > 0 ) {
			$sanitized_items = array_values(
				array_filter(
					array_map(
						static function ( $item ) {
							return is_scalar( $item ) ? strval( $item ) : '';
						},
						$items
					)
				)
			);

			if ( count( $sanitized_items ) === 0 ) {
				return [];
			}

			$placeholders = implode( ',', array_fill( 0, count( $sanitized_items ), '%s' ) );
			$sql .= $query['where'] === '' ? ' WHERE ' : ' AND ';
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- $safe_group_by is restricted by get_safe_group_by() to a known column name, and the placeholder list is built from the number of sanitized items.
			$sql .= $wpdb->prepare( $safe_group_by . ' IN (' . $placeholders . ')', $sanitized_items );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $safe_group_by is restricted by get_safe_group_by() to a known column name.
		$sql .= " GROUP BY $safe_group_by ORDER BY count DESC, $safe_group_by";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $sql, ARRAY_A );

		return is_array( $rows ) ? $rows : [];
	}

	/**
	 * Restrict groupBy to a known log column before it is used in SQL fragments.
	 *
	 * @phpstan-param LogGetParams $params
	 * @param array<string, mixed> $params
	 * @return 'ip'|'url'|'agent'|false
	 */
	private static function get_safe_group_by( array $params ) {
		if ( ! isset( $params['groupBy'] ) || ! is_string( $params['groupBy'] ) ) {
			return false;
		}

		$safe_group_by = sanitize_text_field( $params['groupBy'] );

		if ( ! in_array( $safe_group_by, [ 'ip', 'url', 'agent' ], true ) ) {
			return false;
		}

		return $safe_group_by;
	}
}
