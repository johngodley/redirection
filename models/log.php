<?php

class RE_Log {
	public $id;
	public $created;
	public $url;
	public $agent;
	public $referrer;
	public $ip;
	public $redirection_id;

	function __construct( $values ) {
		foreach ( $values as $key => $value ) {
			$this->$key = $value;
		}

		$this->created = mysql2date( 'U', $this->created );
		$this->url     = stripslashes( $this->url );
	}

	static function get_by_id( $id ) {
		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}redirection_logs WHERE id=%d", $id ) );
		if ( $row ) {
			return new RE_Log( $row );
		}

		return false;
	}

	static function create( $url, $target, $agent, $ip, $referrer, $extra = array() ) {
		global $wpdb, $redirection;

		$insert = array(
			'url'     => urldecode( $url ),
			'created' => current_time( 'mysql' ),
			'ip'      => substr( $ip, 0, 45 ),
		);

		if ( ! empty( $agent ) ) {
			$insert['agent'] = $agent;
		}

		if ( ! empty( $referrer ) ) {
			$insert['referrer'] = $referrer;
		}

		$insert['sent_to']        = $target;
		$insert['redirection_id'] = isset( $extra['redirect_id'] ) ? $extra['redirect_id'] : 0;
		$insert['group_id']       = isset( $extra['group_id'] ) ? $extra['group_id'] : 0;

		$insert = apply_filters( 'redirection_log_data', $insert );
		if ( $insert ) {
			do_action( 'redirection_log', $insert );

			$wpdb->insert( $wpdb->prefix . 'redirection_logs', $insert );
		}

		return $wpdb->insert_id;
	}

	static function show_url( $url ) {
		return $url;
	}

	static function delete( $id ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_logs WHERE id=%d", $id ) );
	}

	static function delete_for_id( $id ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_logs WHERE redirection_id=%d", $id ) );
	}

	static function delete_for_group( $id ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_logs WHERE group_id=%d", $id ) );
	}

	static function delete_all( $filter_by = '', $filter = '' ) {
		global $wpdb;

		$where = array();

		if ( $filter_by === 'url' && $filter ) {
			$where[] = $wpdb->prepare( 'url LIKE %s', '%' . $wpdb->esc_like( $filter ) . '%' );
		} elseif ( $filter_by === 'ip' ) {
			$where[] = $wpdb->prepare( 'ip=%s', $filter );
		}

		$where_cond = '';
		if ( count( $where ) > 0 ) {
			$where_cond = ' WHERE ' . implode( ' AND ', $where );
		}

		$wpdb->query( "DELETE FROM {$wpdb->prefix}redirection_logs" . $where_cond );
	}

	static function export_to_csv() {
		global $wpdb;

		$filename = 'redirection-log-' . date_i18n( get_option( 'date_format' ) ) . '.csv';

		header( 'Content-Type: text/csv' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$stdout = fopen( 'php://output', 'w' );

		fputcsv( $stdout, array( 'date', 'source', 'target', 'ip', 'referrer', 'agent' ) );

		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_logs" );
		$exported = 0;

		while ( $exported < $total_items ) {
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}redirection_logs LIMIT %d,%d", $exported, 100 ) );
			$exported += count( $rows );

			foreach ( $rows as $row ) {
				$csv = array(
					$row->created,
					$row->url,
					$row->sent_to,
					$row->ip,
					$row->referrer,
					$row->agent,
				);

				fputcsv( $stdout, $csv );
			}

			if ( count( $rows ) < 100 ) {
				break;
			}
		}
	}

	public function to_json() {
		return array(
			'sent_to' => $this->sent_to,
			'ip' => $this->ip,
		);
	}
}

class RE_404 {
	public $id;
	public $created;
	public $url;
	public $agent;
	public $referrer;
	public $ip;

	function __construct( $values ) {
		foreach ( $values as $key => $value ) {
			$this->$key = $value;
		}

		$this->created = mysql2date( 'U', $this->created );
	}

	static function get_by_id( $id ) {
		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}redirection_404 WHERE id=%d", $id ) );
		if ( $row ) {
			return new RE_404( $row );
		}

