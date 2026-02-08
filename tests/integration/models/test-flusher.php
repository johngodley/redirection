<?php

class FlusherTest extends WP_UnitTestCase {
	private function setScheduleExpire( $days ) {
		Red_Options::save( array( 'expire_redirect' => $days, 'expire_404' => $days ) );
	}

	private function addLog( $days ) {
		global $wpdb;

		$data = array(
			'url' => 'source',
			'created' => gmdate( 'Y-m-d H:i:s', mktime( 0, 0, 0, (int) gmdate( 'm' ), (int) gmdate( 'd' ) - $days, (int) gmdate( 'Y' ) ) ),
		);

		$wpdb->insert( $wpdb->prefix . 'redirection_logs', $data );
	}

	/**
	 * Add multiple logs in bulk for performance
	 *
	 * @param int $count Number of logs to add.
	 * @param int $days Age of logs in days.
	 * @return void
	 */
	private function addLogsBulk( $count, $days ) {
		global $wpdb;

		$created = gmdate( 'Y-m-d H:i:s', mktime( 0, 0, 0, (int) gmdate( 'm' ), (int) gmdate( 'd' ) - $days, (int) gmdate( 'Y' ) ) );
		$table = $wpdb->prefix . 'redirection_logs';

		// Build bulk insert with 500 rows at a time to avoid query size limits
		$batch_size = 500;
		$batches = ceil( $count / $batch_size );

		for ( $batch = 0; $batch < $batches; $batch++ ) {
			$rows_in_batch = min( $batch_size, $count - ( $batch * $batch_size ) );
			$values = array();

			for ( $i = 0; $i < $rows_in_batch; $i++ ) {
				$values[] = $wpdb->prepare( '(%s, %s)', 'source', $created );
			}

			if ( ! empty( $values ) ) {
				// phpcs:ignore
				$wpdb->query( "INSERT INTO {$table} (url, created) VALUES " . implode( ',', $values ) );
			}
		}
	}

	private function getLogCount() {
		global $wpdb;

		return intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_logs" ), 10 );
	}

	public function testNothingScheduled() {
		Red_Flusher::clear();
		$this->assertEquals( 0, wp_next_scheduled( Red_Flusher::DELETE_HOOK ) );
	}

	public function testNothingScheduledAfterClear() {
		Red_Flusher::clear();
		$this->assertEquals( 0, wp_next_scheduled( Red_Flusher::DELETE_HOOK ) );
	}

	public function testSchedule() {
		Red_Flusher::schedule();
		$this->assertTrue( wp_next_scheduled( Red_Flusher::DELETE_HOOK ) > 0 );
	}

	public function testScheduleRepeatNoChange() {
		Red_Flusher::schedule();

		$next = wp_next_scheduled( Red_Flusher::DELETE_HOOK );

		Red_Flusher::schedule();

		$this->assertEquals( $next, wp_next_scheduled( Red_Flusher::DELETE_HOOK ) );
	}

	public function testScheduleIsClearedWhenReset() {
		$this->setScheduleExpire( 0 );
		Red_Flusher::schedule();
		$this->assertEquals( 0, wp_next_scheduled( Red_Flusher::DELETE_HOOK ) );
	}

	public function testScheduleIsCleared() {
		Red_Flusher::schedule();
		Red_Flusher::clear();

		$this->assertEquals( 0, wp_next_scheduled( Red_Flusher::DELETE_HOOK ) );
	}

	public function testFlush() {
		Red_Flusher::clear();
		Red_Redirect_Log::delete_all();

		// Set expiration to 7 days so that 8-day-old logs are deleted but 5-day-old logs remain
		$this->setScheduleExpire( 7 );

		// Clear the scheduled event that was created by setScheduleExpire (via flush_schedule filter)
		Red_Flusher::clear();

		$this->addLog( 5 );
		$this->addLog( 8 );
		$this->assertEquals( 2, $this->getLogCount() );

		$flusher = new Red_Flusher();
		$flusher->flush();

		$this->assertEquals( 1, $this->getLogCount() );
		$this->assertEquals( 0, wp_next_scheduled( Red_Flusher::DELETE_HOOK ) );
	}

	public function testBigFlush() {
		Red_Flusher::clear();
		Red_Redirect_Log::delete_all();

		// Set expiration to 7 days so that 8-day-old logs are deleted
		$this->setScheduleExpire( 7 );

		// Clear the scheduled event that was created by setScheduleExpire (via flush_schedule filter)
		Red_Flusher::clear();

		for ( $i = 0; $i < Red_Flusher::DELETE_MAX + 2; $i++ ) {
			$this->addLog( 8 );   // Will get flushed
		}

		$this->assertEquals( Red_Flusher::DELETE_MAX + 2, $this->getLogCount() );

		wp_schedule_event( time() + ( 60 * 30 ), Red_Flusher::DELETE_FREQ, Red_Flusher::DELETE_HOOK );

		$next_event = wp_next_scheduled( Red_Flusher::DELETE_HOOK );

		$flusher = new Red_Flusher();
		$flusher->flush();

		$this->assertEquals( 2, $this->getLogCount() );
		$this->assertNotEquals( $next_event, wp_next_scheduled( Red_Flusher::DELETE_HOOK ) );

		$flusher->flush();
		$this->assertEquals( 0, $this->getLogCount() );
	}

