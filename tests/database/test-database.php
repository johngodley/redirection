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
		delete_option( 'redirection_version' );
		delete_option( Red_Database_Status::DB_UPGRADE_STAGE );

		$database = new Red_Database();

		$this->assertTrue( $database->needs_installing() );
		$this->assertFalse( $database->needs_updating( REDIRECTION_DB_VERSION ) );
	}

	// Dont trigger an install when upgrading from an older database without the 'database' setting
	public function testNoInstallAfterUpgradeOld() {
		delete_option( REDIRECTION_OPTION );
		update_option( 'redirection_version', 1 );

		$database = new Red_Database();

		$this->assertFalse( $database->needs_installing() );
	}

	// Don't trigger upgrade if not installed
	public function testNoUpgradeOnNewInstall() {
		delete_option( REDIRECTION_OPTION );
		delete_option( 'redirection_version' );

		$database = new Red_Database();

		$this->assertFalse( $database->needs_updating( REDIRECTION_DB_VERSION ) );
	}

	// Don't trigger upgrade if same version
	public function testNoUpgradeOnSameVersion() {
		update_option( 'redirection_version', array( 'database' => REDIRECTION_DB_VERSION ) );
		delete_option( 'redirection_version' );

		$database = new Red_Database();

		$this->assertFalse( $database->needs_updating( REDIRECTION_DB_VERSION ) );
	}

	// Don't trigger upgrade if newer version
	public function testNoUpgradeOnNewerVersion() {
		update_option( 'redirection_version', array( 'database' => '50.0' ) );
		delete_option( 'redirection_version' );

		$database = new Red_Database();

		$this->assertFalse( $database->needs_updating( REDIRECTION_DB_VERSION ) );
	}

	// Trigger upgrade if redirection_version is present
	public function testUpgradeOld() {
		update_option( 'redirection_version', 1 );

		$database = new Red_Database();

		$this->assertTrue( $database->needs_updating( REDIRECTION_DB_VERSION ) );
	}

	// Trigger upgrade if older database
	public function testUpgradeOldVersion() {
		update_option( REDIRECTION_OPTION, array( 'database' => '1.0' ) );
		delete_option( 'redirection_version' );

		$database = new Red_Database();

		$this->assertTrue( $database->needs_updating( REDIRECTION_DB_VERSION ) );
	}

	// Trigger upgrade if at target version but still have a stage remaining
	public function testUpgradeStillRemaining() {
		update_option( REDIRECTION_OPTION, array( 'database' => REDIRECTION_DB_VERSION ) );
		update_option( Red_Database_Status::DB_UPGRADE_STAGE, array( 'stage' => 'some_stage' ) );

		$database = new Red_Database();
		$this->assertTrue( $database->needs_updating( REDIRECTION_DB_VERSION ) );
	}

	public function testGetVersionNone() {
		delete_option( REDIRECTION_OPTION );
		delete_option( 'redirection_version' );

		$database = new Red_Database();

		$this->assertEquals( '', $database->get_current_version() );
	}

	public function testGetVersionOld() {
		delete_option( REDIRECTION_OPTION );
		update_option( 'redirection_version', '1.2' );

		$database = new Red_Database();

		$this->assertEquals( '1.2', $database->get_current_version() );
	}

	public function testGetVersionNew() {
		update_option( REDIRECTION_OPTION, array( 'database' => '1.5' ) );
		update_option( 'redirection_version', '1.2' );

		$database = new Red_Database();

		$this->assertEquals( '1.5', $database->get_current_version() );
	}

	public function testSupports() {
		update_option( REDIRECTION_OPTION, array( 'database' => '1.5' ) );

		$database = new Red_Database();

		$this->assertTrue( $database->does_support( '1.2' ) );
		$this->assertTrue( $database->does_support( '1.5' ) );
	}

	public function testDoesntSupport() {
		update_option( REDIRECTION_OPTION, array( 'database' => '1.5' ) );

		$database = new Red_Database();

		$this->assertFalse( $database->does_support( '1.8' ) );
	}

	public function testGetUpgradesForSameVersion() {
		$database = new Red_Database();
		$upgrades = $database->get_upgrades_for_version( '2.2' );
		$this->assertEquals( 5, count( $upgrades ) );
	}

	public function testGetUpgradesForUnknownVersion() {
		$database = new Red_Database();
		$upgrades = $database->get_upgrades_for_version( '100.0.0' );
		$this->assertEmpty( $upgrades );
	}
}