		return false;
	}

	static function create( $url, $agent, $ip, $referrer ) {
		global $wpdb, $redirection;

		$insert = array(
			'url'     => substr( urldecode( $url ), 0, 255 ),
			'created' => current_time( 'mysql' ),
			'ip'      => substr( $ip, 0, 45 ),
		);

		if ( ! empty( $agent ) ) {
			$insert['agent'] = substr( $agent, 0, 255 );
		}

		if ( ! empty( $referrer ) ) {
			$insert['referrer'] = substr( $referrer, 0, 255 );
		}

		$insert = apply_filters( 'redirection_404_data', $insert );
		if ( $insert ) {
			$wpdb->insert( $wpdb->prefix . 'redirection_404', $insert );

			if ( $wpdb->insert_id ) {
				return $wpdb->insert_id;
			}
		}

		return false;
	}

	static function delete( $id ) {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_404 WHERE id=%d", $id ) );
	}

	static function delete_all( $filter_by = '', $filter = '' ) {
		global $wpdb;

		$where = array();

		if ( $filter_by === 'url-exact' ) {
			$where[] = $wpdb->prepare( 'url=%s', $filter );
		} if ( $filter_by === 'url' && $filter ) {
			$where[] = $wpdb->prepare( 'url LIKE %s', '%' . $wpdb->esc_like( $filter ) . '%' );
		} elseif ( $filter_by === 'ip' ) {
			$where[] = $wpdb->prepare( 'ip=%s', $filter );
		}

		$where_cond = '';
		if ( count( $where ) > 0 ) {
			$where_cond = ' WHERE ' . implode( ' AND ', $where );
		}

		$wpdb->query( "DELETE FROM {$wpdb->prefix}redirection_404" . $where_cond );
	}

	static function export_to_csv() {
		global $wpdb;

		$filename = 'redirection-404-' . date_i18n( get_option( 'date_format' ) ) . '.csv';

		header( 'Content-Type: text/csv' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$stdout = fopen( 'php://output', 'w' );

		fputcsv( $stdout, array( 'date', 'source', 'ip', 'referrer' ) );

		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_404" );
		$exported = 0;

		while ( $exported < $total_items ) {
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}redirection_404 LIMIT %d,%d", $exported, 100 ) );
			$exported += count( $rows );

			foreach ( $rows as $row ) {
				$csv = array(
					$row->created,
					$row->url,
					$row->ip,
					$row->referrer,
				);

				fputcsv( $stdout, $csv );
			}

			if ( count( $rows ) < 100 ) {
				break;
			}
		}
	}

	public function to_json() {
		return array(
			'ip' => $this->ip,
		);
	}
}

class RE_Filter_Log {
	static public function get( $table, $construct, array $params ) {
		global $wpdb;

		$query = self::get_query( $params );

		$rows = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}$table {$query['where']}" . $wpdb->prepare( ' ORDER BY ' . $query['orderby'] . ' ' . $query['direction'] . ' LIMIT %d,%d', $query['offset'], $query['limit'] )
		);
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}$table " . $query['where'] );
		$items = array();

		foreach ( $rows as $row ) {
			$item = new $construct( $row );
			$items[] = array_merge( $item->to_json(), array(
				'id' => intval( $item->id, 10 ),
				'created' => date_i18n( get_option( 'date_format' ), $item->created ),
				'created_time' => gmdate( get_option( 'time_format' ), $item->created ),
				'url' => $item->url,
				'agent' => $item->agent,
				'referrer' => $item->referrer,
			) );
		}

		return array(
			'items' => $items,
			'total' => intval( $total_items, 10 ),
		);
	}

	static public function get_grouped( $table, $group, array $params ) {
		global $wpdb;

		$query = self::get_query( $params );

		if ( ! in_array( $group, array( 'ip', 'url' ), true ) ) {
			$group = 'url';
		}

		$sql = $wpdb->prepare( "SELECT COUNT(*) as count,$group FROM {$wpdb->prefix}$table " . $query['where'] . ' GROUP BY ' . $group . ' ORDER BY count ' . $query['direction'] . ' LIMIT %d,%d', $query['offset'], $query['limit'] );
		$rows = $wpdb->get_results( $sql );
		$total_items = $wpdb->get_var( "SELECT COUNT(DISTINCT $group) FROM {$wpdb->prefix}$table" );

		foreach ( $rows as $row ) {
			$row->count = intval( $row->count, 10 );
			$row->id = isset( $row->url ) ? $row->url : $row->ip;
		}

		return array(
			'items' => $rows,
			'total' => intval( $total_items, 10 ),
		);
	}

	static private function get_query( array $params ) {
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

		if ( isset( $params['filter'] ) && strlen( $params['filter'] ) > 0 ) {
			if ( isset( $params['filterBy'] ) && $params['filterBy'] === 'ip' ) {
				$query['where'] = $wpdb->prepare( 'WHERE ip=%s', $params['filter'] );
			} elseif ( isset( $params['filterBy'] ) && $params['filterBy'] === 'url-exact' ) {
				$query['where'] = $wpdb->prepare( 'WHERE url=%s', $params['filter'] );
			} else {
				$query['where'] = $wpdb->prepare( 'WHERE url LIKE %s', '%' . $wpdb->esc_like( trim( $params['filter'] ) ) . '%' );
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
}
