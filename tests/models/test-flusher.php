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
		Plugin\Flusher::clear();
		$this->assertEquals( 0, wp_next_scheduled( Plugin\Flusher::DELETE_HOOK ) );
	}

	public function testNothingScheduledAfterClear() {
		Plugin\Flusher::clear();
		$this->assertEquals( 0, wp_next_scheduled( Plugin\Flusher::DELETE_HOOK ) );
	}

	public function testSchedule() {
		Plugin\Flusher::schedule();
		$this->assertTrue( wp_next_scheduled( Plugin\Flusher::DELETE_HOOK ) > 0 );
	}

	public function testScheduleRepeatNoChange() {
		Plugin\Flusher::schedule();

		$next = wp_next_scheduled( Plugin\Flusher::DELETE_HOOK );

		Plugin\Flusher::schedule();

		$this->assertEquals( $next, wp_next_scheduled( Plugin\Flusher::DELETE_HOOK ) );
	}

	public function testScheduleIsClearedWhenReset() {
		$this->setScheduleExpire( 0 );
		Plugin\Flusher::schedule();
		$this->assertEquals( 0, wp_next_scheduled( Plugin\Flusher::DELETE_HOOK ) );
	}

	public function testScheduleIsCleared() {
		Plugin\Flusher::schedule();
		Plugin\Flusher::clear();

		$this->assertEquals( 0, wp_next_scheduled( Plugin\Flusher::DELETE_HOOK ) );
	}

	public function testFlush() {
		Plugin\Flusher::clear();
		Log\Redirect::delete_all();

		$this->addLog( 5 );
		$this->addLog( 8 );
		$this->assertEquals( 2, $this->getLogCount() );

		$flusher = new Plugin\Flusher();
		$flusher->flush();

		$this->assertEquals( 1, $this->getLogCount() );
		$this->assertEquals( 0, wp_next_scheduled( Plugin\Flusher::DELETE_HOOK ) );
	}

	public function testBigFlush() {
		Plugin\Flusher::clear();
		Log\Redirect::delete_all();

		for ( $i = 0; $i < Plugin\Flusher::DELETE_MAX + 2; $i++ ) {
			$this->addLog( 8 );   // Will get flushed
		}

		$this->assertEquals( Plugin\Flusher::DELETE_MAX + 2, $this->getLogCount() );

		wp_schedule_event( time() + ( 60 * 30 ), Plugin\Flusher::DELETE_FREQ, Plugin\Flusher::DELETE_HOOK );

		$next_event = wp_next_scheduled( Plugin\Flusher::DELETE_HOOK );

		$flusher = new Plugin\Flusher();
		$flusher->flush();

		$this->assertEquals( 2, $this->getLogCount() );
		$this->assertNotEquals( $next_event, wp_next_scheduled( Plugin\Flusher::DELETE_HOOK ) );

		$flusher->flush();
		$this->assertEquals( 0, $this->getLogCount() );
	}
}
