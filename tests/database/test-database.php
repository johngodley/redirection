<?php

class DatabaseTest extends WP_UnitTestCase {
	private $previous_prefix;

	private function removeTables() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_items" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_logs" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_groups" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_404" );
	}

	private function getCreateTable( $table ) {
		global $wpdb;

		$create = 'Create Table';
		$result = $wpdb->get_row( "SHOW CREATE TABLE $table" );

		return $result->$create;
	}

	public function setUp() {
		global $wpdb;

		$this->previous_prefix = $wpdb->prefix;
		$wpdb->prefix = 'dbtest_';

		$this->removeTables();
	}

	public function tearDown() {
		global $wpdb;

		$this->removeTables();

		$wpdb->prefix = $this->previous_prefix;
	}

	// A fresh install should install the DB
	public function testNeedDatabaseAfterInstall() {
		delete_option( REDIRECTION_OPTION );
		delete_option( Red_Database_Status::OLD_DB_VERSION );
		red_set_options( [ Red_Database_Status::DB_UPGRADE_STAGE => false ] );

		$status = new Red_Database_Status();

		$this->assertTrue( $status->needs_installing() );
		$this->assertFalse( $status->needs_updating() );
	}

	// Dont trigger an install when upgrading from an older database without the 'database' setting
	public function testNoInstallAfterUpgradeOld() {
		delete_option( REDIRECTION_OPTION );
		update_option( Red_Database_Status::OLD_DB_VERSION, 1 );

		$status = new Red_Database_Status();

		$this->assertFalse( $status->needs_installing() );
	}

	// Don't trigger upgrade if not installed
	public function testNoUpgradeOnNewInstall() {
		delete_option( REDIRECTION_OPTION );
		delete_option( Red_Database_Status::OLD_DB_VERSION );

		$status = new Red_Database_Status();

		$this->assertFalse( $status->needs_updating() );
	}

	// Don't trigger upgrade if same version
	public function testNoUpgradeOnSameVersion() {
		update_option( Red_Database_Status::OLD_DB_VERSION, array( 'database' => REDIRECTION_DB_VERSION ) );
		delete_option( Red_Database_Status::OLD_DB_VERSION );

		$status = new Red_Database_Status();

		$this->assertFalse( $status->needs_updating() );
	}

	// Don't trigger upgrade if newer version
	public function testNoUpgradeOnNewerVersion() {
		update_option( Red_Database_Status::OLD_DB_VERSION, array( 'database' => '50.0' ) );
		delete_option( Red_Database_Status::OLD_DB_VERSION );

		$status = new Red_Database_Status();

		$this->assertFalse( $status->needs_updating() );
	}

	// Trigger upgrade if redirection_version is present
	public function testUpgradeOld() {
		update_option( Red_Database_Status::OLD_DB_VERSION, 1 );

		$status = new Red_Database_Status();

		$this->assertTrue( $status->needs_updating() );
		$this->assertFalse( get_option( Red_Database_Status::OLD_DB_VERSION ) );
		$this->assertEquals( 1, red_get_options()['database'] );
	}

	// Trigger upgrade if older database
	public function testUpgradeOldVersion() {
		update_option( REDIRECTION_OPTION, array( 'database' => '1.0' ) );
		delete_option( Red_Database_Status::OLD_DB_VERSION );

		$status = new Red_Database_Status();

		$this->assertTrue( $status->needs_updating() );
	}

	// Trigger upgrade if at target version but still have a stage remaining
	public function testUpgradeStillRemaining() {
		update_option( REDIRECTION_OPTION, array( 'database' => REDIRECTION_DB_VERSION ) );
		red_set_options( [ Red_Database_Status::DB_UPGRADE_STAGE => array( 'stage' => 'some_stage' ) ] );

		$status = new Red_Database_Status();
		$this->assertTrue( $status->needs_updating() );
	}

	public function testGetVersionNone() {
		delete_option( REDIRECTION_OPTION );
		delete_option( Red_Database_Status::OLD_DB_VERSION );

		$status = new Red_Database_Status();

		$this->assertEquals( '', $status->get_current_version() );
	}

	public function testGetVersionOld() {
		delete_option( REDIRECTION_OPTION );
		update_option( Red_Database_Status::OLD_DB_VERSION, '1.2' );

		$status = new Red_Database_Status();

		$this->assertEquals( '1.2', $status->get_current_version() );
	}

	public function testGetVersionNew() {
		update_option( REDIRECTION_OPTION, array( 'database' => '1.5' ) );
		update_option( Red_Database_Status::OLD_DB_VERSION, '1.2' );

		$status = new Red_Database_Status();

		$this->assertEquals( '1.5', $status->get_current_version() );
	}

	public function testSupports() {
		update_option( REDIRECTION_OPTION, array( 'database' => '1.5' ) );

		$status = new Red_Database_Status();

		$this->assertTrue( $status->does_support( '1.2' ) );
		$this->assertTrue( $status->does_support( '1.5' ) );

		update_option( REDIRECTION_OPTION, array( 'database' => REDIRECTION_DB_VERSION ) );
	}

	public function testDoesntSupport() {
		update_option( REDIRECTION_OPTION, array( 'database' => '1.5' ) );

		$status = new Red_Database_Status();

		$this->assertFalse( $status->does_support( '1.8' ) );

		update_option( REDIRECTION_OPTION, array( 'database' => REDIRECTION_DB_VERSION ) );
	}

	public function testGetUpgradesForSameVersion() {
		$database = new Red_Database();
		$upgrades = $database->get_upgrades_for_version( '2.2', false );
		$this->assertEquals( 7, count( $upgrades ) );
	}

	public function testGetUpgradesForUnknownVersion() {
		$database = new Red_Database();
		$upgrades = $database->get_upgrades_for_version( '100.0.0', false );
		$this->assertEmpty( $upgrades );
	}
}
