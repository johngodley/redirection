<?php

class DatabaseTester {
	public function get_create_table( $table ) {
		global $wpdb;

		$wpdb->hide_errors();
		$create = 'Create Table';
		$result = $wpdb->get_row( "SHOW CREATE TABLE $table" );
		$wpdb->show_errors();

		if ( $result === false ) {
			throw new Exception( 'Failed to create table ' . $table );
		}

		return $result->$create;
	}

	public function check_against_latest( $unit, $version ) {
		global $wpdb;

		$database = new Red_Latest_Database();

		foreach ( $database->get_all_tables() as $table => $expected ) {
			$actual = $this->get_create_table( $table );
			$actual = preg_replace( '/^\s+/m', '', $actual );
			$actual = preg_replace( '/\s?COLLATE \w*/', '', $actual );
			$actual = preg_replace( '/\).*?$/', ') ' . $database->get_charset(), $actual );

			// 'massage' all the SQL so we can try and match it
			$expected = preg_replace( '/^\s+/m', '', $expected );
			$expected = str_replace( 'IF NOT EXISTS ', '', $expected );

			$unit->assertEquals( $expected, $actual, 'Database table for ' . $version . ' ' . $table . ' does not match' );
		}

		// Other checks for converted rows
		$unit->assertEquals( 0, intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups WHERE module_id=3" ), 10 ), 'Checking ' . $version );
		$unit->assertTrue( intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups" ), 10 ) > 0, 'Checking ' . $version );
	}

	public function sql_clean( $sql ) {
		global $wpdb;

		$latest = new Red_Latest_Database();

		$sql = str_replace( '{$prefix}', $wpdb->prefix, $sql );
		$sql = trim( $sql );

		if ( strlen( $sql ) > 0 && strpos( $sql, 'CREATE TABLE' ) !== false ) {
			$sql .= ' ' . $latest->get_charset();
		}

		return $sql;
	}

	public function load_sql( $file ) {
		$file = file_get_contents( $file );
		if ( $file ) {
			return array_filter( array_map( array( $this, 'sql_clean' ), explode( ';', $file ) ) );
		}

		return false;
	}

	public function create_tables( $file ) {
		global $wpdb;

		$sql = $this->load_sql( $file );

		if ( $sql ) {
			foreach ( $sql as $table ) {
				$result = $wpdb->query( $table );

				if ( $result === false ) {
					throw new Exception( 'Failed to create table ' . $table );
				}
			}

			return true;
		}

		$this->fail( 'Missing table: ' . $file );
	}

	// Specifically checks the conversion of IP addresses when going from <2.4 => 2.4
	public function create_content_for_version( $ver ) {
		global $wpdb;

		if ( $ver === '2.3.4' ) {
			// Insert numeric IPs
			$wpdb->insert( $wpdb->prefix . 'redirection_404', array( 'ip' => ip2long( '192.168.1.1' ) ) );
			$wpdb->insert( $wpdb->prefix . 'redirection_404', array( 'ip' => ip2long( '203.168.1.5' ) ) );
		}
	}

	public function check_content_for_version( $unit, $ver ) {
		global $wpdb;

		if ( $ver === '2.3.4' ) {
			// Check that the 404 table converts from INT to VARCHAR
			$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_404" );
			$unit->assertEquals( '192.168.1.1', $rows[0]->ip );
			$unit->assertEquals( '203.168.1.5', $rows[1]->ip );
		}
	}
}

class UpgradeDatabaseTest extends WP_UnitTestCase {
	private $previous_prefix;

	private function removeTables() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_items" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_logs" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_groups" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_404" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_modules" );
	}

	public function setUp() {
		global $wpdb;

		$status = new Red_Database_Status();
		$status->stop_upgrade();

		$this->previous_prefix = $wpdb->prefix;
		$wpdb->prefix = 'dbtest_';

		$this->removeTables();
	}

	public function tearDown() {
		global $wpdb;

		$this->removeTables();
		red_set_options( array( 'database' => '' ) );

		$wpdb->prefix = $this->previous_prefix;
	}

	// A fresh install should install the DB
	public function testNeedDatabaseAfterInstall() {
		delete_option( REDIRECTION_OPTION );
		delete_option( Red_Database::OLD_DB_VERSION );

		$database = new Red_Database();

		$this->assertTrue( $database->needs_installing() );
		$this->assertFalse( $database->needs_updating( REDIRECTION_DB_VERSION ) );
	}

	// Dont trigger an install when upgrading from an older database without the 'database' setting
	public function testNoInstallAfterUpgradeOld() {
		delete_option( REDIRECTION_OPTION );
		update_option( Red_Database::OLD_DB_VERSION, 1 );

		$database = new Red_Database();

		$this->assertFalse( $database->needs_installing() );
	}

	// Don't trigger upgrade if not installed
	public function testNoUpgradeOnNewInstall() {
		delete_option( REDIRECTION_OPTION );
		delete_option( Red_Database::OLD_DB_VERSION );

		$database = new Red_Database();

		$this->assertFalse( $database->needs_updating( REDIRECTION_DB_VERSION ) );
	}

	// Don't trigger upgrade if same version
	public function testNoUpgradeOnSameVersion() {
		update_option( Red_Database::OLD_DB_VERSION, array( 'database' => REDIRECTION_DB_VERSION ) );
		delete_option( Red_Database::OLD_DB_VERSION );

		$database = new Red_Database();

		$this->assertFalse( $database->needs_updating( REDIRECTION_DB_VERSION ) );
	}

	// Don't trigger upgrade if newer version
	public function testNoUpgradeOnNewerVersion() {
		update_option( Red_Database::OLD_DB_VERSION, array( 'database' => '50.0' ) );
		delete_option( Red_Database::OLD_DB_VERSION );

		$database = new Red_Database();

		$this->assertFalse( $database->needs_updating( REDIRECTION_DB_VERSION ) );
	}

	// Trigger upgrade if redirection_version is present
	public function testUpgradeOld() {
		update_option( Red_Database::OLD_DB_VERSION, 1 );

		$database = new Red_Database();

		$this->assertTrue( $database->needs_updating( REDIRECTION_DB_VERSION ) );
	}

	// Trigger upgrade if older database
	public function testUpgradeOldVersion() {
		update_option( REDIRECTION_OPTION, array( 'database' => '1.0' ) );
		delete_option( Red_Database::OLD_DB_VERSION );

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
		delete_option( Red_Database::OLD_DB_VERSION );

		$database = new Red_Database();

		$this->assertEquals( '', $database->get_current_version() );
	}

	public function testGetVersionOld() {
		delete_option( REDIRECTION_OPTION );
		update_option( Red_Database::OLD_DB_VERSION, '1.2' );

		$database = new Red_Database();

		$this->assertEquals( '1.2', $database->get_current_version() );
	}

	public function testGetVersionNew() {
		update_option( REDIRECTION_OPTION, array( 'database' => '1.5' ) );
		update_option( Red_Database::OLD_DB_VERSION, '1.2' );

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

	public function testUpgradeFromOlder() {
		global $wpdb;

		$database = new Red_Database();
		$latest = new Red_Latest_Database();
		$tester = new DatabaseTester();
		$status = new Red_Database_Status();

		$versions = array(
			'2.3.4',  // => 2.4
			'2.3.3',  // => 2.3.3
			'2.3.2',  // => 2.3.2
			'2.3.1',  // => 2.3.1
			'2.1.19', // => 2.2
			'2.1.15', // => 2.1.16
			'2.0.0',  // => 2.0.1
		);

		foreach ( $versions as $ver ) {
			$this->removeTables();
			$status->stop_upgrade();

			// Load old tables
			$tester->create_tables( dirname( __FILE__ ) . '/sql/' . $ver . '.sql' );
			$latest->create_groups( $wpdb );
			$tester->create_content_for_version( $ver );

			red_set_options( array( 'database' => $ver ) );

			// Perform upgrade to latest
			$loop = 0;

			while ( $loop < 50 ) {
				$result = $database->apply_upgrade( $status->get_current_stage(), false );
				$info = $status->get_upgrade_status( $result );

				if ( ! $info['inProgress'] ) {
					break;
				}

				if ( $info['status'] === 'error' ) {
					$this->fail( $info['reason'] );
				}

				$loop++;
			}

			if ( $loop === 50 ) {
				$this->fail( 'Loop detected' );
			}

			// Check tables match latest
			$tester->check_against_latest( $this, $ver );
			$tester->check_content_for_version( $this, $ver );
		}
	}
}