	public function testAggressiveModeNotTriggeredForSmallBacklog() {
		Red_Flusher::clear();
		Red_Redirect_Log::delete_all();
		delete_transient( 'redirection_aggressive_delete' );

		// Set expiration to 7 days
		$this->setScheduleExpire( 7 );
		Red_Flusher::clear();

		// Add logs just under the normal batch size
		for ( $i = 0; $i < Red_Flusher::DELETE_MAX - 100; $i++ ) {
			$this->addLog( 8 );
		}

		$flusher = new Red_Flusher();
		$flusher->flush();

		// Aggressive mode should NOT be enabled
		$this->assertFalse( get_transient( 'redirection_aggressive_delete' ) );
	}

	public function testAggressiveModeTriggeredForLargeBacklog() {
		Red_Flusher::clear();
		Red_Redirect_Log::delete_all();
		delete_transient( 'redirection_aggressive_delete' );

		// Set expiration to 7 days
		$this->setScheduleExpire( 7 );
		Red_Flusher::clear();

		// Add logs that will trigger aggressive mode (over DELETE_MAX and AGGRESSIVE_THRESHOLD)
		$large_count = Red_Flusher::DELETE_MAX + Red_Flusher::AGGRESSIVE_THRESHOLD + 1000;
		$this->addLogsBulk( $large_count, 8 );

		$this->assertEquals( $large_count, $this->getLogCount() );

		$flusher = new Red_Flusher();
		$flusher->flush();

		// After first flush, we should have deleted DELETE_MAX logs
		// and aggressive mode should be enabled
		$remaining_after_first = $large_count - Red_Flusher::DELETE_MAX;
		$this->assertEquals( $remaining_after_first, $this->getLogCount() );
		$this->assertTrue( get_transient( 'redirection_aggressive_delete' ) !== false );

		// Second flush should delete DELETE_AGGRESSIVE logs (in aggressive mode)
		$flusher->flush();
		$remaining_after_second = $remaining_after_first - Red_Flusher::DELETE_AGGRESSIVE;
		$this->assertEquals( $remaining_after_second, $this->getLogCount() );

		// Aggressive mode should still be active
		$this->assertTrue( get_transient( 'redirection_aggressive_delete' ) !== false );
	}

	public function testAggressiveModeSchedulesFasterRuns() {
		Red_Flusher::clear();
		Red_Redirect_Log::delete_all();
		delete_transient( 'redirection_aggressive_delete' );

		$this->setScheduleExpire( 7 );
		Red_Flusher::clear();

		// Add logs that will trigger aggressive mode
		$large_count = Red_Flusher::DELETE_MAX + Red_Flusher::AGGRESSIVE_THRESHOLD + 1000;
		$this->addLogsBulk( $large_count, 8 );

		$flusher = new Red_Flusher();
		$flusher->flush();

		// Enable aggressive mode
		$this->assertTrue( get_transient( 'redirection_aggressive_delete' ) !== false );

		// Get the scheduled time
		$next_run = wp_next_scheduled( Red_Flusher::DELETE_HOOK );

		// Should be scheduled within DELETE_FAST minutes (3 minutes = 180 seconds)
		$expected_time = time() + ( Red_Flusher::DELETE_FAST * 60 );
		$this->assertLessThanOrEqual( $expected_time + 10, $next_run, 'Aggressive mode should schedule faster runs' );
	}

	public function testAggressiveModeClearsWhenBacklogComplete() {
		Red_Flusher::clear();
		Red_Redirect_Log::delete_all();
		delete_transient( 'redirection_aggressive_delete' );

		$this->setScheduleExpire( 7 );
		Red_Flusher::clear();

		// Add a small number of logs (less than DELETE_MAX)
		for ( $i = 0; $i < 100; $i++ ) {
			$this->addLog( 8 );
		}

		// Manually enable aggressive mode
		set_transient( 'redirection_aggressive_delete', true, HOUR_IN_SECONDS );
		$this->assertTrue( get_transient( 'redirection_aggressive_delete' ) !== false );

		$flusher = new Red_Flusher();
		$flusher->flush();

		// All logs should be deleted and aggressive mode should be cleared
		$this->assertEquals( 0, $this->getLogCount() );
		$this->assertFalse( get_transient( 'redirection_aggressive_delete' ) );
	}

