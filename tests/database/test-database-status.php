<?php

class DatabaseStatusTest extends WP_UnitTestCase {
	private function clearStage() {
		red_set_options( [ Red_Database_Status::DB_UPGRADE_STAGE => false ] );
	}

	public function setUp() {
		$this->clearStage();
	}

	private function setRunningStage( $stage ) {
		$database = new Red_Database();
		$upgraders = $database->get_upgrades_for_version( '1.0', false );

		$status = new Red_Database_Status();
		$status->start_upgrade( $upgraders );
		$status->set_stage( $stage );
	}

	public function testNoStageWhenNotRunning() {
		$status = new Red_Database_Status();
		$stage = $status->get_current_stage();

		$this->assertFalse( $stage );
	}

	public function testStopWhenNotRunning() {
		$status = new Red_Database_Status();
		$status->stop_update();
		$stage = $status->get_current_stage();

		$this->assertFalse( $stage );
	}

	public function testInitialReturnsStage() {
		$this->setRunningStage( 'add_title_201' );

		$status = new Red_Database_Status();
		$stage = $status->get_current_stage();

		$this->assertEquals( 'add_title_201', $stage );

		$settings = red_get_options();
		$option = $settings[ Red_Database_Status::DB_UPGRADE_STAGE ];

		$this->assertEquals( 'add_title_201', $option['stage'] );
		$this->assertEquals( 'add_title_201', $option['stages'][0] );
	}

	public function testStopWhenRunning() {
		$database = new Red_Database();
		$upgraders = $database->get_upgrades_for_version( '1.0', false );

		$status = new Red_Database_Status();
		$status->start_upgrade( $upgraders );
		$status->stop_update();
		$stage = $status->get_current_stage();

		$this->assertFalse( $stage );
	}

	public function testSkipNotRunning() {
		$status = new Red_Database_Status();
		$status->set_next_stage();
		$stage = $status->get_current_stage();

		$this->assertFalse( $stage );
	}

	public function testSkipToNextStage() {
		red_set_options( array( 'database' => '1.0' ) );

		$this->setRunningStage( 'add_title_201' );

		$status = new Red_Database_Status();
		$status->set_next_stage();
		$stage = $status->get_current_stage();

		$this->assertEquals( 'add_group_indices_216', $stage );
	}

	public function testSkipToEnd() {
		$database = new Red_Database();
		$upgrades = $database->get_upgrades();
		$upgrade = Red_Database_Upgrader::get( $upgrades[ count( $upgrades ) - 1 ] );
		$stages = array_keys( $upgrade->get_stages() );

		red_set_options( array( 'database' => '1.0' ) );
		$this->setRunningStage( $stages[ count( $stages ) - 1 ] );

		$status = new Red_Database_Status();
		$status->set_next_stage();
		$stage = $status->get_current_stage();

		$this->assertFalse( $stage );
	}

	public function testStatusNotRunningNoUpgrade() {
		red_set_options( array( 'database' => REDIRECTION_DB_VERSION ) );

		$status = new Red_Database_Status();
		$expected = [
			'status' => 'ok',
			'inProgress' => false,
		];

		$this->assertEquals( $expected, $status->get_json() );
	}

	public function testStatusNotRunningNeedUpgrade() {
		red_set_options( array( 'database' => '1.0' ) );

		$status = new Red_Database_Status();
		$status->start_upgrade( [] );
		$expected = [
			'inProgress' => false,
			'status' => 'need-update',
			'current' => '1.0',
			'next' => REDIRECTION_DB_VERSION,
		];

		$actual = $this->get_results( $status, null );

		$this->assertEquals( $expected, $actual );
	}

	public function testStatusNotRunningNeedInstall() {
		red_set_options( array( 'database' => '' ) );

		$status = new Red_Database_Status();
		$status->start_install( [] );
		$expected = [
			'status' => 'need-install',
			'inProgress' => false,
			'current' => '-',
			'next' => REDIRECTION_DB_VERSION,
		];
		$actual = $this->get_results( $status, null );

		$this->assertEquals( $expected, $actual );
	}

	public function testStatusRunningWithStage() {
		red_set_options( array( 'database' => '1.0' ) );
		$this->setRunningStage( 'add_title_201' );

		$reason = 'Add titles to redirects';

		$status = new Red_Database_Status();
		$database = new Red_Database();
		$status->start_upgrade( $database->get_upgrades() );
		$status->set_ok( $reason );

		$expected = [
			'status' => 'need-update',
			'result' => 'ok',
			'inProgress' => true,
			'current' => '1.0',
			'next' => REDIRECTION_DB_VERSION,
			'complete' => 0.0,
			'reason' => $reason,
		];

		$actual = $this->get_results( $status, $reason );

		$this->assertEquals( $expected, $actual );
	}

	public function testStatusRunningFinish() {
		red_set_options( array( 'database' => '1.0' ) );
		$this->setRunningStage( false );

		$reason = 'Expand size of redirect titles';

		$status = new Red_Database_Status();
		$database = new Red_Database();

		$status->start_upgrade( $database->get_upgrades() );
		$status->set_ok( $reason );
		$status->finish();
		$expected = [
			'status' => 'finish-update',
			'inProgress' => false,
			'complete' => 100,
			'reason' => $reason,
		];

		$actual = $this->get_results( $status, $reason );

		$this->assertEquals( $expected, $actual );
	}

	public function testStatusRunningInstallFinish() {
		red_set_options( array( 'database' => '1.0' ) );
		$this->setRunningStage( false );

		$reason = 'Expand size of redirect titles';

		$status = new Red_Database_Status();
		$database = new Red_Database();

		$status->start_install( $database->get_upgrades_for_version( '', false ) );
		$status->set_ok( $reason );
		$status->finish();
		$expected = [
			'status' => 'finish-install',
			'inProgress' => false,
			'complete' => 100,
			'reason' => $reason,
		];

		$actual = $this->get_results( $status, $reason );

		$this->assertEquals( $expected, $actual );
	}

	public function testStatusRunningError() {
		$latest = new Red_Latest_Database();
		$reason = 'this is an error';

		red_set_options( array( 'database' => '1.0' ) );
		$this->setRunningStage( 'add_title_201' );
		$status = new Red_Database_Status();
		$status->set_error( $reason );

		$expected = [
			'status' => 'need-update',
			'result' => 'error',
			'inProgress' => true,
			'current' => '1.0',
			'next' => REDIRECTION_DB_VERSION,
			'complete' => 0.0,
			'reason' => 'this is an error',
			'debug' => array_merge( $latest->get_table_schema(), [ 'Stage: add_title_201' ] ),
		];

		$actual = $this->get_results( $status, new WP_Error( 'error', 'this is an error' ) );

		$this->assertEquals( $expected, $actual );
	}

	private function get_results( $status, $reason ) {
		$actual = $status->get_json( $reason );
		unset( $actual['time'] );
		unset( $actual['manual'] );

		return $actual;
	}
}
