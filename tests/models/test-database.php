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

	private function checkTableExists( $table ) {
		global $wpdb;

		$table = $wpdb->prefix.$table;
		$exists = $wpdb->get_results( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) );

		return count( $exists ) === 1;
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

	public function testNothing() {
		$this->assertFalse( $this->checkTableExists( 'redirection_items' ) );
		$this->assertFalse( $this->checkTableExists( 'redirection_groups' ) );
		$this->assertFalse( $this->checkTableExists( 'redirection_logs' ) );
		$this->assertFalse( $this->checkTableExists( 'redirection_404' ) );
	}

	public function testInstallClean() {
		$database = new RE_Database();
		$database->install();

		$this->assertTrue( $this->checkTableExists( 'redirection_items' ) );
		$this->assertTrue( $this->checkTableExists( 'redirection_groups' ) );
		$this->assertTrue( $this->checkTableExists( 'redirection_logs' ) );
		$this->assertTrue( $this->checkTableExists( 'redirection_404' ) );
	}

	public function testInstallExisting() {
		$database = new RE_Database();
		$database->install();
		$database->install();

		$this->assertTrue( $this->checkTableExists( 'redirection_items' ) );
		$this->assertTrue( $this->checkTableExists( 'redirection_groups' ) );
		$this->assertTrue( $this->checkTableExists( 'redirection_logs' ) );
		$this->assertTrue( $this->checkTableExists( 'redirection_404' ) );
	}

	public function testRemove() {
		add_option( 'redirection_post', 'test' );
		add_option( 'redirection_root', 'test' );
		add_option( 'redirection_index', 'test' );
		add_option( 'redirection_version', 'test' );

		$database = new RE_Database();
		$database->install();
		$database->remove();

		$this->assertFalse( $this->checkTableExists( 'redirection_items' ) );
		$this->assertFalse( $this->checkTableExists( 'redirection_groups' ) );
		$this->assertFalse( $this->checkTableExists( 'redirection_logs' ) );
		$this->assertFalse( $this->checkTableExists( 'redirection_404' ) );

		$this->assertFalse( get_option( 'redirection_post' ) );
		$this->assertFalse( get_option( 'redirection_root' ) );
		$this->assertFalse( get_option( 'redirection_index' ) );
		$this->assertFalse( get_option( 'redirection_version' ) );
	}

	public function testDefaultGroupsClean() {
		global $wpdb;

		$database = new RE_Database();
		$database->install();

		$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_groups" );
		$this->assertEquals( count( $groups ), 2 );
	}

	public function testDefaultGroupsExisting() {
		global $wpdb;

		$database = new RE_Database();
		$database->install();
		$database->create_defaults();

		$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_groups" );
		$this->assertEquals( count( $groups ), 2 );
	}

	public function testVersion() {
		$database = new RE_Database();
		$database->install();
		$this->assertEquals( REDIRECTION_DB_VERSION, get_option( 'redirection_version' ) );
	}

	public function testUpgradeSameVersion() {
		$database = new RE_Database();
		$database->install();
		$database->upgrade( REDIRECTION_DB_VERSION, REDIRECTION_DB_VERSION );

		$this->assertEquals( REDIRECTION_DB_VERSION, get_option( 'redirection_version' ) );
	}

	public function testUpgradeVersion() {
		$database = new RE_Database();
		$database->install();
		$database->upgrade( REDIRECTION_DB_VERSION, 10.1 );

		$this->assertEquals( '10.1', get_option( 'redirection_version' ) );
	}

	private function sqlClean( $sql ) {
		global $wpdb;

		$database = new RE_Database();

		$sql = str_replace( '{$prefix}', $wpdb->prefix, $sql );
		$sql = trim( $sql );

		if ( strlen( $sql ) > 0 && strpos( $sql, 'CREATE TABLE' ) !== false ) {
			$sql .= ' '.$database->get_charset();
		}

		return $sql;
	}

	private function loadSql( $file ) {
		$file = file_get_contents( $file );
		if ( $file ) {
			return array_filter( array_map( array( $this, 'sqlClean' ), explode( ';', $file ) ) );
		}

		return false;
	}

	private function createTables( $file ) {
		global $wpdb;

		$sql = $this->loadSql( $file );

		if ( $sql ) {
			$database = new RE_Database();

			foreach ( $sql as $table ) {
				$wpdb->query( $table );
			}

			$database->create_defaults();
			return true;
		}

		$this->fail( 'Missing table: '.$file );
	}

	private function checkAgainstLatest( $version ) {
		global $wpdb;

		$database = new RE_Database();

		foreach ( $database->get_all_tables() as $table => $expected ) {
			$actual = $this->getCreateTable( $table );
			$actual = preg_replace( '/^\s+/m', '', $actual );
			$actual = preg_replace( '/\s?COLLATE \w*/', '', $actual );
			$actual = preg_replace( '/\).*?$/', ') '.$database->get_charset(), $actual );

			// 'massage' all the SQL so we can try and match it
			$expected = preg_replace( '/^\s+/m', '', $expected );
			$expected = str_replace( 'IF NOT EXISTS ', '', $expected );

			$this->assertEquals( $expected, $actual, 'Database table for '.$version.' '.$table.' does not match' );
		}

		// Check deleted tables don't exist
		$this->assertFalse( $this->checkTableExists( 'redirection_modules' ), 'Modules table still exists' );

		// Other checks for converted rows
		$this->assertEquals( 0, intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups WHERE module_id=3" ), 10 ), 'Checking '.$version );
		$this->assertTrue( intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_groups" ), 10 ) > 0, 'Checking '.$version );
	}

	public function testUpgradeFromLatest() {
		// Perform upgrade to latest
		$database = new RE_Database();
		$database->install();
		$database->upgrade( REDIRECTION_DB_VERSION, REDIRECTION_DB_VERSION );

		// Check tables match latest
		$this->checkAgainstLatest( REDIRECTION_DB_VERSION );
	}

	/**
	 * Upgrades old tables the latest version
	 */
	public function testUpgradeFromOlder() {
		global $wpdb;

		// List of DB versions up to, but not including, the latest
		$versions = array(
			'2.3.3',
			'2.3.2',
			'2.3.1',
			'2.3.0',
			'2.1.19',
			'2.1.15',
			'2.0.0',
		);

		$this->removeTables();

		foreach ( $versions as $ver ) {
			// Load old tables
			$this->createTables( dirname( __FILE__ ).'/sql/'.$ver.'.sql' );
			$this->createContentForVersion( $ver );

			// Perform upgrade to latest
			$database = new RE_Database();
			$database->upgrade( $ver, REDIRECTION_DB_VERSION );

			// Check tables match latest
			$this->checkAgainstLatest( $ver );
			$this->checkContentForVersion( $ver );

			// Remove all evidence
			$this->removeTables();
		}
	}

	// Specifically checks the conversion of IP addresses when going from <2.4 => 2.4
	public function createContentForVersion( $ver ) {
		global $wpdb;

		if ( $ver === '2.3.3' ) {
			// Insert numeric IPs
			$wpdb->insert( $wpdb->prefix.'redirection_404', array( 'ip' => ip2long( '192.168.1.1' ) ) );
			$wpdb->insert( $wpdb->prefix.'redirection_404', array( 'ip' => ip2long( '203.168.1.5' ) ) );
		}
	}

	public function checkContentForVersion( $ver ) {
		global $wpdb;

		if ( $ver === '2.3.3' ) {
			// Check that the 404 table converts from INT to VARCHAR
			$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_404" );
			$this->assertEquals( '192.168.1.1', $rows[0]->ip );
			$this->assertEquals( '203.168.1.5', $rows[1]->ip );
		}
	}
}
