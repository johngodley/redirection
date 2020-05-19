<?php

class Red_Redirect_Log extends Red_Log {
	public $redirection_id;
	public $group_id;

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
	 * @return integer Log ID, or false
	 */
	public static function create( $domain, $url, $ip, $details ) {
		global $wpdb;

		$insert = self::sanitize_create( $domain, $url, $ip, $details );
		$insert['group_id'] = 0;
		$insert['redirection_id'] = 0;

		if ( isset( $details['group_id'] ) ) {
			$insert['group_id'] = intval( $details['group_id'], 10 );
		}

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

	public static function delete_for_id( $id ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_logs WHERE redirection_id=%d", $id ) );
	}

	public static function delete_for_group( $id ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}redirection_logs WHERE group_id=%d", $id ) );
	}

	public static function get_csv_filename() {
		return 'redirection-log';
	}

	public static function get_csv_header() {
		return [ 'date', 'source', 'target', 'ip', 'referrer', 'agent' ];
	}

	public static function get_csv_row( array $row ) {
		return [
			$row->created,
			$row->url,
			$row->sent_to,
			$row->ip,
			$row->referrer,
			$row->agent,
		];
	}

	private function get_redirect_name( $agent ) {
		// phpcs:ignore
		if ( $agent === 'wordpress' ) {
			return 'WordPress';
		}

		return ucwords( $agent );
	}

	public function to_json() {
		return array_merge( parent::to_json(), [
			'sent_to' => $this->sent_to,
			'redirection_id' => intval( $this->redirection_id, 10 ),
			'group_id' => intval( $this->group_id, 10 ),
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
