<?php 

class Red_Notify {
	const NOTIFY_HOOK = 'redirection_send_notification';
	
	protected $recurrence = false;
	
	public function __construct() {
		$options = red_get_options();
		
		if ( isset( $options['notify_schedule'] ) ) {
			if ( $options['notify_schedule'] === 'never' ) {
				$this->recurrence = false;
			} else {
				$this->recurrence = $options['notify_schedule'];
			}
		}
		
		add_filter( 'cron_schedules', array( $this, 'custom_cron_schedules' ) );
	}
	
	public function custom_cron_schedules( $schedules ) {
		$schedules['weekly'] = array(
			'interval'	=> 7 * 24 * 60 * 60,
			'display'		=> 'Weekly'
		);
		
		$schedules['monthly'] = array(
			'interval'		=> 30 * 24 * 60 * 60,
			'display'		=> 'Every 30 Days'
		);
		
		return $schedules;
	}
	
	public function schedule() {	
		if ( $this->recurrence ) {
			if ( wp_next_scheduled( Red_Notify::NOTIFY_HOOK ) == 0 ) {
				wp_schedule_event( time(), $this->recurrence, self::NOTIFY_HOOK );
			} else { 
				if ( wp_get_schedule( self::NOTIFY_HOOK ) != $this->recurrence ) {
					wp_clear_scheduled_hook( self::NOTIFY_HOOK );
					wp_schedule_event( time(), $this->recurrence, self::NOTIFY_HOOK );
				}
			}
		} else {
			wp_clear_scheduled_hook( self::NOTIFY_HOOK );
		}
	}
	
	public function send() {
		global $wpdb;		
		$options = red_get_options();
		
		if ( empty( $options['notify_emails'] ) ) 
			return false;	
		
		$schedules = wp_get_schedules();
		$interval = $schedules[$this->recurrence]['interval'];
		$range = $this->getDateRange( $interval );
		
		$sql = "SELECT url FROM {$wpdb->prefix}redirection_404 WHERE created between '{$range[0]}' and '{$range[1]}'";
		$rows = $wpdb->get_results( $sql );
		
		if ( empty( $rows ) )
			return false;
		
		$to = $options['notify_emails'];
		$subject = 'New 404s Logged';
		$headers = array('Content-Type: text/html; charset=UTF-8');
		$message = "The following 404s were recently logged:<br />";
		
		foreach ( $rows as $row ) {
			$message .= $row->url . "<br />";
		}
		
		$message .= "<a href='" . admin_url('tools.php?page=redirection.php&sub=404s')
			. "'>Manage now</a>";
		
		wp_mail( $to, $subject, $message, $headers );
	}
	
	public function getDateRange( $interval, $end = null ) {
		if ( ! $end )
			$end = time();
		
		$start = $end - $interval;
		return array( date("Y-m-d H:i:s", $start), date("Y-m-d H:i:s", $end) );
	}
}
