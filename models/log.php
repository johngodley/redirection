<?php

require_once __DIR__ . '/log-404.php';
require_once __DIR__ . '/log-redirect.php';

abstract class Red_Log {
	protected $id;
	protected $created;
	protected $url;
	protected $agent;
	protected $referrer;
	protected $ip;
	protected $domain;
	protected $http_code;
	protected $request_method;
	protected $request_data;

	public function __construct( $values ) {
		foreach ( $values as $key => $value ) {
			$this->$key = $value;
		}

		$this->created = mysql2date( 'U', $this->created );
	}

	abstract protected static function get_table_name( $wpdb );

	public static function get_by_id( $id ) {
		global $wpdb;

		$table = self::get_table_name( $wpdb );
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM {$table} WHERE id=%d', $id ) );
		if ( $row ) {
			return new self( $row );
		}

		return false;
	}

	public static function delete( $id ) {
		global $wpdb;

		$wpdb->delete( static::get_table_name( $wpdb ), [ 'id' => $id ] );
	}

	public static function delete_all( $filter_by, $filter_value ) {
		global $wpdb;

		$where = [];

		if ( $filter_by === 'url-exact' ) {
			$where[] = $wpdb->prepare( 'url=%s', $filter_value );
		} elseif ( $filter_by === 'url' ) {
			$where[] = $wpdb->prepare( 'url LIKE %s', '%' . $wpdb->esc_like( $filter_value ) . '%' );
		} elseif ( $filter_by === 'ip' ) {
			$where[] = $wpdb->prepare( 'ip=%s', $filter_value );
		}

		$where_cond = '';
		if ( count( $where ) > 0 ) {
			$where_cond = ' WHERE ' . implode( ' AND ', $where );
		}

		// phpcs:ignore
		$wpdb->query( "DELETE FROM " . static::get_table_name( $wpdb ) . $where_cond );
	}

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
			'request_method' => $this->request_method,
			'http_code' => intval( $this->http_code, 10 ),
			'request_data' => $this->request_data ? json_decode( $this->request_data ) : '',
		];
	}

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
		$rows = $wpdb->get_results( $sql );

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

	public static function get_grouped( $group, array $params ) {
		global $wpdb;

		$table = static::get_table_name( $wpdb );
		$query = self::get_query( $params );

		if ( ! in_array( $group, array( 'ip', 'url' ), true ) ) {
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
		$total_items = $wpdb->get_var( "SELECT COUNT(DISTINCT $group) FROM {$table}" );

		foreach ( $rows as $row ) {
			$row->count = intval( $row->count, 10 );
			$row->id = isset( $row->url ) ? $row->url : $row->ip;
		}

		return array(
			'items' => $rows,
			'total' => intval( $total_items, 10 ),
		);
	}

	private static function get_query( array $params ) {
		global $wpdb;

		$query = array(
			'orderby' => 'id',
			'direction' => 'DESC',
			'limit' => RED_DEFAULT_PER_PAGE,
			'offset' => 0,
			'where' => '',
		);

		if ( isset( $params['orderby'] ) && in_array( $params['orderby'], array( 'ip', 'url' ), true ) ) {
			$query['orderby'] = $params['orderby'];
		}

		if ( isset( $params['direction'] ) && in_array( $params['direction'], array( 'asc', 'desc' ), true ) ) {
			$query['direction'] = strtoupper( $params['direction'] );
		}

		if ( isset( $params['filterBy'] ) && is_array( $params['filterBy'] ) ) {
			if ( isset( $params['filterBy']['ip'] ) ) {
				// phpcs:ignore
				$ip = @inet_pton( trim( $params['filterBy']['ip'] ) );

				if ( $ip !== false ) {
					$ip = @inet_ntop( $ip );  // Convert back to string
					$query['where'] = $wpdb->prepare( 'WHERE ip=%s', $ip );
				} else {
					$query['where'] = $wpdb->prepare( 'WHERE ip LIKE %s', '%' . $wpdb->esc_like( trim( $params['filterBy']['ip'] ) ) . '%' );
				}
			} elseif ( isset( $params['filterBy']['domain'] ) ) {
				$query['where'] = $wpdb->prepare( 'WHERE domain LIKE %s', '%' . $wpdb->esc_like( trim( $params['filterBy']['domain'] ) ) . '%' );
			} elseif ( isset( $params['filterBy']['url-exact'] ) ) {
				$query['where'] = $wpdb->prepare( 'WHERE url=%s', $params['filterBy']['url-exact'] );
			} elseif ( isset( $params['filterBy']['referrer'] ) ) {
				$query['where'] = $wpdb->prepare( 'WHERE referrer LIKE %s', '%' . $wpdb->esc_like( trim( $params['filterBy']['referrer'] ) ) . '%' );
			} elseif ( isset( $params['filterBy']['agent'] ) ) {
				$query['where'] = $wpdb->prepare( 'WHERE agent LIKE %s', '%' . $wpdb->esc_like( trim( $params['filterBy']['agent'] ) ) . '%' );
			} elseif ( isset( $params['filterBy']['target'] ) ) {
				$query['where'] = $wpdb->prepare( 'WHERE sent_to LIKE %s', '%' . $wpdb->esc_like( trim( $params['filterBy']['target'] ) ) . '%' );
			} elseif ( isset( $params['filterBy']['url'] ) ) {
				$query['where'] = $wpdb->prepare( 'WHERE url LIKE %s', '%' . $wpdb->esc_like( trim( $params['filterBy']['url'] ) ) . '%' );
			}
		}

		if ( isset( $params['per_page'] ) ) {
			$query['limit'] = intval( $params['per_page'], 10 );
			$query['limit'] = min( RED_MAX_PER_PAGE, $query['limit'] );
			$query['limit'] = max( 5, $query['limit'] );
		}

		if ( isset( $params['page'] ) ) {
			$query['offset'] = intval( $params['page'], 10 );
			$query['offset'] = max( 0, $query['offset'] );
			$query['offset'] *= $query['limit'];
		}

		return $query;
	}

	protected static function sanitize_create( $domain, $url, $ip, $details ) {
		$insert = [
			'url' => substr( $url, 0, 2000 ),
			'domain' => substr( $domain, 0, 255 ),
			'ip' => substr( $ip, 0, 45 ),
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
			$insert['agent'] = substr( $insert['agent'], 0, 255 );
		}

		if ( isset( $insert['referrer'] ) ) {
			$insert['referrer'] = substr( $insert['referrer'], 0, 255 );
		}

		if ( isset( $insert['request_data'] ) ) {
			$insert['request_data'] = wp_json_encode( $insert['request_data'] );
		}

		return $insert;
	}

	public static function export_to_csv() {
		$filename = static::get_csv_filename() . '-' . date_i18n( get_option( 'date_format' ) ) . '.csv';

		header( 'Content-Type: text/csv' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

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