	public function testEstimateRemainingLogsReturnsZeroWhenNoExpiry() {
		Red_Flusher::clear();
		Red_Redirect_Log::delete_all();

		// Set expiration to 0 (disabled)
		$this->setScheduleExpire( 0 );

		// Add some old logs
		for ( $i = 0; $i < 100; $i++ ) {
			$this->addLog( 30 );
		}

		$flusher = new Red_Flusher();

		// Use reflection to test the private method
		$reflection = new ReflectionClass( $flusher );
		$method = $reflection->getMethod( 'estimate_remaining_logs' );
		$method->setAccessible( true );

		$result = $method->invoke( $flusher, 'redirection_logs', 0 );
		$this->assertEquals( 0, $result );
	}

	public function testEstimateRemainingLogsDetectsLargeBacklog() {
		Red_Flusher::clear();
		Red_Redirect_Log::delete_all();

		$this->setScheduleExpire( 7 );

		// Add more than AGGRESSIVE_THRESHOLD logs
		$large_count = Red_Flusher::AGGRESSIVE_THRESHOLD + 5000;
		$this->addLogsBulk( $large_count, 8 );

		$flusher = new Red_Flusher();

		// Use reflection to test the private method
		$reflection = new ReflectionClass( $flusher );
		$method = $reflection->getMethod( 'estimate_remaining_logs' );
		$method->setAccessible( true );

		$result = $method->invoke( $flusher, 'redirection_logs', 7 );

		// Should return at least AGGRESSIVE_THRESHOLD (the LIMIT + 1)
		$this->assertGreaterThanOrEqual( Red_Flusher::AGGRESSIVE_THRESHOLD, $result );
	}

	public function testExpireLogsReturnsCorrectDeletedCount() {
		Red_Flusher::clear();
		Red_Redirect_Log::delete_all();

		$this->setScheduleExpire( 7 );

		// Add 50 old logs
		$count = 50;
		for ( $i = 0; $i < $count; $i++ ) {
			$this->addLog( 8 );
		}

		$flusher = new Red_Flusher();

		// Use reflection to test the private method
		$reflection = new ReflectionClass( $flusher );
		$method = $reflection->getMethod( 'expire_logs' );
		$method->setAccessible( true );

		$deleted = $method->invoke( $flusher, 'redirection_logs', 7, 100 );

		// Should have deleted exactly 50 logs (all of them)
		$this->assertEquals( $count, $deleted );
		$this->assertEquals( 0, $this->getLogCount() );
	}

	public function testExpireLogsRespectsLimit() {
		Red_Flusher::clear();
		Red_Redirect_Log::delete_all();

		$this->setScheduleExpire( 7 );

		// Add 100 old logs
		$count = 100;
		for ( $i = 0; $i < $count; $i++ ) {
			$this->addLog( 8 );
		}

		$flusher = new Red_Flusher();

		// Use reflection to test the private method
		$reflection = new ReflectionClass( $flusher );
		$method = $reflection->getMethod( 'expire_logs' );
		$method->setAccessible( true );

		// Delete with a limit of 30
		$limit = 30;
		$deleted = $method->invoke( $flusher, 'redirection_logs', 7, $limit );

		// Should have deleted exactly 30 logs
		$this->assertEquals( $limit, $deleted );
		$this->assertEquals( $count - $limit, $this->getLogCount() );
	}

	public function testMultipleFlushesInAggressiveMode() {
		Red_Flusher::clear();
		Red_Redirect_Log::delete_all();
		delete_transient( 'redirection_aggressive_delete' );

		$this->setScheduleExpire( 7 );
		Red_Flusher::clear();

		// Add enough logs to require multiple aggressive flushes
		// Need: DELETE_MAX + (DELETE_AGGRESSIVE * 2) to test multiple aggressive runs
		$large_count = Red_Flusher::DELETE_MAX + ( Red_Flusher::DELETE_AGGRESSIVE * 2 ) + 5000;
		$this->addLogsBulk( $large_count, 8 );

		$initial_count = $this->getLogCount();
		$this->assertEquals( $large_count, $initial_count );

		$flusher = new Red_Flusher();

		// First flush: Normal mode, deletes DELETE_MAX
		$flusher->flush();
		$after_first = $this->getLogCount();
		$this->assertEquals( $initial_count - Red_Flusher::DELETE_MAX, $after_first );
		$this->assertTrue( get_transient( 'redirection_aggressive_delete' ) !== false, 'Aggressive mode should be enabled after first flush' );

		// Second flush: Aggressive mode, deletes DELETE_AGGRESSIVE
		$flusher->flush();
		$after_second = $this->getLogCount();
		$this->assertEquals( $after_first - Red_Flusher::DELETE_AGGRESSIVE, $after_second );
		$this->assertTrue( get_transient( 'redirection_aggressive_delete' ) !== false, 'Aggressive mode should still be enabled' );

		// Third flush: Still aggressive mode, deletes DELETE_AGGRESSIVE
		$flusher->flush();
		$after_third = $this->getLogCount();
		$this->assertEquals( $after_second - Red_Flusher::DELETE_AGGRESSIVE, $after_third );
	}
}
