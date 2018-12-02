<?php

class LatestDatabaseTest extends WP_UnitTestCase {
	private function checkTableExists( $table ) {
		global $wpdb;

		$table = $wpdb->prefix . $table;
		$exists = $wpdb->get_results( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) );

		return count( $exists ) === 1;
	}

	private function removeTables() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_items" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_logs" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_groups" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}redirection_404" );
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

	public function testInstallClean() {
		$database = new Red_Latest_Database();
		$database->install();

		$this->assertTrue( $this->checkTableExists( 'redirection_items' ) );
		$this->assertTrue( $this->checkTableExists( 'redirection_groups' ) );
		$this->assertTrue( $this->checkTableExists( 'redirection_logs' ) );
		$this->assertTrue( $this->checkTableExists( 'redirection_404' ) );
	}

	public function testInstallExisting() {
		$database = new Red_Latest_Database();
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
		add_option( Red_Database::OLD_DB_VERSION, 'test' );

		$database = new Red_Latest_Database();
		$database->install();
		$database->remove();

		$this->assertFalse( $this->checkTableExists( 'redirection_items' ) );
		$this->assertFalse( $this->checkTableExists( 'redirection_groups' ) );
		$this->assertFalse( $this->checkTableExists( 'redirection_logs' ) );
		$this->assertFalse( $this->checkTableExists( 'redirection_404' ) );

		$this->assertFalse( get_option( 'redirection_post' ) );
		$this->assertFalse( get_option( 'redirection_root' ) );
		$this->assertFalse( get_option( 'redirection_index' ) );
		$this->assertFalse( get_option( Red_Database::OLD_DB_VERSION ) );
	}

	public function testDefaultGroupsClean() {
		global $wpdb;

		$database = new Red_Latest_Database();
		$database->install();

		$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_groups" );
		$this->assertEquals( count( $groups ), 2 );
	}

	public function testDefaultGroupsExisting() {
		global $wpdb;

		$database = new Red_Latest_Database();
		$database->install();
		$database->create_groups( $wpdb );

		$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_groups" );
		$this->assertEquals( count( $groups ), 2 );
	}

	public function testVersion() {
		delete_option( Red_Database::OLD_DB_VERSION );

		$database = new Red_Latest_Database();
		$database->install();

		$settings = red_get_options();
		$this->assertFalse( get_option( Red_Database::OLD_DB_VERSION ) );
		$this->assertEquals( REDIRECTION_DB_VERSION, $settings['database'] );
	}

	public function testMissingTables() {
		$database = new Red_Latest_Database();
		$missing = $database->get_missing_tables();

		$this->assertEquals( 4, count( $missing ) );
	}

	public function testGetSchema() {
		$database = new Red_Latest_Database();
		$database->install();

		$schema = $database->get_table_schema();
		$this->assertEquals( 'CREATE TABLE', substr( $schema[0], 0, 12 ) );
	}
}
