<?php

use Redirection\Database;
use Redirection\Plugin\Settings;

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

	public function setUp() : void {
		global $wpdb;

		$this->previous_prefix = $wpdb->prefix;
		$wpdb->prefix = 'dbtest_';

		$this->removeTables();
	}

	public function tearDown() : void {
		global $wpdb;

		$this->removeTables();

		$wpdb->prefix = $this->previous_prefix;
	}

	public function testInstallClean() {
		$database = new Database\Schema\Schema_Latest();
		$database->install();

		$this->assertTrue( $this->checkTableExists( 'redirection_items' ) );
		$this->assertTrue( $this->checkTableExists( 'redirection_groups' ) );
		$this->assertTrue( $this->checkTableExists( 'redirection_logs' ) );
		$this->assertTrue( $this->checkTableExists( 'redirection_404' ) );
	}

	public function testInstallExisting() {
		$database = new Database\Schema\Schema_Latest();
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
		add_option( Database\Status::OLD_DB_VERSION, 'test' );
		Settings\red_set_options( [ Database\Status::DB_UPGRADE_STAGE => 'something' ] );

		$database = new Database\Schema\Schema_Latest();
		$database->install();
		$database->remove();

		$this->assertFalse( $this->checkTableExists( 'redirection_items' ) );
		$this->assertFalse( $this->checkTableExists( 'redirection_groups' ) );
		$this->assertFalse( $this->checkTableExists( 'redirection_logs' ) );
		$this->assertFalse( $this->checkTableExists( 'redirection_404' ) );

		$this->assertFalse( get_option( 'redirection_post' ) );
		$this->assertFalse( get_option( 'redirection_root' ) );
		$this->assertFalse( get_option( 'redirection_index' ) );
		$this->assertFalse( get_option( Database\Status::OLD_DB_VERSION ) );
		$this->assertFalse( get_option( Database\Status::DB_UPGRADE_STAGE ) );
	}

	public function testDefaultGroupsClean() {
		global $wpdb;

		$database = new Database\Schema\Schema_Latest();
		$database->install();

		$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_groups" );
		$this->assertEquals( count( $groups ), 2 );
	}

	public function testDefaultGroupsExisting() {
		global $wpdb;

		$database = new Database\Schema\Schema_Latest();
		$database->install();
		$database->create_groups( $wpdb );

		$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_groups" );
		$this->assertEquals( count( $groups ), 2 );
	}

	public function testVersion() {
		delete_option( Database\Status::OLD_DB_VERSION );

		$database = new Database\Schema\Schema_Latest();
		$database->install();

		$settings = Settings\red_get_options();
		$this->assertFalse( get_option( Database\Status::OLD_DB_VERSION ) );
		$this->assertEquals( REDIRECTION_DB_VERSION, $settings['database'] );
	}

	public function testMissingTables() {
		$database = new Database\Schema\Schema_Latest();
		$missing = $database->get_missing_tables();

		$this->assertEquals( 4, count( $missing ) );
	}

	public function testGetSchema() {
		$database = new Database\Schema\Schema_Latest();
		$database->install();

		$schema = $database->get_table_schema();
		$this->assertEquals( 'CREATE TABLE', substr( $schema[0], 0, 12 ) );
	}
}
