<?php

class MonitorTest extends WP_UnitTestCase {
	private function getPost( $status, $type ) {
		return (object) array(
			'post_status' => $status,
			'post_type' => $type,
		);
	}

	private function getDraftPost( $type = 'post' ) {
		return $this->getPost( 'draft', $type );
	}

	private function getPublishedPost( $type = 'post' ) {
		return $this->getPost( 'publish', $type );
	}

	private function getFormData() {
		return array( 'redirection_slug' => true );
	}

	public function testUnpublished() {
		$monitor = new Red_Monitor( array() );

		$this->assertFalse( $monitor->can_monitor_post( $this->getDraftPost(), $this->getPublishedPost(), $this->getFormData() ) );
		$this->assertFalse( $monitor->can_monitor_post( $this->getPublishedPost(), $this->getDraftPost(), $this->getFormData() ) );
	}

	public function testHierarchical() {
		$monitor = new Red_Monitor( array() );

		$this->assertFalse( $monitor->can_monitor_post( $this->getPublishedPost( 'page' ), $this->getPublishedPost(), $this->getFormData() ) );
	}

	public function testRedirectionEnabled() {
		$monitor = new Red_Monitor( array() );

		$this->assertFalse( $monitor->can_monitor_post( $this->getPublishedPost(), $this->getPublishedPost(), array() ) );
	}

	public function testCanMonitor() {
		$monitor = new Red_Monitor( array() );

		$this->assertTrue( $monitor->can_monitor_post( $this->getPublishedPost(), $this->getPublishedPost(), $this->getFormData() ) );
	}

	public function testNoHooks() {
		$monitor = new Red_Monitor( array() );

		$this->assertFalse( has_action( 'post_updated', array( $monitor, 'post_updated' ) ) );
		$this->assertFalse( has_action( 'edit_form_advanced', array( $monitor, 'insert_old_post' ) ) );
		$this->assertFalse( has_action( 'edit_page_form', array( $monitor, 'insert_old_post' ) ) );
	}

	public function testHasHooks() {
		update_option( 'permalink_structure', '/%category%/%postname%' );

		$monitor = new Red_Monitor( array( 'monitor_post' => 99 ) );

		$this->assertEquals( 11, has_action( 'post_updated', array( $monitor, 'post_updated' ) ) );
		$this->assertEquals( 10, has_action( 'edit_form_advanced', array( $monitor, 'insert_old_post' ) ) );
		$this->assertEquals( 10, has_action( 'edit_page_form', array( $monitor, 'insert_old_post' ) ) );
	}
}
