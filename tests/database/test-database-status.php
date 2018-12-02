<?php

class DatabaseStatusTest extends WP_UnitTestCase {
	private function clearStage() {
		delete_option( Red_Database_Status::DB_UPGRADE_STAGE );
	}

	public function setUp() {
		$this->clearStage();
	}

	public function testNoStageWhenNotRunning() {
		$status = new Red_Database_Status();
		$stage = $status->get_current_stage();

		$this->assertFalse( $stage );
	}

	private function setRunningStage( $stage ) {
		$database = new Red_Database();
		$upgraders = $database->get_upgrades_for_version( '1.0' );

		$status = new Red_Database_Status();
		$status->set_initial_stages( $upgraders );
		$status->update_stage( $stage );
	}

	public function testStopWhenNotRunning() {
		$status = new Red_Database_Status();
		$status->stop_upgrade();
		$stage = $status->get_current_stage();

		$this->assertFalse( $stage );
	}

	public function testInitialReturnsStage() {
		$this->setRunningStage( 'add_title_201' );

		$status = new Red_Database_Status();
		$stage = $status->get_current_stage();

		$this->assertEquals( 'add_title_201', $stage );

		$option = get_option( Red_Database_Status::DB_UPGRADE_STAGE );

		$this->assertEquals( 'add_title_201', $option['stage'] );
		$this->assertEquals( 'add_title_201', $option['stages'][0] );
	}

	public function testStopWhenRunning() {
		$database = new Red_Database();
		$upgraders = $database->get_upgrades_for_version( '1.0' );

		$status = new Red_Database_Status();
		$status->set_initial_stages( $upgraders );
		$status->stop_upgrade();
		$stage = $status->get_current_stage();

		$this->assertFalse( $stage );
	}

	public function testSkipNotRunning() {
		$status = new Red_Database_Status();
		$status->skip_current_stage();
		$stage = $status->get_current_stage();

		$this->assertFalse( $stage );
	}

	public function testSkipToNextStage() {
		red_set_options( array( 'database' => '1.0' ) );

		$this->setRunningStage( 'add_title_201' );

		$status = new Red_Database_Status();
		$status->skip_current_stage();
		$stage = $status->get_current_stage();

		$this->assertEquals( 'add_group_indices_216', $stage );
	}

	public function testSkipToEnd() {
		red_set_options( array( 'database' => '1.0' ) );
		$this->setRunningStage( 'convert_title_to_text_240' );

		$status = new Red_Database_Status();
		$status->skip_current_stage();
		$stage = $status->get_current_stage();

		$this->assertFalse( $stage );
	}

	public function testStatusNotRunningNoUpgrade() {
		red_set_options( array( 'database' => REDIRECTION_DB_VERSION ) );

		$status = new Red_Database_Status();
		$expected = [
			'needUpgrade' => false,
			'needInstall' => false,
			'inProgress' => false,
		];

		$this->assertEquals( $expected, $status->get_upgrade_status() );
	}

	public function testStatusNotRunningNeedUpgrade() {
		red_set_options( array( 'database' => '1.0' ) );

		$status = new Red_Database_Status();
		$expected = [
			'needUpgrade' => true,
			'needInstall' => false,
			'inProgress' => false,
			'current' => '1.0',
			'next' => REDIRECTION_DB_VERSION,
		];
		$actual = $status->get_upgrade_status();
		unset( $actual['time'] );

		$this->assertEquals( $expected, $actual );
	}

	public function testStatusNotRunningNeedInstall() {
		red_set_options( array( 'database' => '' ) );

		$status = new Red_Database_Status();
		$expected = [
			'needUpgrade' => false,
			'needInstall' => true,
			'inProgress' => false,
			'current' => '-',
			'next' => REDIRECTION_DB_VERSION,
		];
		$actual = $status->get_upgrade_status();
		unset( $actual['time'] );

		$this->assertEquals( $expected, $actual );
	}

	public function testStatusRunningWithStage() {
		red_set_options( array( 'database' => '1.0' ) );
		$this->setRunningStage( 'add_title_201' );

		$status = new Red_Database_Status();
		$expected = [
			'needUpgrade' => true,
			'needInstall' => false,
			'inProgress' => true,
			'current' => '1.0',
			'next' => REDIRECTION_DB_VERSION,
			'complete' => 0.0,
			'status' => 'ok',
			'reason' => 'Add titles to redirects',
		];

		$actual = $status->get_upgrade_status( 'Add titles to redirects' );
		unset( $actual['time'] );

		$this->assertEquals( $expected, $actual );
	}

	public function testStatusRunningFinish() {
		red_set_options( array( 'database' => '1.0' ) );
		$this->setRunningStage( false );

		$status = new Red_Database_Status();
		$expected = [
			'needUpgrade' => true,
			'needInstall' => false,
			'inProgress' => false,
			'current' => '1.0',
			'next' => REDIRECTION_DB_VERSION,
			'complete' => 100,
			'status' => 'ok',
			'reason' => 'Expand size of redirect titles',
		];

		$actual = $status->get_upgrade_status( 'Expand size of redirect titles' );
		unset( $actual['time'] );

		$this->assertEquals( $expected, $actual );
	}

	public function testStatusRunningError() {
		$latest = new Red_Latest_Database();

		red_set_options( array( 'database' => '1.0' ) );
		$this->setRunningStage( 'add_title_201' );
		$status = new Red_Database_Status();
		$expected = [
			'needUpgrade' => true,
			'needInstall' => false,
			'inProgress' => true,
			'current' => '1.0',
			'next' => REDIRECTION_DB_VERSION,
			'complete' => 0.0,
			'status' => 'error',
			'reason' => 'this is an error',
			'debug' => $latest->get_table_schema(),
		];

		$actual = $status->get_upgrade_status( new WP_Error( 'error', 'this is an error' ) );
		unset( $actual['time'] );

		$this->assertEquals( $expected, $actual );
	}
}
