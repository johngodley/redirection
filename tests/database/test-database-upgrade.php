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
			$actual = preg_replace( '/\s?CHARACTER SET \w*/', '', $actual );
			$actual = preg_replace( '/\).*?$/', ') ' . $database->get_charset(), $actual );

			// 'massage' all the SQL so we can try and match it
			$expected = preg_replace( '/^\s+/m', '', $expected );
			$expected = str_replace( 'IF NOT EXISTS ', '', $expected );

			$expected = strtolower( $expected );
			$actual = strtolower( $actual );

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

	public function create_tables( $file, $unit ) {
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

		$unit->fail( 'Missing table: ' . $file );
	}

	// Specifically checks the conversion of IP addresses when going from <2.4 => 2.4
	public function create_content_for_version( $ver ) {
		global $wpdb;

		if ( $ver === '2.3.4' ) {
			// Insert numeric IPs
			$wpdb->insert( $wpdb->prefix . 'redirection_404', array( 'ip' => ip2long( '192.168.1.1' ) ) );
			$wpdb->insert( $wpdb->prefix . 'redirection_404', array( 'ip' => ip2long( '203.168.1.5' ) ) );
		} elseif ( $ver === '3.9' ) {
			$wpdb->insert( $wpdb->prefix . 'redirection_items', [ 'url' => '/TEST/?thing=cat' ] );
			$wpdb->insert( $wpdb->prefix . 'redirection_items', [ 'url' => '/' ] );
			$wpdb->insert( $wpdb->prefix . 'redirection_items', [ 'url' => '/.*', 'regex' => 1 ] );
			$wpdb->insert( $wpdb->prefix . 'redirection_items', [ 'url' => '//' ] );
			$wpdb->insert( $wpdb->prefix . 'redirection_items', [ 'url' => '/thing///' ] );
			$wpdb->insert( $wpdb->prefix . 'redirection_items', [ 'url' => '/index.php' ] );
		} elseif ( $ver === '4.0' ) {
			$wpdb->insert( $wpdb->prefix . 'redirection_items', [ 'url' => '//?s=no-results' ] );
		}
	}

	public function check_content_for_version( $unit, $ver ) {
		global $wpdb;

		if ( $ver === '2.3.4' ) {
			// Check that the 404 table converts from INT to VARCHAR
			$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_404" );
			$unit->assertEquals( '192.168.1.1', $rows[0]->ip );
			$unit->assertEquals( '203.168.1.5', $rows[1]->ip );
		} elseif ( $ver === '3.9' ) {
			$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_items" );

			$unit->assertEquals( '/TEST/?thing=cat', $rows[0]->url );
			$unit->assertEquals( '/test', $rows[0]->match_url );

			$unit->assertEquals( '/', $rows[1]->url );
			$unit->assertEquals( '/', $rows[1]->match_url );

			$unit->assertEquals( '/.*', $rows[2]->url );
			$unit->assertEquals( 'regex', $rows[2]->match_url );

			$unit->assertEquals( '/', $rows[3]->match_url );
			$unit->assertEquals( '/thing//', $rows[4]->match_url );
			$unit->assertEquals( '/index.php', $rows[5]->match_url );
		} elseif ( $ver === '4.0' ) {
			$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_items" );

			$unit->assertEquals( '/', $rows[0]->match_url );
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
		$status->stop_update();

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
		delete_option( Red_Database_Status::OLD_DB_VERSION );

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
		update_option( Red_Database_Status::DB_UPGRADE_STAGE, array( 'stage' => 'some_stage' ) );

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
	}

	public function testDoesntSupport() {
		update_option( REDIRECTION_OPTION, array( 'database' => '1.5' ) );

		$status = new Red_Database_Status();

		$this->assertFalse( $status->does_support( '1.8' ) );
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

	public function testUpgradeFromOlder() {
		global $wpdb;

		$database = new Red_Database();
		$latest = new Red_Latest_Database();
		$tester = new DatabaseTester();

		$versions = array(
			'4.2', // => 4.3
			'4.0', // => 4.1
			'3.9', // => 4.0
			'2.3.4', // => 2.4
			'2.3.2', // => 2.3.3
			'2.3.1.1', // => 2.3.2
			'2.3.0', // => 2.3.1
			'2.1.19', // => 2.2
			'2.1.15', // => 2.1.16
			'2.0.0',  // => 2.0.1
		);

		foreach ( $versions as $ver ) {
			$status = new Red_Database_Status();

			$this->removeTables();
			$status->stop_update();

			// Load old tables
			$tester->create_tables( dirname( __FILE__ ) . '/sql/' . $ver . '.sql', $this );
			$latest->create_groups( $wpdb );
			$tester->create_content_for_version( $ver );

			red_set_options( array( 'database' => $ver ) );

			// Perform upgrade to latest
			$last = 0;
			$loop = 0;

			while ( $loop < 200 ) {
				$result = $database->apply_upgrade( $status );

				if ( is_wp_error( $result ) ) {
					$this->fail( $result->get_error_message() );
				}

				$info = $status->get_json();
				if ( ! $info['inProgress'] ) {
					break;
				}

				if ( $info['result'] === 'error' ) {
					$this->fail( $info['current'] . ' ' . $info['reason'] );
				}

				if ( $last === $status->get_current_stage() ) {
					$this->fail( 'Loop detected for ' . $ver );
					die();
				}

				$last = $status->get_current_stage();
				$loop++;
			}

			// Check tables match latest
			$tester->check_against_latest( $this, $ver );
			$tester->check_content_for_version( $this, $ver );
		}
	}

	public function testUpgradeBroken233() {
		global $wpdb;

		$this->removeTables();

		// Some sites have a broken 2.4 database and are still marked as 2.3.3
		$status = new Red_Database_Status();
		$tester = new DatabaseTester();
		$latest = new Red_Latest_Database();
		$database = new Red_Database();

		$status->stop_update();
		$tester->create_tables( dirname( __FILE__ ) . '/sql/2.3.2.sql', $this );
		red_set_options( array( 'database' => '2.3.3' ) );
		$latest->create_groups( $wpdb );

		// Set up the broken install
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}redirection_404 DROP INDEX ip" );
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}redirection_404 ADD INDEX ip (id)" );
		$existing = $wpdb->get_row( "SHOW CREATE TABLE `{$wpdb->prefix}redirection_404`", ARRAY_N );

		while ( true ) {
			$result = $database->apply_upgrade( $status );

			if ( is_wp_error( $result ) ) {
				$this->fail( $result->get_error_message() );
			}

			$info = $status->get_json();
			if ( ! $info['inProgress'] ) {
				break;
			}

			if ( $info['result'] === 'error' ) {
				$this->fail( $info['current'] . ' ' . $info['reason'] );
			}
		}

		$tester->check_against_latest( $this, '2.3.2' );

		$existing = $wpdb->get_row( "SHOW CREATE TABLE `{$wpdb->prefix}redirection_404`", ARRAY_N );
		$this->assertTrue( strpos( $existing[1], 'KEY `ip` (`ip`)' ) !== false );
	}
}
