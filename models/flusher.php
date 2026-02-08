<?php

/**
 * Handles automatic expiration and optimization of redirect and 404 logs
 */
class Red_Flusher {
	const DELETE_HOOK = 'redirection_log_delete';
	const DELETE_FREQ = 'daily';
	const DELETE_MAX = 20000;
	const DELETE_AGGRESSIVE = 50000;  // Batch size for large backlogs (reduced from 100k for better replication)
	const DELETE_KEEP_ON = 10;  // 10 minutes
	const DELETE_FAST = 3;  // 3 minutes for aggressive mode (increased to reduce replication pressure)
	const AGGRESSIVE_THRESHOLD = 100000;  // Switch to aggressive mode if more than 100k logs need deletion

	/**
	 * Flush expired logs and optimize tables
	 *
	 * @return void
	 */
	public function flush() {
		$options = Red_Options::get();

		// Start with normal batch size
		$batch_size = self::DELETE_MAX;

		// Check if we're in an ongoing aggressive deletion cycle
		$aggressive_mode = get_transient( 'redirection_aggressive_delete' );
		$is_aggressive = $aggressive_mode !== false;
		if ( $is_aggressive ) {
			$batch_size = self::DELETE_AGGRESSIVE;
		}

		$total  = $this->expire_logs( 'redirection_logs', $options['expire_redirect'], $batch_size );
		$total += $this->expire_logs( 'redirection_404', $options['expire_404'], $batch_size );

		// If we deleted the full batch, there are likely more logs to delete
		if ( $total >= $batch_size ) {
			// Check if we should switch to aggressive mode (only if not already in it)
			if ( ! $is_aggressive && $batch_size === self::DELETE_MAX ) {
				// Sample check: if we hit the normal limit, check if there's a large backlog
				$remaining = $this->estimate_remaining_logs( 'redirection_logs', $options['expire_redirect'] );
				$remaining += $this->estimate_remaining_logs( 'redirection_404', $options['expire_404'] );

				if ( $remaining >= self::AGGRESSIVE_THRESHOLD ) {
					// Enable aggressive mode for 1 hour (will auto-expire if deletion completes)
					set_transient( 'redirection_aggressive_delete', true, HOUR_IN_SECONDS );
					$is_aggressive = true;
					$batch_size = self::DELETE_AGGRESSIVE;
				}
			}

			$delay_minutes = $is_aggressive ? self::DELETE_FAST : self::DELETE_KEEP_ON;
			$next = time() + ( $delay_minutes * 60 );

			// Schedule next deletion if it's before the next normal event
			$next_scheduled = wp_next_scheduled( self::DELETE_HOOK );
			if ( $next_scheduled === false || $next < $next_scheduled ) {
				wp_schedule_single_event( $next, self::DELETE_HOOK );
			}
		} else {
			// Deletion is complete or slowing down, clear aggressive mode
			delete_transient( 'redirection_aggressive_delete' );
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
	 * Estimate remaining expired logs using a fast sampling method
	 * Uses COUNT(*) over a limited subquery to avoid full table scans
	 *
	 * @param string $table Table name (without prefix).
	 * @param int $expiry_time Number of days to keep logs.
	 * @return int Estimated number of expired logs.
	 */
	private function estimate_remaining_logs( $table, $expiry_time ) {
		global $wpdb;

		if ( $expiry_time <= 0 ) {
			return 0;
		}

		// Sample approach: Check up to AGGRESSIVE_THRESHOLD + 1 expired logs
		// Use COUNT(*) over a limited subquery so only a single scalar is returned
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM ( SELECT 1 FROM {$wpdb->prefix}{$table} WHERE created < DATE_SUB(NOW(), INTERVAL %d DAY) LIMIT %d ) AS t", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$expiry_time,
				self::AGGRESSIVE_THRESHOLD + 1
			)
		);

		// If we reached AGGRESSIVE_THRESHOLD rows, there's definitely a large backlog
		return $count ? (int) $count : 0;
	}

	/**
	 * Delete expired logs from a table
	 *
	 * @param string $table Table name (without prefix).
	 * @param int $expiry_time Number of days to keep logs.
	 * @param int $batch_size Maximum number of logs to delete in this batch.
	 * @return int Number of logs deleted.
	 */
	private function expire_logs( $table, $expiry_time, $batch_size = self::DELETE_MAX ) {
		global $wpdb;

		if ( $expiry_time <= 0 ) {
			return 0;
		}

		// Use DELETE with LIMIT - more efficient than counting first
		// The affected rows tell us how many were deleted
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}{$table} WHERE created < DATE_SUB(NOW(), INTERVAL %d DAY) LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$expiry_time,
				$batch_size
			)
		);

		return $deleted ? (int) $deleted : 0;
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
