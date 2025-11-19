<?php

/**
 * Handles automatic expiration and optimization of redirect and 404 logs
 */
class Red_Flusher {
	const DELETE_HOOK = 'redirection_log_delete';
	const DELETE_FREQ = 'daily';
	const DELETE_MAX = 20000;
	const DELETE_KEEP_ON = 10;  // 10 minutes

	/**
	 * Flush expired logs and optimize tables
	 *
	 * @return void
	 */
	public function flush() {
		$options = Red_Options::get();

		$total  = $this->expire_logs( 'redirection_logs', $options['expire_redirect'] );
		$total += $this->expire_logs( 'redirection_404', $options['expire_404'] );

		if ( $total >= self::DELETE_MAX ) {
			$next = time() + ( self::DELETE_KEEP_ON * 60 );

			// There are still more logs to clear - keep on doing until we're clean or until the next normal event
			if ( $next < wp_next_scheduled( self::DELETE_HOOK ) ) {
				wp_schedule_single_event( $next, self::DELETE_HOOK );
			}
		}

		$this->optimize_logs();
	}

	/**
	 * Randomly optimize log tables to improve performance
	 *
	 * @return void
	 */
	private function optimize_logs() {
		global $wpdb;

		$rand = wp_rand( 1, 5000 );

		if ( $rand === 11 ) {
			$wpdb->query( "OPTIMIZE TABLE {$wpdb->prefix}redirection_logs" );
		} elseif ( $rand === 12 ) {
			$wpdb->query( "OPTIMIZE TABLE {$wpdb->prefix}redirection_404" );
		}
	}

	/**
	 * Delete expired logs from a table
	 *
	 * @param string $table Table name (without prefix).
	 * @param int $expiry_time Number of days to keep logs.
	 * @return int Number of logs deleted.
	 */
	private function expire_logs( $table, $expiry_time ) {
		global $wpdb;

		if ( $expiry_time > 0 ) {
			// Known values
			// phpcs:ignore
			$logs = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}{$table} WHERE created < DATE_SUB(NOW(), INTERVAL %d DAY)", $expiry_time ) );

			if ( $logs > 0 ) {
				// Known values
				// phpcs:ignore
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}{$table} WHERE created < DATE_SUB(NOW(), INTERVAL %d DAY) LIMIT %d", $expiry_time, self::DELETE_MAX ) );
				return min( self::DELETE_MAX, $logs );
			}
		}

		return 0;
	}

	/**
	 * Schedule the automatic log deletion cron job
	 *
	 * @return void
	 */
	public static function schedule() {
		$options = Red_Options::get();

		if ( $options['expire_redirect'] > 0 || $options['expire_404'] > 0 ) {
			if ( wp_next_scheduled( self::DELETE_HOOK ) === false ) {
				wp_schedule_event( time(), self::DELETE_FREQ, self::DELETE_HOOK );
			}
		} else {
			self::clear();
		}
	}

	/**
	 * Clear the scheduled log deletion cron job
	 *
	 * @return void
	 */
	public static function clear() {
		wp_clear_scheduled_hook( self::DELETE_HOOK );
	}
}
