<?php 

class NotifyTest extends WP_UnitTestCase {
	
	private function scheduleNotification( $recurrence ) {
		update_option( 'redirection_options', array( 'notify_schedule' => $recurrence ) );
		$notifier = new Red_Notify();
		$notifier->schedule();
	}
	
	public function testNothingScheduled() {
		$this->assertEquals( 0 , wp_next_scheduled( Red_Notify::NOTIFY_HOOK ) );
	}
	
	public function testSchedule() {
		$this->scheduleNotification( 'weekly' );
		$this->assertTrue( wp_next_scheduled( Red_Notify::NOTIFY_HOOK ) > 0 );
	}
	
	public function testReschedule() {
		$this->scheduleNotification( 'daily' );
		$this->scheduleNotification( 'monthly' );
		
		$this->assertEquals( 'monthly', wp_get_schedule( Red_Notify::NOTIFY_HOOK ) );
	}
	
	public function testNothingScheduledAfterCleared() {
		$this->scheduleNotification( 'monthly' );
		$this->scheduleNotification( 'never' );
		
		$this->assertEquals( 0, wp_next_scheduled( Red_Notify::NOTIFY_HOOK ) );
	}
	
	public function testGetDateRange() {
		$interval = 7 * 24 * 60 * 60;	// One week in seconds
		$end = strtotime('2017-08-05 00:00:00');
		$expect = array( '2017-07-29 00:00:00', '2017-08-05 00:00:00' );
		$notifier = new Red_Notify();
		
		$this->assertEquals( $expect, $notifier->getDateRange($interval, $end) );
	}
		
		
}
