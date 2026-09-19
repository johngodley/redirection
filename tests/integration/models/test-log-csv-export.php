<?php

/**
 * A logged agent or referrer is attacker controlled and must not be able to
 * break out of its column when exported.
 */
class Log_Csv_Export_Test extends WP_UnitTestCase {
	const PAYLOAD = 'safe\\",=1+1,"';

	public function setUp(): void {
		parent::setUp();

		global $wpdb;

		// phpcs:ignore
		$wpdb->query( "TRUNCATE {$wpdb->prefix}redirection_404" );
		// phpcs:ignore
		$wpdb->query( "TRUNCATE {$wpdb->prefix}redirection_logs" );
	}

	private function parse_as_spreadsheet( $csv ) {
		$rows = [];

		foreach ( explode( "\n", trim( $csv ) ) as $line ) {
			$rows[] = str_getcsv( $line, ',', '"', '' );
		}

		return $rows;
	}

	public function test404ExportKeepsAgentPayloadInOneColumn() {
		Red_404_Log::create( 'domain', '/missing', '192.168.1.1', [
			'agent' => self::PAYLOAD,
			'referrer' => 'https://example.com/',
			'http_code' => 404,
			'request_method' => 'GET',
		] );

		$rows = $this->parse_as_spreadsheet( Red_404_Log::get_export_data( 'csv' ) );

		$this->assertCount( 2, $rows );
		$this->assertCount( count( $rows[0] ), $rows[1] );
		$this->assertContains( self::PAYLOAD, $rows[1] );
		$this->assertNotContains( '=1+1', $rows[1] );
	}

	public function test404ExportKeepsReferrerPayloadInOneColumn() {
		Red_404_Log::create( 'domain', '/missing', '192.168.1.1', [
			'agent' => 'agent',
			'referrer' => self::PAYLOAD,
			'http_code' => 404,
			'request_method' => 'GET',
		] );

		$rows = $this->parse_as_spreadsheet( Red_404_Log::get_export_data( 'csv' ) );

		$this->assertCount( 2, $rows );
		$this->assertCount( count( $rows[0] ), $rows[1] );
		$this->assertContains( self::PAYLOAD, $rows[1] );
		$this->assertNotContains( '=1+1', $rows[1] );
	}

	public function testRedirectLogExportKeepsAgentPayloadInOneColumn() {
		Red_Redirect_Log::create( 'domain', '/from', '192.168.1.1', [
			'agent' => self::PAYLOAD,
			'referrer' => 'https://example.com/',
			'target' => '/to',
			'http_code' => 301,
			'request_method' => 'GET',
		] );

		$rows = $this->parse_as_spreadsheet( Red_Redirect_Log::get_export_data( 'csv' ) );

		$this->assertCount( 2, $rows );
		$this->assertCount( count( $rows[0] ), $rows[1] );
		$this->assertContains( self::PAYLOAD, $rows[1] );
		$this->assertNotContains( '=1+1', $rows[1] );
	}

	public function testCustomFieldExportKeepsAgentPayloadInOneColumn() {
		Red_404_Log::create( 'domain', '/missing', '192.168.1.1', [
			'agent' => self::PAYLOAD,
			'referrer' => 'https://example.com/',
			'http_code' => 404,
			'request_method' => 'GET',
		] );

		$rows = $this->parse_as_spreadsheet( Red_404_Log::get_export_data( 'csv', [], [ 'url', 'agent' ] ) );

		$this->assertCount( 2, $rows );
		$this->assertCount( count( $rows[0] ), $rows[1] );
		$this->assertContains( self::PAYLOAD, $rows[1] );
		$this->assertNotContains( '=1+1', $rows[1] );
	}
}
