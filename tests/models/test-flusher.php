<?php

class FlusherTest extends WP_UnitTestCase {
	private function setScheduleExpire( $days ) {
		update_option( 'redirection_options', array( 'expire_redirect' => $days, 'expire_404' => $days ) );
	}

	private function addLog( $days ) {
		global $wpdb;

		$data = array(
			'url' => 'source',
			'created' => date( 'Y-m-d H:I:s', mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $days, date( 'Y' ) ) ),
		);

		$wpdb->insert( $wpdb->prefix.'redirection_logs', $data );
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
		RE_Log::delete_all();

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
		RE_Log::delete_all();

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
}
